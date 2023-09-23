var windowWidth = window.innerWidth;
var menu = null;
var menuOffcanvas = null;
var is_mobile = function() {
    return !(window.getComputedStyle(document.getElementById('is_mobile')).display === "none");
};
var hasTouch = function() {
    return 'ontouchstart' in document.documentElement
     || navigator.maxTouchPoints > 0
     || navigator.msMaxTouchPoints > 0;
}
var disabledHoverStyle = function() {
    try { // prevent exception on browsers not supporting DOM styleSheets properly
      for (var si in document.styleSheets) {
        var styleSheet = document.styleSheets[si];
        if (!styleSheet.rules) continue;

        for (var ri = styleSheet.rules.length - 1; ri >= 0; ri--) {
          if (!styleSheet.rules[ri].selectorText) continue;

          if (styleSheet.rules[ri].selectorText.match(':hover')) {
            styleSheet.deleteRule(ri);
          }
        }
      }
    } catch (ex) {}
}
var responsiveDisplay = function() {
    if(is_mobile()) {
        document.getElementById('page-organization').classList.remove('decalage-pdf-div');
        menu.classList.remove('show');
        menuOffcanvas.hide();
        document.getElementById('container-pages').classList.remove('vh-100');
        document.getElementById('container-btn-zoom').classList.add('d-none');
    } else {
        menuOffcanvas.show();
        document.getElementById('page-organization').classList.add('decalage-pdf-div');
        document.getElementById('container-pages').classList.add('vh-100');
        document.getElementById('container-btn-zoom').classList.remove('d-none');
    }
    menu.classList.remove('d-md-block');
    menu.classList.remove('d-none');
};
var isSelectionMode = function() {
    return document.querySelectorAll('.canvas-container .input-select:checked').length > 0;
}
var isDraggedMode = function() {
    return document.querySelectorAll('.canvas-container .input-drag:checked').length > 0;
}

var nbPagePerLine = 5;
if(is_mobile()) {
    nbPagePerLine = 2;
}
var pdfjsLib = window['pdfjs-dist/build/pdf'];
pdfjsLib.GlobalWorkerOptions.workerSrc = '/vendor/pdf.worker.js?legacy';
var nbPDF = 0;
var pages = [];
var pdfRenderTasks = [];

var loadPDF = async function(pdfBlob, filename, pdfIndex) {
    let url = await URL.createObjectURL(pdfBlob);

    let dataTransfer = new DataTransfer();
    let i = 0;
    for (i = 0; i < document.getElementById('input_pdf').files.length; i++) {
        dataTransfer.items.add(document.getElementById('input_pdf').files[i]);
    }
    dataTransfer.items.add(new File([pdfBlob], filename, {
        type: 'application/pdf'
    }));
    document.getElementById('input_pdf').files = dataTransfer.files;
    updateListePDF();

    let pdfLetter = String.fromCharCode(96 + i+1).toUpperCase();

    let loadingTask = pdfjsLib.getDocument(url);
    await loadingTask.promise.then(function(pdf) {
        for(var pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++ ) {
            pdf.getPage(pageNumber).then(function(page) {
                let pageIndex = pdfLetter + "_" + (page.pageNumber - 1);
                pages[pageIndex] = page;

                let pageHTML = '<div class="position-relative mt-0 ms-1 me-0 mb-1 canvas-container d-flex align-items-center justify-content-center bg-transparent bg-opacity-25 border border-2 border-transparent" id="canvas-container-' + pageIndex +'" draggable="true">';
                    pageHTML += '<canvas class="canvas-pdf shadow-sm"></canvas>';
                    pageHTML += '<div title="' + trad['Select this page'] + '" class="position-absolute top-0 start-50 translate-middle-x p-2 ps-3 pe-3 mt-2 rounded-circle btn-select d-none"><i class="bi bi-check-square"></i></div>';
                    pageHTML += '<div title="' + trad['Delete this page'] + '" class="position-absolute top-50 start-0 translate-middle-y p-2 ps-3 pe-3 ms-2 rounded-circle btn-delete d-none"><i class="bi bi-trash"></i></div>';
                    pageHTML += '<div title="' + trad['Restore this page'] + '" class="position-absolute top-50 start-50 translate-middle p-2 ps-3 pe-3 rounded-circle container-resize btn-restore d-none"><i class="bi bi-recycle"></i></div>';
                    pageHTML += '<div title="' + trad['Move this page'] + '" class="position-absolute top-50 start-50 translate-middle p-2 ps-3 pe-3 rounded-circle container-resize btn-drag d-none"><i class="bi bi-arrows-move"></i></div>';
                    pageHTML += '<div title="' + trad['Move here'] + '" class="position-absolute start-50 top-50 translate-middle p-2 ps-3 pe-3 container-resize btn-drag-here d-none"><i class="bi bi-arrow-up-square"></i></div>';
                    pageHTML += '<div title="' + trad['Move here'] + '" class="position-absolute top-50 start-50 translate-middle  rounded-circle container-resize btn-drag-here_mobile bg-white shadow d-none"><i class="bi bi-arrows-collapse"></i></div>';
                    pageHTML += '<div title="' + trad['Turn this page'] + '" class="position-absolute top-50 end-0 translate-middle-y p-2 ps-3 pe-3 me-2 rounded-circle container-rotate btn-rotate d-none"><i class="bi bi-arrow-clockwise"></i></div>';
                    pageHTML += '<div title="' + trad['Download this page'] + '" class="position-absolute bottom-0 start-50 translate-middle-x p-2 ps-3 pe-3 mb-3 rounded-circle btn-download d-none"><i class="bi bi-download"></i></div>';
                    pageHTML += '<p class="page-title position-absolute text-center w-100 ps-2 pe-2 pb-0 pt-0 mb-1 bg-white opacity-75 d-none" style="bottom: -4px; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">Page '+page.pageNumber+' - '+filename+'</p>';
                    pageHTML += '<input form="form_pdf" class="checkbox-page d-none" role="switch" type="checkbox" checked="checked" value="'+pdfLetter+page.pageNumber+'" />';
                    pageHTML += '<input type="hidden" class="input-rotate" value="0" id="input_rotate_'+pageIndex+'" />';
                    pageHTML += '<input type="checkbox" class="input-select d-none" value="'+pdfLetter+page.pageNumber+'" id="input_select_'+pageIndex+'" />';
                    pageHTML += '<input type="checkbox" class="input-hover d-none" value="'+pdfLetter+page.pageNumber+'" id="input_select_'+pageIndex+'" />';
                    pageHTML += '<input type="checkbox" class="input-drag d-none" value="'+pdfLetter+page.pageNumber+'" id="input_drag_'+pageIndex+'" />';
                pageHTML += '</div>';

                document.getElementById('container-pages').insertAdjacentHTML('beforeend', pageHTML);

                let canvasContainer = document.getElementById('canvas-container-' + pageIndex);
                canvasContainer.addEventListener('click', function(e) {
                    if(isPageDeleted(this) || isPageDragged(this) || isDraggedMode()) {
                        return;
                    }
                    canvasContainer.querySelector('.btn-select').click();
                });
                canvasContainer.addEventListener('mouseenter', function(e) {
                    if(is_mobile()) {
                        return false;
                    }
                    this.querySelector('input[type=checkbox].input-hover').checked = true;
                    updatePageState(this);
                });
                canvasContainer.addEventListener('mouseleave', function(e) {
                    this.querySelector('input[type=checkbox].input-hover').checked = false;
                    updatePageState(this);
                });
                canvasContainer.addEventListener('dragstart', function(e) {
                    if(is_mobile()) {
                        return false;
                    }
                    if(isDraggedMode()) {
                        return false;
                    }
                    this.querySelector('.container-resize').classList.add('d-none');
                    this.querySelector('.canvas-pdf').classList.add('shadow-lg');
                    this.querySelector('.canvas-pdf').style.border = '2px dashed #777';
                    e.dataTransfer.setData('element', this.id);
                    this.style.opacity = 0.4;
                });
                canvasContainer.addEventListener('dragend', function(e) {
                    this.querySelector('.container-resize').classList.remove('d-none');
                    this.querySelector('.canvas-pdf').classList.remove('shadow-lg');
                    this.querySelector('.canvas-pdf').style.removeProperty('border');
                    this.style.opacity = 1;
                    updatePageState(this);
                });
                canvasContainer.addEventListener('dragover', function(e) {
                    if (e.preventDefault) {
                        e.preventDefault();
                    }
                    let pdfOver = this;
                    let pdfMoving = document.querySelector('#'+e.dataTransfer.getData('element'));

                    if(pdfOver.id == pdfMoving.id) {

                        return;
                    }

                    let leftRight = false;
                    let topBottom = false;

                    if(pdfOver.offsetTop < pdfMoving.offsetTop) {
                        topBottom = 'top';
                    }

                    if(pdfOver.offsetTop > pdfMoving.offsetTop) {
                        topBottom = 'bottom';
                    }

                    if(pdfOver.offsetLeft > pdfMoving.offsetLeft) {
                        leftRight = 'right';
                    }

                    if(pdfOver.offsetLeft < pdfMoving.offsetLeft) {
                        leftRight = 'left';
                    }

                    if (leftRight == 'left' || topBottom == 'top') {
                        pdfOver.insertAdjacentElement('beforebegin', pdfMoving);
                    }

                    if (leftRight == 'right' || topBottom == 'bottom') {
                        pdfOver.insertAdjacentElement('afterend', pdfMoving);
                    }

                    return false;
                });
                canvasContainer.querySelector('.btn-delete').addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleDeletePage(this.parentNode);
                });
                canvasContainer.querySelector('.btn-restore').addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleDeletePage(this.parentNode);
                });
                canvasContainer.querySelector('.btn-select').addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleSelectPage(this.parentNode);
                });
                canvasContainer.querySelector('.btn-drag').addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleSelectPage(this.parentNode);
                    document.getElementById('btn_drag_select').click();
                });
                canvasContainer.querySelector('.btn-drag-here_mobile').addEventListener('click', function(e) {
                    e.stopPropagation();
                    movePagesDragged(this.parentNode, 'right');
                });
                canvasContainer.querySelector('.btn-drag-here').addEventListener('click', function(e) {
                    e.stopPropagation();
                    movePagesDragged(this.parentNode, 'right');
                });
                canvasContainer.querySelector('.btn-download').addEventListener('click', function(e) {
                    e.stopPropagation();
                    let container = this.parentNode;
                    let pageValue = container.querySelector('.checkbox-page').value;
                    let orientation = degreesToOrientation(container.querySelector('.input-rotate').value);
                    if(orientation) {
                        pageValue = pageValue + "-" + orientation;
                    }
                    document.querySelector('#input_pages').value = pageValue;
                    document.querySelector('#form_pdf').submit();
                });
                canvasContainer.querySelector('.btn-rotate').addEventListener('click', function(e) {
                    e.stopPropagation();
                    let inputRotate = this.parentNode.querySelector('.input-rotate');
                    inputRotate.value = (parseInt(inputRotate.value) + 90) % 360;
                    pageRender(pageIndex);
                })

                pageRender(pageIndex);
            });
        }
    }, function (reason) {
        console.error(reason);
    });

    return loadingTask;
};

var pageRenderAll = function() {
    for(pageIndex in pages) {
        pageRender(pageIndex);
    }
}

var pageRender = async function(pageIndex) {
  let scrollWidth = 12;
  if(is_mobile()) {
      scrollWidth = -4;
  }
  let page = pages[pageIndex];
  let rotation = parseInt(document.querySelector('#input_rotate_'+pageIndex).value);
  let viewport = page.getViewport({scale: 1, rotation: rotation});
  let sizeWidth = Math.floor((document.getElementById('container-pages').offsetWidth - (8*(nbPagePerLine+1)) - scrollWidth) / nbPagePerLine);
  let sizeHeight = sizeWidth * 1.25;
  let scaleWidth = sizeWidth / viewport.width;
  let scaleHeight = sizeHeight / viewport.height;
  let viewportWidth = page.getViewport({scale: scaleWidth, rotation: rotation});
  let viewportHeight = page.getViewport({scale: scaleHeight, rotation: rotation});

  if(viewportWidth.height > sizeWidth) {
      viewport = viewportHeight;
  } else {
      viewport = viewportWidth;
  }

  let canvasContainer = document.getElementById('canvas-container-' + pageIndex);
  canvasContainer.style.height = (sizeHeight + 4) + "px";
  canvasContainer.style.width = (sizeWidth + 4) + "px";
  let canvasPDF = canvasContainer.querySelector('.canvas-pdf');
  let context = canvasPDF.getContext('2d');
  canvasPDF.height = viewport.height;
  canvasPDF.width = viewport.width;

  if(pdfRenderTasks[pageIndex]) {
    pdfRenderTasks[pageIndex].cancel();
  }
  pdfRenderTasks[pageIndex] = await page.render({
    canvasContext: context,
    viewport: viewport,
  });
}

var getFileIndex = function(page) {

    return page.id.replace('canvas-container-', '').replace(/_.*$/, '');
}

var getFilesStats = function() {
    let files = [];
    document.querySelectorAll('.canvas-container').forEach(function(page) {
        let fileIndex = getFileIndex(page);
        if(!files[fileIndex]) {
            files[fileIndex] = { nbPage: 0, nbPageSelected: 0, nbPageDeleted: 0};
        }

        if(isPageDeleted(page)) {
            files[fileIndex].nbPageDeleted++;
        } else {
            files[fileIndex].nbPage++;
        }

        if(isPageSelected(page)) {
            files[fileIndex].nbPageSelected++;
        }
    });

    return files;
}

var updateListePDF = function() {
    document.querySelector('#list_pdf').innerHTML = "";
    let nbFiles = document.querySelector('#input_pdf').files.length;
    for (var i = 0; i < nbFiles; i++) {
        let pdfLetter = String.fromCharCode(96 + i+1).toUpperCase();
        const pdfFile = document.querySelector('#input_pdf').files.item(i);
        document.querySelector('#list_pdf').insertAdjacentHTML('beforeend', '<li id="file_' + pdfLetter + '" class="list-group-item small ps-2 pe-5" title="'+decodeURI(pdfFile.name)+'" style="text-overflow: ellipsis; white-space: nowrap; overflow: hidden;"><i class="bi bi-files"></i><span class="ms-2">'+decodeURI(pdfFile.name)+'</span> <input class="form-check-input float-end position-absolute file-list-checkbox" type="checkbox" /> </li>');
        let fileItem = document.querySelector('#file_' + pdfLetter);
        fileItem.querySelector('input[type=checkbox]').addEventListener('change', function(e) {
            document.querySelectorAll('.canvas-container').forEach(function(page) {
                if(getFileIndex(page) == pdfLetter && !isPageDeleted(page)) {
                    selectPage(page, e.target.checked);
                }
            });
            updateGlobalState();
        });
        document.querySelector('#liste_pdf_titre_mobile').innerText = decodeURI(pdfFile.name);
        document.querySelector('#btn_liste_pdf_bar span').innerText = nbFiles;
        if(nbFiles > 1) {
            document.querySelector('#liste_pdf_titre_mobile').innerText = nbFiles + ' documents PDF';
        }
    }
    updateGlobalState();
}

var getPagesSelected = function() {
    let pages = [];
    document.querySelectorAll('.canvas-container .input-select:checked').forEach(function(item) {
        pages[item.parentNode.id.replace('canvas-container-', '')] = item.parentNode;
    });

    return pages;
}

var selectPage = function(page, state) {
    page.querySelector('input[type=checkbox].input-select').checked = state;
    updatePageState(page);
}

var toggleSelectPage = function(page) {
    selectPage(page, !isPageSelected(page));
    updateGlobalState();
}

var isPageSelected = function(page) {

    return page.querySelector('input[type=checkbox].input-select').checked;
}

var dragPage = function(page, state) {
    page.querySelector('input[type=checkbox].input-drag').checked = state;
    updatePageState(page);
}

var toggleDragPage = function(page) {
    dragPage(page, !isPageDragged(page));
    updateGlobalState();
    document.querySelectorAll('.canvas-container').forEach(function(page) {
        updatePageState(page);
    });
}

var isPageDragged = function(page) {

    return page.querySelector('input[type=checkbox].input-drag').checked;
}

var movePagesDragged = function(pageHere, position) {
    document.querySelectorAll('.canvas-container .input-drag:checked').forEach(function(item) {
        let page = item.parentNode;
        if(position == 'right') {
            pageHere.insertAdjacentElement('afterend', page);
        } else {
            pageHere.insertAdjacentElement('beforebegin', page);
        }
    });
    document.getElementById('btn_drag_select').click();
}

var toggleDeletePage = function(page) {
    deletePage(page, isPageDeleted(page))
    updateGlobalState();
}

var deletePage = function(page, state) {
    page.querySelector('input[type=checkbox].checkbox-page').checked = state;
    page.querySelector('input[type=checkbox].input-select').checked = false;
    updatePageState(page);
}

var isPageDeleted = function(page) {

    return !page.querySelector('input[type=checkbox].checkbox-page').checked;
}

var isPageHover = function(page) {

    return page.querySelector('input[type=checkbox].input-hover').checked;
}

var updatePageState = function(page) {
    page.classList.remove('border-primary', 'shadow-sm', 'bg-primary', 'border-secondary', 'bg-secondary');
    page.classList.add('border-transparent', 'bg-transparent');
    page.querySelector('.canvas-pdf').style.opacity = '1';
    page.querySelector('.canvas-pdf').style.zIndex = 'inherit';
    page.querySelector('.canvas-pdf').classList.add('shadow-sm');
    page.querySelector('.canvas-pdf').classList.remove('shadow');
    page.querySelector('.btn-rotate').classList.add('d-none');
    page.querySelector('.btn-download').classList.add('d-none');
    page.querySelector('.btn-delete').classList.add('d-none');
    page.querySelector('.btn-select').classList.add('d-none');
    page.querySelector('.btn-select').classList.remove('text-primary');
    page.querySelector('.btn-drag').classList.add('d-none');
    page.querySelector('.btn-drag-here').classList.add('d-none');
    page.querySelector('.btn-drag-here_mobile').classList.add('d-none');
    page.querySelector('.btn-restore').classList.add('d-none');
    page.querySelector('.page-title').classList.add('d-none');
    page.querySelector('.canvas-pdf').classList.remove('opacity-50');
    page.classList.remove('page-dragged');
    page.draggable = true;

    if(isSelectionMode()) {
        page.draggable = false;
    }

    if(isPageDeleted(page)) {
        page.querySelector('.canvas-pdf').style.opacity = '0.15';
    }

    if(isPageHover(page) && !isPageDeleted(page) && !isPageDragged(page) && !isPageSelected(page) && !isDraggedMode()) {
        page.querySelector('.page-title').classList.remove('d-none');
        page.classList.add('border-secondary', 'bg-secondary');
        page.classList.remove('border-transparent', 'bg-transparent');
        page.querySelector('.btn-select').classList.remove('d-none')
    }

    if(isPageHover(page) && !isPageDeleted(page) && !isPageDragged(page) && !isPageSelected(page) && !isDraggedMode() && !isSelectionMode()) {
        page.querySelector('.btn-rotate').classList.remove('d-none');
        page.querySelector('.btn-download').classList.remove('d-none');
        page.querySelector('.btn-delete').classList.remove('d-none');
        page.querySelector('.btn-drag').classList.remove('d-none');
    }

    if(isPageHover(page) && isPageDeleted(page)) {
        page.querySelector('.btn-restore').classList.remove('d-none');
    }

    if(is_mobile() && isPageDeleted(page)) {
        page.querySelector('.btn-restore').classList.remove('d-none');
    }

    if(isPageSelected(page)) {
        page.querySelector('.page-title').classList.remove('d-none');
        page.classList.add('border-primary', 'shadow-sm', 'bg-primary');
        page.classList.remove('border-transparent', 'bg-transparent', 'border-secondary', 'bg-secondary');
        page.querySelector('.btn-select').classList.add('text-primary');
        page.querySelector('.btn-select').classList.remove('d-none');
    }

    if(isPageDragged(page)) {
        page.classList.add('page-dragged');
        page.querySelector('.canvas-pdf').classList.remove('shadow-sm');
        page.querySelector('.canvas-pdf').classList.add('shadow');
    }

    if(!isPageDragged(page) && isDraggedMode()) {
        page.querySelector('.canvas-pdf').classList.add('opacity-50');
        page.querySelector('.btn-drag-here').classList.remove('d-none');
    }
}

var updateFilesState = function() {
    let filesStats = getFilesStats();
    for(fileIndex in filesStats) {
        let checkbox = document.querySelector('#file_'+fileIndex+' input[type=checkbox]');
        let fileStat = filesStats[fileIndex];
        checkbox.checked = (fileStat.nbPageSelected > 0 && fileStat.nbPageSelected == fileStat.nbPage);
        checkbox.indeterminate = (fileStat.nbPageSelected > 0 && fileStat.nbPageSelected < fileStat.nbPage);
        document.querySelector('#file_'+fileIndex+' span').classList.remove('text-primary');
        if(fileStat.nbPageSelected > 0) {
            document.querySelector('#file_'+fileIndex+' span').classList.add('text-primary');
        }
    }
}

var updateGlobalState = function() {
    updateFilesState();
    if(!is_mobile()) {
        document.querySelector('#container-btn-zoom').classList.remove('d-none');
    }
    document.querySelector('#container_btn_select').classList.add('opacity-50');
    document.querySelector('#container_btn_select').classList.remove('border-primary');
    document.querySelector('#container_btn_select .card-header').classList.remove('bg-primary', 'text-white');
    document.querySelector('#container_btn_select .card-header').classList.add('text-muted');
    document.querySelectorAll('#container_btn_select .card-body button').forEach(function(button) {
        button.classList.add('btn-outline-secondary');
        button.classList.remove('btn-outline-primary');
        button.setAttribute('disabled', 'disabled');
    });
    document.querySelector('#container_btn_select .card-header span').innerText = "0";
    document.querySelector('#container_btn_select .card-footer').classList.add('d-none');
    document.querySelector('#top_bar_action').classList.remove('d-none');
    document.querySelector('#top_bar_action_selection').classList.add('d-none');
    document.querySelector('#bottom_bar_action').classList.remove('d-none');
    document.querySelector('#bottom_bar_action_selection').classList.add('d-none');
    document.querySelector('#save').removeAttribute('disabled');

    if(isSelectionMode()) {
        document.querySelector('#container_btn_select .card-header span').innerText = document.querySelectorAll('.canvas-container .input-select:checked').length;
        document.querySelector('#top_bar_action_selection_recap_nb_pages').innerText = document.querySelectorAll('.canvas-container .input-select:checked').length;
        document.querySelector('#container_btn_select').classList.remove('opacity-50');
        document.querySelector('#container_btn_select').classList.add('border-primary');
        document.querySelector('#container_btn_select .card-header').classList.remove('text-muted');
        document.querySelector('#container_btn_select .card-header').classList.add('bg-primary', 'text-white');
        document.querySelectorAll('#container_btn_select .card-body button').forEach(function(button) {
            button.classList.add('btn-outline-primary');
            button.classList.remove('btn-outline-secondary');
            button.removeAttribute('disabled');
        });
        document.querySelector('#container_btn_select .card-footer').classList.remove('d-none');
        document.querySelectorAll('.canvas-container .btn-add').forEach(function(button) {
            button.classList.remove('d-none');
        });
        document.querySelector('#top_bar_action_selection').classList.remove('d-none');
        document.querySelector('#top_bar_action').classList.add('d-none');
        document.querySelector('#bottom_bar_action_selection').classList.remove('d-none');
        document.querySelector('#bottom_bar_action').classList.add('d-none');
        document.querySelector('#save').setAttribute('disabled', 'disabled');
    }
}

var degreesToOrientation = function(degrees) {
    if(degrees == 90) { return "east"; }
    if(degrees == 180) { return "south"; }
    if(degrees == 270) { return "west"; }

    return null;
}

var uploadAndLoadPDF = async function(input_upload) {
    const cache = await caches.open('pdf');
    for (let i = 0; i < input_upload.files.length; i++) {
        if(input_upload.files[i].size > maxSize) {

            alert("Le PDF ne doit pas dépasser " + Math.round(maxSize/1024/1024) + " Mo");
            break;
        }
        let filename = input_upload.files[i].name;
        let response = new Response(input_upload.files[i], { "status" : 200, "statusText" : "OK" });
        let urlPdf = '/pdf/'+filename;
        await cache.put(urlPdf, response);
        let pdfBlob = await getPDFBlobFromCache(urlPdf);
        nbPDF++;
        await loadPDF(pdfBlob, filename, nbPDF);
    }
}

var createEventsListener = function() {
    document.getElementById('save-select_mobile').addEventListener('click', function(event) {
        document.getElementById('save-select').click();
    });
    document.getElementById('save-select').addEventListener('click', function(event) {
        let buttonSave = document.getElementById('save');
        let buttonSaveDisabledState = buttonSave.disabled;
        if(buttonSave.disabled) {
            buttonSave.disabled = false;
        }
        buttonSave.click();
        buttonSave.disabled = true;
    });
    document.getElementById('save').addEventListener('click', function(event) {
        let order = [];

        let selectionMode = isSelectionMode();

        document.querySelectorAll('.canvas-container').forEach(function(canvasContainer) {
            let checkbox = canvasContainer.querySelector('.checkbox-page');
            if(selectionMode) {
                checkbox = canvasContainer.querySelector('.input-select');
            }
            let inputRotate = canvasContainer.querySelector('.input-rotate');
            let pageValue = "";
            if(checkbox.checked) {
                pageValue = checkbox.value;
            }
            let orientation = degreesToOrientation(inputRotate.value);
            if(pageValue && orientation) {
                pageValue = pageValue + "-" + orientation;
            }
            if(pageValue) {
                order.push(pageValue);
            }
        });
        document.querySelector('#input_pages').value = order.join(',');
    });
    document.getElementById('save_mobile').addEventListener('click', function(event) {
        document.getElementById('save').click();
    });
    document.getElementById('input_pdf_upload_2').addEventListener('change', async function(event) {
        await uploadAndLoadPDF(this);
        this.value = '';
    });
    document.getElementById('btn-zoom-decrease').addEventListener('click', function(event) {
        nbPagePerLine++;
        pageRenderAll();
    });
    document.getElementById('btn-zoom-increase').addEventListener('click', function(event) {
        nbPagePerLine--;
        pageRenderAll();
    });
    document.getElementById('btn_cancel_select_footer').addEventListener('click', function(event) {
        document.getElementById('btn_cancel_select').click();
    });
    document.getElementById('btn_cancel_select_mobile').addEventListener('click', function(event) {
        document.getElementById('btn_cancel_select').click();
    });
    document.getElementById('btn_cancel_select').addEventListener('click', function(event) {
        if(isDraggedMode()) {
            document.getElementById('btn_drag_select').click();
        }
        document.querySelectorAll('.input-select:checked').forEach(function(input) {
            input.parentNode.querySelector('.btn-select').click();
        });
    });
    document.getElementById('btn_delete_select_mobile').addEventListener('click', function(event) {
        document.getElementById('btn_delete_select').click();
    });
    document.getElementById('btn_delete_select').addEventListener('click', function(event) {
        if(isDraggedMode()) {
            document.getElementById('btn_drag_select').click();
        }
        let pages = getPagesSelected();
        for(index in pages) {
            deletePage(pages[index]);
        }
        updateGlobalState();
    });
    document.getElementById('btn_rotate_select_mobile').addEventListener('click', function(event) {
        document.getElementById('btn_rotate_select').click();
    });
    document.getElementById('btn_rotate_select').addEventListener('click', function(event) {
        let pages = getPagesSelected();
        for(index in pages) {
            let inputRotate = pages[index].querySelector('.input-rotate');
            inputRotate.value = (parseInt(inputRotate.value) + 90) % 360;
            pageRender(index);
        }
    });
    document.getElementById('btn_drag_select').addEventListener('click', function(event) {
        let pages = getPagesSelected();
        for(index in pages) {
            toggleDragPage(pages[index]);
        }
        this.classList.toggle('active');
        document.getElementById('btn_drag_select_mobile').classList.toggle('active');
    });
    document.getElementById('btn_drag_select_mobile').addEventListener('click', function(event) {
        document.getElementById('btn_drag_select').click();
    });
    document.querySelector('#btn_liste_pdf').addEventListener('click', function(event) {
        bootstrap.Modal.getOrCreateInstance(document.querySelector('#modalFichier')).show();
        document.querySelector('#modalFichier .modal-body').insertAdjacentElement('afterbegin', document.querySelector('#list_pdf'));
    });
    document.querySelector('#btn_liste_pdf_bar').addEventListener('click', function(event) {
        document.querySelector('#btn_liste_pdf').click();
    });
    document.querySelector('body').addEventListener('click', function(event) {
        if(!event.originalTarget.classList.contains('offcanvas-header') && !event.originalTarget.classList.contains('offcanvas-body') && event.originalTarget.id != 'container-pages' && event.originalTarget.id != 'sidebarToolsLabel' && event.originalTarget.id != 'btn_container') {
            return;
        }
        document.getElementById('btn_cancel_select').click();
    });

}

async function getPDFBlobFromCache(cacheUrl) {
    const cache = await caches.open('pdf');
    let responsePdf = await cache.match(cacheUrl);

    if(!responsePdf) {
        return null;
    }

    let pdfBlob = await responsePdf.blob();

    return pdfBlob;
}

async function uploadFromUrl(url) {
    history.replaceState({}, '', '/organization');
    var response = await fetch(url);
    if(response.status != 200) {
        return;
    }
    var pdfBlob = await response.blob();

    if(pdfBlob.type != 'application/pdf' && pdfBlob.type != 'application/octet-stream') {
        return;
    }
    let dataTransfer = new DataTransfer();
    let filename = url.replace(/^.*\//, '');
    dataTransfer.items.add(new File([pdfBlob], filename, {
        type: 'application/pdf'
    }));
    document.getElementById('input_pdf_upload').files = dataTransfer.files;
    document.getElementById('input_pdf_upload').dispatchEvent(new Event("change"));
}

var pageUpload = async function() {
    document.querySelector('body').classList.remove('bg-light');
    document.getElementById('input_pdf_upload').value = '';
    document.getElementById('page-upload').classList.remove('d-none');
    document.getElementById('page-organization').classList.add('d-none');
    document.getElementById('input_pdf_upload').focus();
    let cache;
    try {
        cache = await caches.open('pdf');
    } catch (e) {
        console.error(e)
        alert("Erreur d'accès au cache. Cette application ne fonctionne pas en mode de navigation privée");
        return;
    }
    document.getElementById('input_pdf_upload').addEventListener('change', async function(event) {
        uploadAndLoadPDF(this);
        pageOrganization(null);
    });
}

var pageOrganization = async function() {
    document.querySelector('body').classList.add('bg-light');
    document.getElementById('page-upload').classList.add('d-none');
    document.getElementById('page-organization').classList.remove('d-none');
    menu = document.getElementById('sidebarTools');
    menuOffcanvas = new bootstrap.Offcanvas(menu);
    responsiveDisplay();
    createEventsListener();
};

(function () {
    if(window.location.hash && window.location.hash.match(/^\#http/)) {
        let hashUrl = window.location.hash.replace(/^\#/, '');
        pageUpload();
        uploadFromUrl(hashUrl);
    } else {
        pageUpload();
    }
    window.addEventListener('hashchange', function() {
        window.location.reload();
    })

    if (hasTouch()) {
        disabledHoverStyle();
    }
})();