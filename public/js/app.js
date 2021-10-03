
// Loaded via <script> tag, create shortcut to access PDF.js exports.
var pdfjsLib = window['pdfjs-dist/build/pdf'];

// The workerSrc property shall be specified.
pdfjsLib.GlobalWorkerOptions.workerSrc = '/vendor/pdf.worker.js?legacy';


// Asynchronous download of PDF
var loadingTask = pdfjsLib.getDocument(url);
loadingTask.promise.then(function(pdf) {
    
    var fontCaveat = null;
    var copiedObject = null;
    var activeCanvas = null;
    var activeCanvasPointer = null;
    var canvasEditions = [];
    var svgCollections = [];
    
    if(localStorage.getItem('svgCollections')) {
        svgCollections = JSON.parse(localStorage.getItem('svgCollections'));
    }

    opentype.load('/vendor/fonts/Caveat-Regular.ttf', function(err, font) {
        fontCaveat = font;
    });
    
    var getHtmlSvg = function(svg, i) {
        var inputRadio = document.createElement('input');
        inputRadio.type = "radio";
        inputRadio.classList.add("btn-check");
        inputRadio.id="radio_svg_"+i;
        inputRadio.name = "svg_2_add";
        inputRadio.autocomplete = "off";
        inputRadio.value = svg.svg;
        var svgButton = document.createElement('label');
        svgButton.classList.add('position-relative');
        svgButton.classList.add('btn');
        svgButton.classList.add('btn-outline-secondary');
        svgButton.htmlFor = "radio_svg_"+i;
        if(svg.type == 'signature') {
            svgButton.innerHTML += '<i class="bi bi-vector-pen text-black align-middle float-start"></i> ';
        }
        if(svg.type == 'initials') {
            svgButton.innerHTML += '<i class="bi bi-type text-black align-middle float-start"></i> ';
        }
        if(svg.type == 'rubber_stamber') {
            svgButton.innerHTML += '<i class="bi bi-card-text text-black align-middle float-start"></i> ';
        }
        if(svg.type) {
            document.querySelector('.btn-add-svg-type[data-type="'+svg.type+'"]').classList.add('d-none');
        }
        
        svgButton.innerHTML += '<a title="Supprimer" data-index="'+i+'" class="btn-svg-list-suppression opacity-50 link-dark position-absolute" style="right: 6px; top: 2px;"><i class="bi bi-trash"></i></a>';
        var svgImg = document.createElement('img');
        svgImg.src = svg.svg;
        svgImg.style = "max-width: 180px;max-height: 70px;";
        svgButton.appendChild(svgImg);
        var svgContainer = document.createElement('div');
        svgContainer.classList.add('d-grid');
        svgContainer.classList.add('gap-2');
        svgContainer.appendChild(inputRadio);
        svgContainer.appendChild(svgButton);
        
        return svgContainer;
    }
    
    var displaysSVG = function() {
        document.getElementById('svg_list').innerHTML = "";
        document.getElementById('svg_list_signature').innerHTML = "";
        document.getElementById('svg_list_initials').innerHTML = "";
        document.getElementById('svg_list_rubber_stamber').innerHTML = "";
        document.querySelectorAll('.btn-add-svg-type').forEach(function(item) {
            item.classList.remove('d-none');
        });
        svgCollections.forEach((svg, i) => {
            var svgHtmlChild = getHtmlSvg(svg, i);
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
                localStorage.setItem('svgCollections', JSON.stringify(svgCollections));
            });
        });

    }
    
    document.querySelectorAll('.btn-add-svg-type').forEach(function(item) {
        item.addEventListener('click', function(event) {
            document.getElementById('input-svg-type').value = this.dataset.type;
            if(this.dataset.modalnav) {
                bootstrap.Tab.getOrCreateInstance(document.querySelector('#modalAddSvg #nav-tab '+this.dataset.modalnav)).show();
            }
        });
    });

    displaysSVG();
    
    document.getElementById('btn_modal_ajouter').addEventListener('click', function() {
        var svgItem = {};
        if(document.getElementById('input-svg-type').value) {
            svgItem.type = document.getElementById('input-svg-type').value;
        }
        if(document.getElementById('nav-draw-tab').classList.contains('active')) {
            svgItem.svg = document.getElementById('img-upload').src;
        }
        if(document.getElementById('nav-type-tab').classList.contains('active')) {
            var fontPath = fontCaveat.getPath(document.getElementById('input-text-signature').value, 0, 0, 42);
            var fabricPath = new fabric.Path(fontPath.toPathData());
            fabricPath.top = 0;    
            fabricPath.left = 0;    
            fabricPath.height = fabricPath.getScaledHeight();
            var textCanvas = document.createElement('canvas');
            textCanvas.width = fabricPath.getScaledWidth();
            textCanvas.height = fabricPath.getScaledHeight();
            var textCanvas = new fabric.Canvas(textCanvas);
            textCanvas.add(fabricPath).renderAll();
            svgItem.svg = "data:image/svg+xml;base64,"+btoa(textCanvas.toSVG());
        }
        if(document.getElementById('nav-import-tab').classList.contains('active')) {
            svgItem.svg = document.getElementById('img-upload').src;
        }
        svgCollections.push(svgItem);
        displaysSVG();
        localStorage.setItem('svgCollections', JSON.stringify(svgCollections));

        var svg_list_id = "svg_list";
        if(svgItem.type) {
            svg_list_id = svg_list_id + "_" + svgItem.type;
        }

        document.querySelector('#'+svg_list_id+' label:last-child').click();
    });
    
    function dataURLtoBlob(dataurl) {
        var arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
            bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
        while(n--){
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new Blob([u8arr], {type:mime});
    }
    
    function trimSvgWhitespace(svgContent) {
        if(!svgContent) {
            
            return null;
        }
        var svgContainer = document.createElement("div")
        svgContainer.classList.add('invisible');
        svgContainer.classList.add('position-absolute');
        svgContainer.classList.add('top-0');
        svgContainer.classList.add('start-0');
        svgContainer.style = "z-index: -1;";
        svgContainer.innerHTML = svgContent;
        document.body.appendChild(svgContainer);
        var svg = svgContainer.querySelector('svg');
        var box = svg.getBBox();
        svg.setAttribute("viewBox", [box.x, box.y, box.width, box.height].join(" "));
        svgContent = svgContainer.innerHTML;
        document.body.removeChild(svgContainer)
        
        return svgContent = svgContainer.innerHTML;
    }
    
    var signaturePad = new SignaturePad(document.getElementById('signature-pad'), {
        penColor: 'rgb(0, 0, 0)',
        minWidth: 1.25,
        maxWidth: 2,
        throttle: 0,
        onEnd: function() { 
            document.getElementById('btn_modal_ajouter').setAttribute('disabled', 'disabled');

            const file = new File([dataURLtoBlob(signaturePad.toDataURL("image/svg+xml"))], "draw.svg", {
                type: 'image/svg+xml'
            });
            var data = new FormData();    
            data.append('file', file);
            xhr = new XMLHttpRequest();
            xhr.open( 'POST', document.getElementById('form-image-upload').action, true );
            xhr.onreadystatechange = function () { 
                var svgImage = "data:image/svg+xml;base64,"+btoa(trimSvgWhitespace(this.responseText));
                document.getElementById('img-upload').src = svgImage;
                document.getElementById('img-upload').classList.remove("d-none");
                document.getElementById('btn_modal_ajouter').removeAttribute('disabled');
                document.getElementById('btn_modal_ajouter').focus();
            };
            xhr.send( data );
        }
    });
    
    document.querySelectorAll('#modalAddSvg .nav-link').forEach(function(item) { item.addEventListener('shown.bs.tab', function (event) {
        var firstInput = document.querySelector(event.target.dataset.bsTarget).querySelector('input');
        if(firstInput) {
            firstInput.focus();
        }
    })});

    document.getElementById('modalAddSvg').addEventListener('shown.bs.modal', function (event) {
        document.querySelector('#modalAddSvg #nav-tab button:first-child').focus();
        var tab = document.querySelector('#modalAddSvg .tab-pane.active');
        if(tab.querySelector('input')) {
            tab.querySelector('input').focus();
        }
    })

    document.getElementById('modalAddSvg').addEventListener('hidden.bs.modal', function (event) {
        signaturePad.clear();
        document.getElementById('btn_modal_ajouter').setAttribute('disabled', 'disabled');
        document.getElementById('input-svg-type').value = null;
        document.getElementById('input-text-signature').value = null;
        document.getElementById('input-image-upload').value = null;
        document.getElementById('img-upload').src = null;
        document.getElementById('img-upload').classList.add("d-none");
        bootstrap.Tab.getOrCreateInstance(document.querySelector('#modalAddSvg #nav-tab button:first-child')).show();
    })
    
    document.getElementById('input-text-signature').addEventListener('keypress', function(event) {
        document.getElementById('btn_modal_ajouter').removeAttribute('disabled');
        if(event.key == 'Enter') {
            document.getElementById('btn_modal_ajouter').removeAttribute('disabled');
            document.getElementById('btn_modal_ajouter').click()
        }
    })

    document.getElementById('input-image-upload').addEventListener('change', function(event) {
        document.getElementById('btn_modal_ajouter').setAttribute('disabled', 'disabled');
        var data = new FormData();    
        data.append('file', document.getElementById('input-image-upload').files[0]);
        
        xhr = new XMLHttpRequest();
        
        xhr.open( 'POST', document.getElementById('form-image-upload').action, true );
        xhr.onreadystatechange = function () { 
            var svgImage = "data:image/svg+xml;base64,"+btoa(trimSvgWhitespace(this.responseText));
            document.getElementById('img-upload').src = svgImage;
            document.getElementById('img-upload').classList.remove("d-none");
            document.getElementById('btn_modal_ajouter').removeAttribute('disabled');
            document.getElementById('btn_modal_ajouter').focus();
        };
        xhr.send( data );

        event.preventDefault();
    });
    
    document.getElementById('save').addEventListener('click', function(event) {
        canvasEditions.forEach(function(canvasEdition, index) {
            document.getElementById('data-svg-'+index).value = canvasEdition.toSVG();
        })        
    });
    
    document.addEventListener('keydown', function(event) {
        if(event.target.tagName != "BODY") {
            return;
        }
        if(event.key == 'Delete') {
            canvasEditions.forEach(function(canvasEdition, index) {
                canvasEdition.getActiveObjects().forEach(function(activeObject) {
                    canvasEdition.remove(activeObject);
                });
            })
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
    });
    
    for(var pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++ ) {
        pdf.getPage(pageNumber).then(function(page) {
          var scale = 1.5;
          var viewport = page.getViewport({scale: scale});
          var pageIndex = page.pageNumber - 1;
          
          document.getElementById('form_pdf').insertAdjacentHTML('beforeend', '<input name="svg[' + pageIndex + ']" id="data-svg-' + pageIndex + '" type="hidden" value="" />');
          document.getElementById('container-pages').insertAdjacentHTML('beforeend', '<div class="position-relative mt-2 d-inline-block"><canvas id="canvas-pdf-'+pageIndex+'" class="shadow"></canvas><div class="position-absolute top-0 start-0"><canvas id="canvas-edition-'+pageIndex+'"></canvas></div></div><div></div>');
          
          var canvasPDF = document.getElementById('canvas-pdf-' + pageIndex);
          var canvasEditionHTML = document.getElementById('canvas-edition-' + pageIndex);
          
          // Prepare canvas using PDF page dimensions
          var context = canvasPDF.getContext('2d');
          canvasPDF.height = viewport.height;
          canvasPDF.width = viewport.width;
          canvasEditionHTML.height = viewport.height;
          canvasEditionHTML.width = viewport.width;

          var canvasEdition = new fabric.Canvas('canvas-edition-' + pageIndex, {'selection' : false});
          
          canvasEdition.on('mouse:move', function(event) {
              activeCanvas = this;
              activeCanvasPointer = event.pointer;
          });

          canvasEdition.on('mouse:dblclick', function(event) {
              var input_selected = document.querySelector('input[name="svg_2_add"]:checked');

              if(!input_selected) {
                  return;
              }

              save.removeAttribute('disabled');

              x = event.pointer.x
              y = event.pointer.y

              if(input_selected.value == 'text') {
                  var textbox = new fabric.Textbox('Texte à modifier', {
                  left: x - 10,
                  top: y - 10,
                  width: 300,
                  fontSize: 20
                });
                canvasEdition.add(textbox).setActiveObject(textbox);
                textbox.enterEditing();
                textbox.selectAll();
                
                return;
              }

              fabric.loadSVGFromURL(input_selected.value, function(objects, options) {
                  var svg = fabric.util.groupSVGElements(objects, options);
                  svg.scaleToHeight(100);
                  svg.top = y - (svg.getScaledHeight() / 2);
                  svg.left = x - (svg.getScaledWidth() / 2);
                  canvasEdition.add(svg).renderAll();
              });

          });
          
          canvasEditions.push(canvasEdition);

          var renderContext = {
            canvasContext: context,
            viewport: viewport,
            enhanceTextSelection: true
          };
          var renderTask = page.render(renderContext);
          renderTask.promise.then(function () {

          });
        });
    }
    }, function (reason) {
    console.error(reason);
});