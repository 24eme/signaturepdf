var windowWidth = window.innerWidth;
var menu = null;
var menuOffcanvas = null;
var is_mobile = function() {
    return !(window.getComputedStyle(document.getElementById('is_mobile')).display === "none");
};
var responsiveDisplay = function() {
    if(is_mobile()) {
        document.getElementById('page-organization').style.paddingRight = "inherit";
        menu.classList.remove('show');
        menuOffcanvas.hide();
        document.getElementById('container-pages').classList.remove('vh-100');
    } else {
        menuOffcanvas.show();
        document.getElementById('page-organization').style.paddingRight = "350px";
        document.getElementById('container-pages').classList.add('vh-100');
    }
    menu.classList.remove('d-md-block');
    menu.classList.remove('d-none');
};
var isSelectionMode = function() {
    return document.querySelectorAll('.canvas-container .input-select:checked').length > 0;
}
var getPagesSelected = function() {
    let pages = [];
    document.querySelectorAll('.canvas-container .input-select:checked').forEach(function(item) {
        pages[item.parentNode.id.replace('canvas-container-', '')] = item.parentNode;
    });

    return pages;
}

var nbPagePerLine = 5;
if(is_mobile()) {
    nbPagePerLine = 1;
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
    loadingTask.promise.then(function(pdf) {
        for(var pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++ ) {
            pdf.getPage(pageNumber).then(function(page) {
                let pageIndex = pdfLetter + "_" + (page.pageNumber - 1);
                pages[pageIndex] = page;

                let pageHTML = '<div class="position-relative mt-0 ms-1 me-0 mb-1 canvas-container d-flex align-items-center justify-content-center bg-transparent bg-opacity-25 border border-2 border-transparent" id="canvas-container-' + pageIndex +'" draggable="true">';
                    pageHTML += '<canvas class="canvas-pdf shadow-sm"></canvas>';
                    pageHTML += '<div class="position-absolute top-0 start-50 translate-middle-x p-2 ps-3 pe-3 mt-2 rounded-circle btn-select"><i class="bi bi-check-square"></i></div>';
                    pageHTML += '<div class="position-absolute top-50 start-0 translate-middle-y p-2 ps-3 pe-3 ms-2 rounded-circle btn-delete"><i class="bi bi-trash"></i></div>';
                    pageHTML += '<div class="position-absolute top-50 start-50 translate-middle p-2 ps-3 pe-3 rounded-circle container-resize btn-drag"><i class="bi bi-arrows-move"></i></div>';
                    pageHTML += '<div class="position-absolute top-50 end-0 translate-middle-y p-2 ps-3 pe-3 me-2 rounded-circle container-rotate btn-rotate"><i class="bi bi-arrow-clockwise"></i></div>';
                    pageHTML += '<div class="position-absolute bottom-0 start-50 translate-middle-x p-2 ps-3 pe-3 mb-3 rounded-circle btn-download"><i class="bi bi-download"></i></div>';
                    pageHTML += '<div class="position-absolute text-center w-100 pt-1 container-checkbox pb-4 d-none" style="background: rgb(255,255,255,0.8); bottom: 0; cursor: pointer;"><div class="form-switch d-none"><input form="form_pdf" class="form-check-input checkbox-page" role="switch" type="checkbox" checked="checked" style="cursor: pointer;" value="'+pdfLetter+page.pageNumber+'" /></div></div>';
                    pageHTML += '<div class="position-absolute text-center w-100 pt-1 container-checkbox pb-4 d-none" style="background: rgb(255,255,255,0.8); bottom: 0; cursor: pointer;"><div class="form-switch d-none"><input form="form_pdf" class="form-check-input checkbox-page" role="switch" type="checkbox" checked="checked" style="cursor: pointer;" value="'+pdfLetter+page.pageNumber+'" /></div></div>';
                    pageHTML += '<p class="page-title position-absolute text-center w-100 ps-2 pe-2 pb-0 pt-0 mb-1 bg-white opacity-75 d-none" style="bottom: -4px; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">Page '+page.pageNumber+' - '+filename+'</p>';
                    pageHTML += '<input type="hidden" class="input-rotate" value="0" id="input_rotate_'+pageIndex+'" />';
                    pageHTML += '<input type="checkbox" class="input-select d-none" value="'+pdfLetter+page.pageNumber+'" id="input_select_'+pageIndex+'" />';
                pageHTML += '</div>';

                document.getElementById('container-pages').insertAdjacentHTML('beforeend', pageHTML);

                let canvasContainer = document.getElementById('canvas-container-' + pageIndex);
                canvasContainer.addEventListener('mouseenter', function(e) {
                    this.querySelector('.page-title').classList.remove('d-none');
                    if(this.querySelector('input[type=checkbox].input-select').checked) {
                        return;
                    }
                    this.classList.add('border-secondary', 'bg-secondary');
                    this.classList.remove('border-transparent', 'bg-transparent');
                });
                canvasContainer.addEventListener('mouseleave', function(e) {
                    this.querySelector('.page-title').classList.add('d-none');
                    if(this.querySelector('input[type=checkbox].input-select').checked) {
                        return;
                    }
                    this.classList.remove('border-secondary', 'bg-secondary');
                    this.classList.add('border-transparent', 'bg-transparent');
                });
                canvasContainer.addEventListener('dragstart', function(e) {
                    this.querySelector('.container-resize').classList.add('d-none');
                    this.querySelector('.canvas-pdf').classList.add('shadow-lg');
                    this.querySelector('.canvas-pdf').style.border = '2px dashed #777';
                    e.dataTransfer.setData('element', this.id);
                    this.style.opacity = 0.4;
                });
                canvasContainer.addEventListener('dragend', function(e) {
                    this.querySelector('.container-resize').classList.remove('d-none');
                    this.querySelector('.canvas-pdf').classList.remove('shadow-lg');
                    this.querySelector('.canvas-pdf').style.border = '2px solid transparent';
                    this.style.opacity = 1;
                    stateCheckbox(this.querySelector('input[type=checkbox]'));
                });
                canvasContainer.addEventListener('dragover', function(e) {
                    if (e.preventDefault) {
                        e.preventDefault();
                    }
                    if(e.layerX > e.target.clientWidth / 2) {
                        this.insertAdjacentElement('beforebegin', document.querySelector('#'+e.dataTransfer.getData('element')));
                    } else {
                        this.insertAdjacentElement('afterend', document.querySelector('#'+e.dataTransfer.getData('element')));
                    }

                    return false;
                });
                canvasContainer.querySelector('.btn-delete').addEventListener('click', function(e) {
                    let checkbox = this.parentNode.querySelector('input[type=checkbox]');
                    checkbox.checked = !checkbox.checked;
                    stateCheckbox(checkbox);
                });
                canvasContainer.querySelector('.btn-select').addEventListener('click', function(e) {
                    let checkbox = this.parentNode.querySelector('input[type=checkbox].input-select');
                    checkbox.checked = !checkbox.checked;
                    let container = this.parentNode;
                    if(checkbox.checked) {
                        container.classList.add('border-primary', 'shadow-sm', 'bg-primary');
                        container.classList.remove('border-transparent', 'bg-transparent', 'border-secondary', 'bg-secondary');
                    } else {
                        container.classList.remove('border-primary', 'shadow-sm', 'bg-primary');
                        container.classList.add('border-transparent', 'bg-transparent');
                    }
                    if(document.querySelectorAll('.canvas-container .input-select:checked').length > 0) {
                        document.querySelector('#container-btn-save-select').classList.remove('d-none');
                        document.querySelector('#container-btn-save').classList.add('d-none');
                    } else {
                        document.querySelector('#container-btn-save-select').classList.add('d-none');
                        document.querySelector('#container-btn-save').classList.remove('d-none');
                    }
                });
                canvasContainer.querySelector('.btn-download').addEventListener('click', function(e) {
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
};

var pageRenderAll = function() {
    for(pageIndex in pages) {
        pageRender(pageIndex);
    }
}

var pageRender = async function(pageIndex) {
  let page = pages[pageIndex];
  let rotation = parseInt(document.querySelector('#input_rotate_'+pageIndex).value);
  let viewport = page.getViewport({scale: 1, rotation: rotation});
  let size =  Math.floor((document.getElementById('container-pages').offsetWidth - (8*(nbPagePerLine+1)) - 12) / nbPagePerLine);
  let scaleWidth = size / viewport.width;
  let scaleHeight = size / viewport.height;
  let viewportWidth = page.getViewport({scale: scaleWidth, rotation: rotation});
  let viewportHeight = page.getViewport({scale: scaleHeight, rotation: rotation});

  if(viewportWidth.height > size) {
      viewport = viewportHeight;
  } else {
      viewport = viewportWidth;
  }

  let canvasContainer = document.getElementById('canvas-container-' + pageIndex);
  canvasContainer.style.height = (size + 4) + "px";
  canvasContainer.style.width = (size + 4) + "px";
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

var stateCheckbox = function(checkbox) {
    let checkboxContainer = checkbox.parentNode.parentNode.parentNode;

    if(checkbox.checked) {
        checkboxContainer.querySelector('.canvas-pdf').style.opacity = '1'
    } else {
        checkboxContainer.querySelector('.canvas-pdf').style.opacity = '0.15';
    }
};

var updateListePDF = function() {
    document.querySelector('#list_pdf').innerHTML = "";
    for (var i = 0; i < document.querySelector('#input_pdf').files.length; i++) {
        const pdfFile = document.querySelector('#input_pdf').files.item(i);
        document.querySelector('#list_pdf').insertAdjacentHTML('beforeend', '<li  class="list-group-item small" style="text-overflow: ellipsis; white-space: nowrap; overflow: hidden;"><i class="bi bi-files"></i> '+decodeURI(pdfFile.name)+'</li>');
    }
}

var degreesToOrientation = function(degrees) {
    if(degrees == 90) { return "east"; }
    if(degrees == 180) { return "south"; }
    if(degrees == 270) { return "west"; }

    return null;
}

var createEventsListener = function() {
    document.getElementById('save-select').addEventListener('click', function(event) {
        document.getElementById('save').click();
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
    document.getElementById('input_pdf_upload_2').addEventListener('change', async function(event) {
        if(this.files[0].size > maxSize) {

            alert("Le PDF ne doit pas dépasser " + Math.round(maxSize/1024/1024) + " Mo");
            this.value = "";
            return;
        }
        const cache = await caches.open('pdf');
        let filename = this.files[0].name;
        let response = new Response(this.files[0], { "status" : 200, "statusText" : "OK" });
        let urlPdf = '/pdf/'+filename;
        await cache.put(urlPdf, response);
        let pdfBlob = await getPDFBlobFromCache(urlPdf);
        nbPDF++;
        loadPDF(pdfBlob, filename, nbPDF);
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
    document.getElementById('btn_cancel_select').addEventListener('click', function(event) {
        document.querySelectorAll('.input-select:checked').forEach(function(input) {
            input.parentNode.querySelector('.btn-select').click();
        });
    });
    document.getElementById('btn_delete_select').addEventListener('click', function(event) {
        let pages = getPagesSelected();
        for(index in pages) {
            let checkbox = pages[index].querySelector('input[type=checkbox]');
            checkbox.checked = !checkbox.checked;
            stateCheckbox(checkbox);
        }
        document.querySelector('#btn_cancel_select').click();
    });
    document.getElementById('btn_rotate_select').addEventListener('click', function(event) {
        let pages = getPagesSelected();
        for(index in pages) {
            let inputRotate = pages[index].querySelector('.input-rotate');
            inputRotate.value = (parseInt(inputRotate.value) + 90) % 360;
            pageRender(index);
        }
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
    const cache = await caches.open('pdf');
    document.getElementById('input_pdf_upload').addEventListener('change', async function(event) {
            if(document.getElementById('input_pdf_upload').files[0].size > maxSize) {

            alert("Le PDF ne doit pas dépasser " + Math.round(maxSize/1024/1024) + " Mo");
            document.getElementById('input_pdf_upload').value = "";
            return;
        }
        let filename = document.getElementById('input_pdf_upload').files[0].name;
        let response = new Response(document.getElementById('input_pdf_upload').files[0], { "status" : 200, "statusText" : "OK" });
        let urlPdf = '/pdf/'+filename;
        await cache.put(urlPdf, response);
        history.pushState({}, '', '/organization#'+filename);
        pageOrganization(urlPdf)
    });
}

var pageOrganization = async function(url) {
    let filename = url.replace('/pdf/', '');
    document.title = filename + ' - ' + document.title;
    document.querySelector('body').classList.add('bg-light');
    document.getElementById('page-upload').classList.add('d-none');
    document.getElementById('page-organization').classList.remove('d-none');
    menu = document.getElementById('sidebarTools');
    menuOffcanvas = new bootstrap.Offcanvas(menu);
    responsiveDisplay();

    let pdfBlob = await getPDFBlobFromCache(url);
    if(!pdfBlob) {
        document.location = '/organization';
        return;
    }

    createEventsListener();
    loadPDF(pdfBlob, filename, nbPDF);
};

(function () {
    if(window.location.hash && window.location.hash.match(/^\#http/)) {
        let hashUrl = window.location.hash.replace(/^\#/, '');
        pageUpload();
        uploadFromUrl(hashUrl);
    } else if(window.location.hash) {
        pageOrganization('/pdf/'+window.location.hash.replace(/^\#/, ''));
    } else {
        pageUpload();
    }
    window.addEventListener('hashchange', function() {
        window.location.reload();
    })
})();