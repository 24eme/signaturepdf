let canvasEditions = [];
let fontCaveat = null;
let copiedObject = null;
let forceAddLock = true;
let addLock = true;
let activeCanvas = null;
let activeCanvasPointer = null;
let pdfRenderTasks = [];
let pdfPages = [];
let svgCollections = [];
let resizeTimeout;
let pdfHistory = {};
let currentScale = 1.5;
let windowWidth = window.innerWidth;
let menu = null;
let menuOffcanvas = null;
let currentCursor = null;
let signaturePad = null;
const penColorPicker = document.getElementById('penColorPicker');
let penColor = localStorage.getItem('penColor') ?? '#000000'
let nblayers = null;
let hasModifications = false;
let currentTextScale = 1;
const defaultScale = 1.5;

async function loadPDF(pdfBlob) {
    let filename = pdfBlob.name;
    let url = await URL.createObjectURL(pdfBlob);

    document.title = filename + ' - ' + document.title;
    let text_document_name = document.querySelector('#text_document_name');
    text_document_name.querySelector('span').innerText = filename;
    text_document_name.setAttribute('title', filename);

    let dataTransfer = new DataTransfer();
    dataTransfer.items.add(new File([pdfBlob], filename, {
        type: 'application/pdf'
    }));
    if(document.getElementById('input_pdf')) {
        document.getElementById('input_pdf').files = dataTransfer.files;
    }
    if(document.getElementById('input_pdf_share')) {
        document.getElementById('input_pdf_share').files = dataTransfer.files;
    }
    let loadingTask = pdfjsLib.getDocument(url);
    loadingTask.promise.then(function(pdf) {

        if(pdf.numPages > maxPage) {
            alert("Le PDF de doit pas dépasser "+maxPage+" pages");
            document.location = "/";
            return;
        }
        for(let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++ ) {
            pdf.getPage(pageNumber).then(function(page) {
              let scale = 1.5;
              let viewport = page.getViewport({scale: scale});
              if(viewport.width > document.getElementById('container-pages').clientWidth - 40) {
                  viewport = page.getViewport({scale: 1});
                  scale = (document.getElementById('container-pages').clientWidth - 40) / viewport.width;
                  viewport = page.getViewport({ scale: scale });
              }

              currentScale = scale;

              let pageIndex = page.pageNumber - 1;

              document.getElementById('form_block').insertAdjacentHTML('beforeend', '<input name="svg[' + pageIndex + ']" id="data-svg-' + pageIndex + '" type="hidden" value="" />');
              document.getElementById('container-pages').insertAdjacentHTML('beforeend', '<div class="position-relative mt-1 ms-1 me-1 d-inline-block" id="canvas-container-' + pageIndex +'"><canvas id="canvas-pdf-'+pageIndex+'" class="shadow-sm canvas-pdf"></canvas><div class="position-absolute top-0 start-0"><canvas id="canvas-edition-'+pageIndex+'"></canvas></div></div>');

              let canvasPDF = document.getElementById('canvas-pdf-' + pageIndex);
              let canvasEditionHTML = document.getElementById('canvas-edition-' + pageIndex);
              // Prepare canvas using PDF page dimensions
              let context = canvasPDF.getContext('2d');
              canvasPDF.height = viewport.height;
              canvasPDF.width = viewport.width;
              canvasEditionHTML.height = canvasPDF.height;
              canvasEditionHTML.width = canvasPDF.width;

              let renderContext = {
                canvasContext: context,
                viewport: viewport,
                enhanceTextSelection: true
              };
              let renderTask = page.render(renderContext);
              pdfRenderTasks.push(renderTask);
              pdfPages.push(page);
              let canvasEdition = new fabric.Canvas('canvas-edition-' + pageIndex, {
                selection : false,
                allowTouchScrolling: true
              });

              document.getElementById('canvas-container-' + pageIndex).addEventListener('drop', function(event) {
                  var input_selected = document.querySelector('input[name="svg_2_add"]:checked');
                  if(!input_selected) {
                      return;
                  }

                  createAndAddSvgInCanvas(canvasEdition, input_selected.value, event.layerX, event.layerY, input_selected.dataset.height);
                  input_selected.checked = false;
                  input_selected.dispatchEvent(new Event("change"));
              });
              canvasEdition.on('mouse:move', function(event) {
                  activeCanvas = this;
                  activeCanvasPointer = event.pointer;
              });
              canvasEdition.on('mouse:down:before', function(event) {
                  currentCursor = this.defaultCursor;
              });
              canvasEdition.on('mouse:down', function(event) {
                  if(event.target) {
                      this.defaultCursor = 'default';
                      return;
                  }
                  var input_selected = document.querySelector('input[name="svg_2_add"]:checked');
                  if(currentCursor == 'default' && input_selected) {
                       this.defaultCursor = 'copy';
                  }
                  if(currentCursor != 'copy') {
                      return;
                  }
                  if(!input_selected) {
                      return;
                  }

                  createAndAddSvgInCanvas(this, input_selected.value, event.pointer.x, event.pointer.y, input_selected.dataset.height);

                  if(addLock) {
                      return;
                  }
                  input_selected.checked = false;
                  input_selected.dispatchEvent(new Event("change"));
              });
              canvasEdition.on('object:scaling', function(event) {
                  if (event.target instanceof fabric.Line) {
                      return;
                  }
                  if (event.target instanceof fabric.Rect) {
                      return;
                  }
                  if(event.transform.action == "scaleX") {
                      event.target.scaleY = event.target.scaleX;
                  }
                  if(event.transform.action == "scaleY") {
                      event.target.scaleX = event.target.scaleY;
                  }
              });
              canvasEdition.on('object:modified', function(event) {
                  if (event.target instanceof fabric.IText) {
                      currentTextScale = event.target.scaleX;
                      return;
                  }
                  var item = getSvgItem(event.target.svgOrigin);
                  if(!item) {
                      return;
                  }
                  item.scale = event.target.width * event.target.scaleX / event.target.canvas.width;
                  storeCollections();
              });
              canvasEdition.on("text:changed", function(event) {
                   if (!event.target instanceof fabric.IText) {
                       return;
                   }
                  const textLinesMaxWidth = event.target.textLines.reduce((max, _, i) => Math.max(max, event.target.getLineWidth(i)), 0);
                  event.target.set({width: textLinesMaxWidth});
              });
              canvasEdition.on("selection:created", function(event) {
                  if (event.selected.length > 1 || event.selected.length === 0) {
                      return;
                  }

                  toolBox.init(event.selected[0])
              });
              canvasEditions.push(canvasEdition);
            });
        }
    }, function (reason) {
        console.error(reason);
    });
};

async function reloadPDF(url) {
    pdfjsLib.getDocument(url).promise.then(function(pdf) {
        for(let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++ ) {
            pdf.getPage(pageNumber).then(function(page) {
                page.render({
                  canvasContext: document.getElementById('canvas-pdf-' + (page.pageNumber - 1)).getContext('2d'),
                  viewport: page.getViewport({scale: currentScale}),
                  enhanceTextSelection: true
                });
            });
        }
    });
}

function responsiveDisplay() {
    if(is_mobile()) {
        document.getElementById('page-signature').classList.remove('decalage-pdf-div');
        menu.classList.remove('show');
        menuOffcanvas.hide();
        document.getElementById('container-pages').classList.remove('vh-100');
    } else {
        menuOffcanvas.show();
        document.getElementById('page-signature').classList.add('decalage-pdf-div');
        document.getElementById('container-pages').classList.add('vh-100');
    }
    menu.classList.remove('d-md-block');
    menu.classList.remove('d-none');
};

function storeCollections() {
    localStorage.setItem('svgCollections', JSON.stringify(svgCollections));
};

function getSvgItem(svg) {
    for (index in svgCollections) {
        let svgItem = svgCollections[index];
        if(svgItem.svg == svg) {

            return svgItem;
        }
    }

    return null;
};

function svgClick(label, event) {
    if(event.detail == 1) {
        label.dataset.lock = parseInt(addLock*1);
    }
    if(event.detail > 1){
        stateAddLock(parseInt(label.dataset.lock*1) != 1);
    }
    if(event.detail > 1) {
        return;
    }
    if(!document.getElementById(label.htmlFor)) {
        return;
    }
    if(!document.getElementById(label.htmlFor).checked) {
        return;
    }
    document.getElementById(label.htmlFor).checked = false;
    document.getElementById(label.htmlFor).dispatchEvent(new Event("change"));
    event.preventDefault();
};

function svgDblClick(label, event) {
    if(parseInt(label.dataset.lock*1) == 1) {
        return;
    }
    stateAddLock(true);
};

function svgDragStart(label, event) {
    document.getElementById(label.htmlFor).checked = true;
    document.getElementById(label.htmlFor).dispatchEvent(new Event("change"));
};

function svgChange(input, event) {
    if(input.checked) {
        document.getElementById('btn_svn_select').classList.add('d-none');
        document.getElementById('svg_object_actions').classList.add('d-none');
        document.getElementById('svg_selected_container').classList.remove('d-none');
        if(input.value.match(/^data:/)) {
            document.getElementById('svg_selected').src = input.value;
        } else {
            document.getElementById('svg_selected').src = input.dataset.svg;
        }
    } else {
        document.getElementById('btn_svn_select').classList.remove('d-none');
        document.getElementById('svg_object_actions').classList.add('d-none');
        document.getElementById('svg_selected_container').classList.add('d-none');
        document.getElementById('svg_selected').src = "";
    }

    stateAddLock(false);

    let input_selected = document.querySelector('input[name="svg_2_add"]:checked');

    if(input_selected && !input_selected.value.match(/^data:/) && input_selected.value != "text" && input_selected.value != "strikethrough" && input_selected.value != "rectangle") {
        input_selected = null;
    }

    document.querySelectorAll('.btn-svg').forEach(function(item) {
        if(input_selected && item.htmlFor == input_selected.id) {
            item.style.setProperty('cursor', 'copy');
        } else {
            item.style.removeProperty('cursor');
        }
    });

    canvasEditions.forEach(function(canvasEdition, index) {
        if(input_selected) {
            canvasEdition.defaultCursor = 'copy';
        } else {
            canvasEdition.defaultCursor = 'default';
        }
    })
    if(is_mobile()) {
        menuOffcanvas.hide();
    }
};

function getHtmlSvg(svg, i) {
    let inputRadio = document.createElement('input');
    inputRadio.type = "radio";
    inputRadio.classList.add("btn-check");
    inputRadio.id="radio_svg_"+i;
    inputRadio.name = "svg_2_add";
    inputRadio.autocomplete = "off";
    inputRadio.value = svg.svg;
    inputRadio.addEventListener('change', function() {
        svgChange(this, event);
    });
    let svgButton = document.createElement('label');
    svgButton.id = "label_svg_"+i;
    svgButton.classList.add('position-relative');
    svgButton.classList.add('btn');
    svgButton.classList.add('btn-svg');
    svgButton.classList.add('btn-outline-secondary');
    svgButton.htmlFor = "radio_svg_"+i;
    if(svg.type == 'signature') {
        svgButton.innerHTML += '<i class="bi bi-vector-pen text-black align-middle float-start"></i>';
    }
    if(svg.type == 'initials') {
        svgButton.innerHTML += '<i class="bi bi-type text-black align-middle float-start"></i>';
    }
    if(svg.type == 'rubber_stamber') {
        svgButton.innerHTML += '<i class="bi bi-card-text text-black align-middle float-start"></i>';
    }
    if(svg.type) {
        document.querySelector('.btn-add-svg-type[data-type="'+svg.type+'"]').classList.add('d-none');
    }
    svgButton.innerHTML += '<a title="Supprimer" data-index="'+i+'" class="btn-svg-list-suppression opacity-50 link-dark position-absolute"><i class="bi bi-trash"></i></a>';
    svgButton.draggable = true;
    svgButton.addEventListener('dragstart', function(event) {
        svgDragStart(this, event);
    });
    svgButton.addEventListener('click', function(event) {
        svgClick(this, event);
    });
    svgButton.addEventListener('dblclick', function(event) {
        svgDblClick(this, event);
    });
    svgButton.addEventListener('mouseout', function(event) {
        this.style.removeProperty('cursor');
    })
    let svgImg = document.createElement('img');
    svgImg.src = svg.svg;
    svgImg.draggable = false;
    svgImg.style = "max-width: 180px;max-height: 70px;";
    svgButton.appendChild(svgImg);
    let svgContainer = document.createElement('div');
    svgContainer.classList.add('d-grid');
    svgContainer.classList.add('gap-2');
    svgContainer.appendChild(inputRadio);
    svgContainer.appendChild(svgButton);

    return svgContainer;
};

function stateAddLock(state) {
    if(forceAddLock) {
        state = true;
    }
    let checkbox = document.getElementById('add-lock-checkbox');
    let input_selected = document.querySelector('input[name="svg_2_add"]:checked');

    addLock = state;

    if(!input_selected) {
        addLock = false;
        checkbox.disabled = true;
    } else {
        checkbox.disabled = false;
    }

    if(addLock && input_selected) {
        let svgButton = document.querySelector('.btn-svg[for="'+input_selected.id+'"]');
        checkbox.checked = true;
        return;
    }

    checkbox.checked = false;
};

function displaysSVG() {
    document.getElementById('svg_list').innerHTML = "";
    document.getElementById('svg_list_signature').innerHTML = "";
    document.getElementById('svg_list_initials').innerHTML = "";
    document.getElementById('svg_list_rubber_stamber').innerHTML = "";
    document.querySelectorAll('.btn-add-svg-type').forEach(function(item) {
        item.classList.remove('d-none');
    });
    svgCollections.forEach((svg, i) => {
        let svgHtmlChild = getHtmlSvg(svg, i);
        if(svg.type) {
            document.getElementById('svg_list_'+svg.type).appendChild(svgHtmlChild);
            return;
        }
        document.getElementById('svg_list').appendChild(svgHtmlChild);
    });

    if(svgCollections.length > 0) {
        document.getElementById('btn-add-svg').classList.add('btn-light');
        document.getElementById('btn-add-svg').classList.remove('btn-primary');
    }

    if(document.getElementById('btn-add-svg').classList.contains('btn-primary')) {
        document.getElementById('btn-add-svg').focus();
    }

    document.querySelectorAll('.btn-svg-list-suppression').forEach(function(item) {
        item.addEventListener('click', function() {
            svgCollections.splice(this.dataset.index, 1);
            displaysSVG();
            storeCollections();
        });
    });
};

function uploadSVG(formData) {
    document.getElementById('btn_modal_ajouter').setAttribute('disabled', 'disabled');
    document.getElementById('btn_modal_ajouter_spinner').classList.remove('d-none');
    document.getElementById('btn_modal_ajouter_check').classList.add('d-none');

    let xhr = new XMLHttpRequest();

    xhr.open( 'POST', document.getElementById('form-image-upload').action, true );
    xhr.onreadystatechange = function () {
        var svgImage = svgToDataUrl(trimSvgWhitespace(this.responseText));
        document.getElementById('img-upload').src = svgImage;
        document.getElementById('img-upload').classList.remove("d-none");
        document.getElementById('btn_modal_ajouter').removeAttribute('disabled');
        document.getElementById('btn_modal_ajouter_spinner').classList.add('d-none');
        document.getElementById('btn_modal_ajouter_check').classList.remove('d-none');
        document.getElementById('btn_modal_ajouter').focus();
    };
    xhr.send( formData );
};

function deleteActiveObject() {
    canvasEditions.forEach(function(canvasEdition, index) {
        canvasEdition.getActiveObjects().forEach(function(activeObject) {
            canvasEdition.remove(activeObject);
            if(activeObject.type == "rect") {
                updateFlatten();
            }
        });
    })
};

function addObjectInCanvas(canvas, item) {
    item.on('selected', function(event) {
        if(!is_mobile()) {
            return;
        }
        document.getElementById('svg_object_actions').classList.remove('d-none');
        document.getElementById('svg_selected_container').classList.add('d-none');
        document.getElementById('btn_svn_select').classList.add('d-none');
    });

    item.on('deselected', function(event) {
        if(!is_mobile()) {
            return;
        }
        if(document.querySelector('input[name="svg_2_add"]:checked')) {
            document.getElementById('svg_selected_container').classList.remove('d-none');
        } else {
            document.getElementById('btn_svn_select').classList.remove('d-none');
        }
        document.getElementById('svg_object_actions').classList.add('d-none');
    });

    return canvas.add(item);
};

function updateWatermark() {
    const text = new fabric.Text(document.querySelector('input[name=watermark]').value, {angle: -40, fill: "#0009", fontSize: 27 * currentScale})
    text.scale = 0.
    const overlay = new fabric.Rect({
        fill: new fabric.Pattern({
            source: text.toCanvasElement(),
        }),
    })

    canvasEditions.forEach(function (canvas) {
        overlay.height = canvas.height
        overlay.width = canvas.width

        canvas.objectCaching = false
        canvas.setOverlayImage(overlay, canvas.renderAll.bind(canvas), {
            objectCaching: false
        })
    })
}


function updateFlatten() {
    let flatten = Boolean(document.querySelector('input[name=watermark]').value);

    flatten = flatten || canvasEditions.some(function (canvas) {
        return canvas.getObjects().some(function (object) {
            return object.type === "rect"
        })
    })

    document.querySelector('input[name=flatten]').checked = flatten;
    if(document.getElementById('save_flatten_indicator')) {
        document.getElementById('save_flatten_indicator').classList.toggle('invisible', !flatten);
    }
}

function setIsChanged(changed) {
    hasModifications = changed

    if(document.querySelector('#alert-signature-help')) {
        document.querySelector('#alert-signature-help').classList.toggle('d-none', changed);
    }
    if(document.getElementById('save')) {
        document.getElementById('save').toggleAttribute('disabled', !changed);
    }
    if(document.getElementById('save_mobile')) {
        document.getElementById('save_mobile').toggleAttribute('disabled', !changed);
    }
    if(document.getElementById('btn_download')) {
        document.getElementById('btn_download').classList.toggle('btn-outline-dark', !changed);
        document.getElementById('btn_download').classList.toggle('btn-outline-secondary', changed);
    }
    if(document.getElementById('btn_share')) {
        document.getElementById('btn_share').classList.toggle('btn-outline-dark', !changed);
        document.getElementById('btn_share').classList.toggle('btn-outline-secondary', changed);
    }
}

function createAndAddSvgInCanvas(canvas, item, x, y, height = null) {
    setIsChanged(true)

    if(!height) {
        height = 100;
    }

    if(item == 'text') {
        let textbox = new fabric.Textbox(trad['Text to modify'], {
        left: x,
        top: y - 20,
        fontSize: 20,
        direction: direction,
        fontFamily: 'Monospace',
        fill: penColor
      });

      addObjectInCanvas(canvas, textbox).setActiveObject(textbox);
      textbox.keysMap[13] = "exitEditing";
      textbox.lockScalingFlip = true;
      textbox.scaleX = currentTextScale;
      textbox.scaleY = currentTextScale;
      textbox.enterEditing();
      textbox.selectAll();


      return;
    }

    if(item == 'strikethrough') {
        let line = new fabric.Line([x, y, x + 250, y], {
          fill: penColor,
          stroke: penColor,
          lockScalingFlip: true,
          strokeWidth: 2,
          padding: 10,
        });
        line.setControlsVisibility({ bl: false, br: false, mt: false, mb: false, tl: false, tr: false})

        addObjectInCanvas(canvas, line).setActiveObject(line);

        return;
    }

    if(item == 'rectangle') {
        let rect = new fabric.Rect({
          left: x,
          top: y,
          width: 200,
          height: 100,
          fill: '#000',
          lockScalingFlip: true
        });
        rect.setControlsVisibility({ tl: false, tr: false, bl: false, br: false,})

        addObjectInCanvas(canvas, rect).setActiveObject(rect);

        updateFlatten();
        return;
    }

    fabric.loadSVGFromURL(item, function(objects, options) {
        let svg = fabric.util.groupSVGElements(objects, options);
        svg.svgOrigin = item;
        svg.lockScalingFlip = true;
        svg.scaleToHeight(height);
        if(svg.getScaledWidth() > 200) {
            svg.scaleToWidth(200);
        }
        let svgItem = getSvgItem(item);
        if(svgItem && svgItem.scale) {
            svg.scaleToWidth(canvas.width * svgItem.scale);
        }
        svg.top = y - (svg.getScaledHeight() / 2);
        svg.left = x - (svg.getScaledWidth() / 2);

        svg.fill = penColor

        addObjectInCanvas(canvas, svg);
    });
};

function autoZoom() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(resizePDF, 100);
};

function zoomChange(inOrOut) {
    if(resizeTimeout) {
        return;
    }

    let deltaScale = 0.2 * inOrOut;

    if(currentScale + deltaScale < 0) {
        return
    }
    if(currentScale + deltaScale > 3) {
        return
    }

    clearTimeout(resizeTimeout);
    currentScale += deltaScale;

    resizeTimeout = setTimeout(resizePDF(currentScale), 50);
};

function resizePDF(scale = 'auto') {
    renderComplete = true;
    pdfRenderTasks.forEach(function(renderTask) {
        if(!renderTask) {
            renderComplete = false;
        }
    });

    if(!renderComplete) {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function(){ resizePDF(scale) }, 50);
        return;
    }

    pdfPages.forEach(function(page, pageIndex) {
        let renderTask = pdfRenderTasks[pageIndex];

        if(scale == 'auto' && page.getViewport({scale: 1.5}).width > document.getElementById('container-pages').clientWidth - 40) {
            scale = (document.getElementById('container-pages').clientWidth - 40) / page.getViewport({scale: 1}).width;
        }

        if(scale == 'auto') {
            scale = 1.5;
        }

        let viewport = page.getViewport({scale: scale});
        currentScale = scale;

        let canvasPDF = document.getElementById('canvas-pdf-' + pageIndex);
        let context = canvasPDF.getContext('2d');
        canvasPDF.height = viewport.height;
        canvasPDF.width = viewport.width;
        canvasEdition = canvasEditions[pageIndex];

        let scaleMultiplier = canvasPDF.width / canvasEdition.width;
        let objects = canvasEdition.getObjects();
        for (let i in objects) {
           objects[i].scaleX = objects[i].scaleX * scaleMultiplier;
           objects[i].scaleY = objects[i].scaleY * scaleMultiplier;
           objects[i].left = objects[i].left * scaleMultiplier;
           objects[i].top = objects[i].top * scaleMultiplier;
           objects[i].setCoords();
        }

        canvasEdition.setWidth(canvasEdition.getWidth() * scaleMultiplier);
        canvasEdition.setHeight(canvasEdition.getHeight() * scaleMultiplier);
        canvasEdition.renderAll();
        canvasEdition.calcOffset();

        let renderContext = {
          canvasContext: context,
          viewport: viewport,
          enhanceTextSelection: true
        };
        renderTask.cancel();
        pdfRenderTasks[pageIndex] = null;
        renderTask = page.render(renderContext);
        renderTask.promise.then(function () {
            pdfRenderTasks[pageIndex] = renderTask;
            clearTimeout(resizeTimeout);
            resizeTimeout = null;
        });
    });

    updateWatermark();
};

function createEventsListener() {

    document.getElementById('add-lock-checkbox').addEventListener('change', function() {
        stateAddLock(this.checked);
    });

    document.querySelectorAll('.btn-add-svg-type').forEach(function(item) {
        item.addEventListener('click', function(event) {
            document.getElementById('input-svg-type').value = this.dataset.type;
            if(this.dataset.modalnav) {
                bootstrap.Tab.getOrCreateInstance(document.querySelector('#modalAddSvg #nav-tab '+this.dataset.modalnav)).show();
            }
        });
    });

    document.querySelectorAll('label.btn-svg').forEach(function(item) {
        item.addEventListener('dragstart', function(event) {
            svgDragStart(this, event);
        });
        item.addEventListener('click', function(event) {
            svgClick(this, event);
        });
        item.addEventListener('dblclick', function(event) {
            svgDblClick(this, event);
        });
    });

    document.querySelectorAll('input[name="svg_2_add"]').forEach(function (item) {
        item.addEventListener('change', function(event) {
            svgChange(this, event);
        });
    });

    document.getElementById('btn_modal_ajouter').addEventListener('click', function() {
        let svgItem = {};
        if(document.getElementById('input-svg-type').value) {
            svgItem.type = document.getElementById('input-svg-type').value;
        }
        if(document.getElementById('nav-draw-tab').classList.contains('active')) {
            svgItem.svg = document.getElementById('img-upload').src;
        }
        if(document.getElementById('nav-type-tab').classList.contains('active')) {
            let fontPath = fontCaveat.getPath(document.getElementById('input-text-signature').value, 0, 0, 42);
            let fabricPath = new fabric.Path(fontPath.toPathData());
            fabricPath.top = 0;
            fabricPath.left = 0;
            fabricPath.height = fabricPath.getScaledHeight();
            let textCanvas = document.createElement('canvas');
            textCanvas.width = fabricPath.getScaledWidth();
            textCanvas.height = fabricPath.getScaledHeight();
            let textCanvasFabric = new fabric.Canvas(textCanvas);
            textCanvasFabric.add(fabricPath).renderAll();
            svgItem.svg = svgToDataUrl(textCanvasFabric.toSVG());
        }
        if(document.getElementById('nav-import-tab').classList.contains('active')) {
            svgItem.svg = document.getElementById('img-upload').src;
        }
        svgCollections.push(svgItem);
        displaysSVG();
        localStorage.setItem('svgCollections', JSON.stringify(svgCollections));

        let svg_list_id = "svg_list";
        if(svgItem.type) {
            svg_list_id = svg_list_id + "_" + svgItem.type;
        }

        document.querySelector('#'+svg_list_id+' label:last-child').click();

        if(document.querySelector('#save').disabled && document.querySelector('#alert-signature-help.auto-open') && !is_mobile()) {
            document.querySelector('#alert-signature-help').classList.remove('d-none');
        }
    });


    document.getElementById('signature-pad-reset').addEventListener('click', function(event) {
        signaturePad.clear();
        event.preventDefault();
    })

    document.querySelectorAll('#modalAddSvg .nav-link').forEach(function(item) { item.addEventListener('shown.bs.tab', function (event) {
        let firstInput = document.querySelector(event.target.dataset.bsTarget).querySelector('input');
        if(firstInput) {
            firstInput.focus();
        }
    })});

    document.getElementById('modalAddSvg').addEventListener('shown.bs.modal', function (event) {
        document.querySelector('#modalAddSvg #nav-tab button:first-child').focus();
        let tab = document.querySelector('#modalAddSvg .tab-pane.active');
        if(tab.querySelector('input')) {
            tab.querySelector('input').focus();
        }
        let input_selected = document.querySelector('input[name="svg_2_add"]:checked');
        if(input_selected) {
            input_selected.checked = false;
            input_selected.dispatchEvent(new Event("change"));
        }
    })

    document.getElementById('modalAddSvg').addEventListener('hidden.bs.modal', function (event) {
        signaturePad.clear();
        document.getElementById('btn_modal_ajouter').setAttribute('disabled', 'disabled');
        document.getElementById('input-svg-type').value = null;
        document.getElementById('input-text-signature').value = null;
        document.getElementById('input-image-upload').value = null;
        document.getElementById('img-upload').src = "";
        document.getElementById('img-upload').classList.add("d-none");
        bootstrap.Tab.getOrCreateInstance(document.querySelector('#modalAddSvg #nav-tab button:first-child')).show();
    })

    document.getElementById('input-text-signature').addEventListener('keydown', function(event) {
        document.getElementById('btn_modal_ajouter').removeAttribute('disabled');
        if(event.key == 'Enter') {
            document.getElementById('btn_modal_ajouter').click()
        }
    })

    document.getElementById('input-image-upload').addEventListener('change', function(event) {
        let data = new FormData();
        data.append('file', document.getElementById('input-image-upload').files[0]);
        uploadSVG(data);
        event.preventDefault();
    });

    document.querySelector('.input-watermark-placeholder')?.addEventListener('click', function (e) {
        const div = e.target
        const input = div.parentNode.querySelector('.input-group')

        input.classList.remove('d-none')
        div.classList.add('d-none')
        input.querySelector('input').focus()
    })

    document.querySelector('input[name=watermark]')?.addEventListener('keyup', debounce(function (e) {
        setIsChanged(hasModifications || !!e.target.value)
        updateFlatten();
        updateWatermark();
    }, 750))

    document.querySelector('input[name=watermark]')?.addEventListener('change', function (e) {
        setIsChanged(hasModifications || !!e.target.value)
        updateFlatten();
        updateWatermark();
    });

    if(document.querySelector('#alert-signature-help')) {
        document.getElementById('btn-signature-help').addEventListener('click', function(event) {
            document.querySelector('#alert-signature-help').classList.remove('d-none');
            event.preventDefault();
        });
        document.querySelector('#alert-signature-help .btn-close').addEventListener('click', function(event) {
            document.querySelector('#alert-signature-help').classList.add('d-none');
            event.preventDefault();
        });
    }

    if(document.getElementById('save')) {
        document.getElementById('save').addEventListener('click', function(event) {
            let previousScale = currentScale;
            if(currentScale != defaultScale) {
                resizePDF(defaultScale)
                while(!renderComplete) { }
            }
            let dataTransfer = new DataTransfer();
            canvasEditions.forEach(function(canvasEdition, index) {
                dataTransfer.items.add(new File([canvasEdition.toSVG()], index+'.svg', {
                    type: 'image/svg+xml'
                }));
            })
            document.getElementById('input_svg').files = dataTransfer.files;
            if(previousScale != currentScale) {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(resizePDF(previousScale), 100);
            }

            hasModifications = false;
        });
    }

    if(document.getElementById('save_share')) {
        document.getElementById('save_share').addEventListener('click', function(event) {
            let dataTransfer = new DataTransfer();
            if(!document.getElementById('save').hasAttribute('disabled')) {
                canvasEditions.forEach(function(canvasEdition, index) {
                    dataTransfer.items.add(new File([canvasEdition.toSVG()], index+'.svg', {
                        type: 'image/svg+xml'
                    }));
                })
            }
            document.getElementById('input_svg_share').files = dataTransfer.files;
            hasModifications = false;


            document.getElementById('input_pdf_hash').value = generatePdfHash();

            if (document.getElementById('checkbox_encryption').checked) {
                storeSymmetricKeyCookie(document.getElementById('input_pdf_hash').value, generateSymmetricKey());
            }

        });
    }

    document.getElementById('save_mobile').addEventListener('click', function(event) {
        document.getElementById('save').click();
    });

    document.getElementById('btn-svg-pdf-delete').addEventListener('click', function(event) {
        deleteActiveObject();
    });

    document.getElementById('btn_svg_selected_close').addEventListener('click', function(event) {
        let input_selected = document.querySelector('input[name="svg_2_add"]:checked');

        stateAddLock(false);
        input_selected.checked = false;
        input_selected.dispatchEvent(new Event("change"));
        this.blur();
    });

    document.addEventListener('click', function(event) {
        if(event.target.nodeName == "DIV") {
            let input_selected = document.querySelector('input[name="svg_2_add"]:checked');
            if(!input_selected) {
                return;
            }
            stateAddLock(false);
            input_selected.checked = false;
            input_selected.dispatchEvent(new Event("change"));
        }
    });

    document.addEventListener('keydown', function(event) {
        if(event.key == 'Escape' && (event.target.tagName == "BODY" || event.target.name == "svg_2_add")) {
            let input_selected = document.querySelector('input[name="svg_2_add"]:checked');
            if(!input_selected) {
                return;
            }
            input_selected.checked = false;
            stateAddLock(false);
            input_selected.dispatchEvent(new Event("change"));
            input_selected.blur();
            return;
        }
        if(event.target.tagName != "BODY") {
            return;
        }
        if(event.key == 'Delete') {
            deleteActiveObject();
            return;
        }

        if(event.ctrlKey && event.key == 'c') {
            if(!activeCanvas || !activeCanvas.getActiveObject()) {
                return;
            }
            copiedObject = fabric.util.object.clone(activeCanvas.getActiveObject());

            return;
        }

        if(event.ctrlKey && event.key == 'v') {
            copiedObject = fabric.util.object.clone(copiedObject);
            copiedObject.left = activeCanvasPointer.x;
            copiedObject.top = activeCanvasPointer.y;
            activeCanvas.add(copiedObject).renderAll();
            return;
        }

        if(event.ctrlKey && (event.key == 'à' || event.key == '0')) {
            autoZoom();
            event.preventDefault() && event.stopPropagation();

            return;
        }
        if(event.ctrlKey && (event.key == '=' || event.key == '+')) {
            zoomChange(1);
            event.preventDefault() && event.stopPropagation();

            return;
        }
        if(event.ctrlKey && event.key == '-') {
            zoomChange(-1);
            event.preventDefault() && event.stopPropagation();

            return;
        }
    });

    window.addEventListener('resize', function(event) {
        event.preventDefault() && event.stopPropagation();
        if(windowWidth == window.innerWidth) {
            return;
        }
        responsiveDisplay();
        windowWidth = window.innerWidth;
        autoZoom();
    });
    document.addEventListener('wheel', function(event) {
        if(!event.ctrlKey) {
            return;
        }
        event.preventDefault() && event.stopPropagation();

        if(event.deltaY > 0) {
            zoomChange(-1)
        } else {
            zoomChange(1)
        }
    }, { passive: false });

    document.getElementById('btn-zoom-decrease').addEventListener('click', function() {
        zoomChange(-1)
    });
    document.getElementById('btn-zoom-increase').addEventListener('click', function() {
        zoomChange(1)
    });

    penColorPicker.addEventListener('input', function (e) {
        e.preventDefault()
        storePenColor(penColorPicker.value)
    })

    window.addEventListener('beforeunload', function(event) {
        if(!hasModifications) {
            return;
        }

        event.preventDefault();
        return true;
    });

    if(pdfHash) {
        updateNbLayers();
        setInterval(function() {
            updateNbLayers();
        }, 10000);
    }
};

function createSignaturePad() {
    signaturePad = new SignaturePad(document.getElementById('signature-pad'), {
        penColor: 'rgb(0, 0, 0)',
        minWidth: 1,
        maxWidth: 2
    });
    signaturePad.addEventListener('endStroke', debounce(function(){
        const file = new File([dataURLtoBlob(signaturePad.toDataURL())], "draw.png", {
            type: 'image/png'
        });
        let data = new FormData();
        data.append('file', file);
        uploadSVG(data);
    }, 500));
};

async function getPDFBlobFromCache(cacheUrl) {
    const cache = await caches.open('pdf');
    let responsePdf = await cache.match(cacheUrl);

    if(!responsePdf) {
        return null;
    }

    let pdfBlob = await responsePdf.blob();

    return pdfBlob;
}

function modalSharing() {
    if(openModal == "shareinformations") {
        let modalInformationsEl = document.getElementById('modal-share-informations');
        let modalInformations = bootstrap.Modal.getOrCreateInstance(modalInformationsEl);
        modalInformations.show();
    }

    if(openModal == 'signed') {
        let modalSignedEl = document.getElementById('modal-signed');
        let modalSigned = bootstrap.Modal.getOrCreateInstance(modalSignedEl);

        document.querySelector('#modal-signed #text_nomail_notification a').addEventListener('click', function() {
            this.href = this.href.replace(/DOCUMENT_NAME/g, document.getElementById('text_document_name').title);
            this.href = this.href.replace('DOCUMENT_URL', document.location.href);
        });

        document.querySelector('#modal-signed #text_nomail_notification a').href = document.getElementById('link_compose_mail').href;

        modalSigned.show();
    }
}

function runCron() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', '/cron');
    xhr.send();
}

async function pageUpload() {
    document.querySelector('body').classList.remove('bg-light');
    document.getElementById('input_pdf_upload').value = '';
    document.getElementById('page-upload').classList.remove('d-none');
    document.getElementById('page-signature').classList.add('d-none');
    document.getElementById('input_pdf_upload').focus();
    document.getElementById('input_pdf_upload').addEventListener('change', async function(event) {
        if(await canUseCache()) {
            storeFileInCache();
            history.pushState({}, '', '/signature#'+document.getElementById('input_pdf_upload').files[0].name);
        }
        pageSignature(null);
    });
}

function updateNbLayers() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', '/signature/'+pdfHash+'/nblayers', true);
    xhr.onload = function() {
      if (xhr.status == 200) {
          let newNblayers = xhr.response;
          if(nblayers !== null && nblayers != newNblayers) {
              console.log('/signature/'+pdfHash+'/pdf');
              reloadPDF('/signature/'+pdfHash+'/pdf');
          }
          nblayers = newNblayers;
          document.querySelectorAll('.nblayers').forEach(function(item) {
            item.innerHTML = nblayers;
          });
          document.querySelector('#nblayers_text').classList.remove('d-none');
          if(!nblayers) {
              document.querySelector('#nblayers_text').classList.add('d-none');
          }
      }
    };
    xhr.send();
};

async function pageSignature(url) {

    document.querySelector('body').classList.add('bg-light');
    modalSharing();
    document.getElementById('page-upload').classList.add('d-none');
    document.getElementById('page-signature').classList.remove('d-none');
    fabric.Textbox.prototype._wordJoiners = /[]/;
    menu = document.getElementById('sidebarTools');
    menuOffcanvas = new bootstrap.Offcanvas(menu);
    forceAddLock = !is_mobile();
    addLock = forceAddLock;

    if(localStorage.getItem('svgCollections')) {
        svgCollections = JSON.parse(localStorage.getItem('svgCollections'));
    }

    if(svgCollections.length == 0 && document.querySelector('#alert-signature-help')) {
        document.querySelector('#alert-signature-help').classList.add('auto-open');
    }

    opentype.load(url_font, function(err, font) {
        fontCaveat = font;
    });

    if(url && url.match(/^cache:\/\//)) {
        await loadFileFromCache(url.replace(/^cache:\/\//, ''));
    } else if (url) {
        await loadFileFromUrl(url);
    }

    if(!document.getElementById('input_pdf_upload').files.length) {
        alert("Chargement du PDF impossible");
        document.location = '/signature';
        return;
    }

    if(document.getElementById('input_pdf_upload').files[0].size > maxSize) {

        alert("Le PDF ne doit pas dépasser " + convertOctet2MegoOctet(maxSize) + " Mo");
        document.getElementById('input_pdf_upload').value = "";
        return;
    }

    storePenColor(penColor)
    createSignaturePad();
    responsiveDisplay();
    displaysSVG();
    stateAddLock();
    createEventsListener();
    loadPDF(document.getElementById('input_pdf_upload').files[0]);
};

document.addEventListener('DOMContentLoaded', function () {
    if(sharingMode) {
        setTimeout(function() { runCron() }, 2000);
    }

    if(pdfHash) {
        if (window.location.hash && window.location.hash.match(/^\#/)) {
            storeSymmetricKeyCookie(pdfHash, window.location.hash.replace(/^#/, ''));
        } else if (getSymmetricKey(pdfHash)) {
            window.location.hash = getSymmetricKey(pdfHash);
        }
        pageSignature('/signature/'+pdfHash+'/pdf');
    } else if(window.location.hash && window.location.hash.match(/^\#http/)) {
        pageSignature(window.location.hash.replace(/^\#/, ''));
    } else if(window.location.hash) {
        pageSignature('cache:///pdf/'+window.location.hash.replace(/^\#/, ''));
    } else {
        pageUpload();
    }

    window.addEventListener('hashchange', function() {
        window.location.reload();
    })
});

function storePenColor(color) {
    penColor = color
    penColorPicker.value = color
    localStorage.setItem('penColor', penColor)
}

const toolBox = (function () {
    const _coloricon = document.createElement('img')
          _coloricon.src = 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-palette" viewBox="0 0 16 16"><path d="M8 5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3m4 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3M5.5 7a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m.5 6a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/><path d="M16 8c0 3.15-1.866 2.585-3.567 2.07C11.42 9.763 10.465 9.473 10 10c-.603.683-.475 1.819-.351 2.92C9.826 14.495 9.996 16 8 16a8 8 0 1 1 8-8m-8 7c.611 0 .654-.171.655-.176.078-.146.124-.464.07-1.119-.014-.168-.037-.37-.061-.591-.052-.464-.112-1.005-.118-1.462-.01-.707.083-1.61.704-2.314.369-.417.845-.578 1.272-.618.404-.038.812.026 1.16.104.343.077.702.186 1.025.284l.028.008c.346.105.658.199.953.266.653.148.904.083.991.024C14.717 9.38 15 9.161 15 8a7 7 0 1 0-7 7"/></svg>'

    function _renderIcon(icon) {
        return function renderIcon(ctx, left, top, styleOverride, fabricObject) {
            const size = this.cornerSize;
            ctx.save();
            ctx.translate(left, top);
            ctx.rotate(fabric.util.degreesToRadians(fabricObject.angle));
            ctx.drawImage(icon, -size/2, -size/2, size, size);
            ctx.restore();
        }
    }

    function _changeColor(eventData, transform) {
        const target = transform.target;
        const _colorpicker = document.createElement('input')
              _colorpicker.setAttribute('type', 'color')
              _colorpicker.value = penColor

        _colorpicker.addEventListener('input', function (e) {
            target.set({ fill: e.target.value })
            target.canvas.requestRenderAll()
            if(target.type != "rect") {
                storePenColor(e.target.value)
            }
        })

        _colorpicker.click()
        _colorpicker.remove()
    }

    function init(el) {
        colorControl = new fabric.Control({
            x: 0.5,
            y: -0.5,
            offsetY: -16,
            offsetX: 16,
            cursorStyle: 'pointer',
            mouseUpHandler: _changeColor,
            render: _renderIcon(_coloricon),
            cornerSize: 24
        })

        el.controls.color = colorControl
    }

    return {
        init: init
    }
})()
