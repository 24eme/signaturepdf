var windowWidth = window.innerWidth;
var is_mobile = function() {
    return !(window.getComputedStyle(document.getElementById('is_mobile')).display === "none");
};
var nbPagePerLine = 5;
if(is_mobile()) {
    nbPagePerLine = 2;
}
var pdfjsLib = window['pdfjs-dist/build/pdf'];
pdfjsLib.GlobalWorkerOptions.workerSrc = '/vendor/pdf.worker.js?legacy';
var nbPDF = 0;
var pages = [];

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

    let pdfLetter = String.fromCharCode(96 + i+1).toUpperCase();

    let loadingTask = pdfjsLib.getDocument(url);
    loadingTask.promise.then(function(pdf) {
        for(var pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++ ) {
            pdf.getPage(pageNumber).then(function(page) {
                let pageIndex = pdfLetter + "_" + (page.pageNumber - 1);
                pages[pageIndex] = page;

                document.getElementById('container-pages').insertAdjacentHTML('beforeend', '<div class="position-relative mt-0 ms-1 me-1 mb-2 canvas-container shadow-sm d-flex align-items-center justify-content-center bg-white" id="canvas-container-' + pageIndex +'" draggable="true"><canvas class="canvas-pdf" style="border: 2px solid transparent;"></canvas><div class="position-absolute top-50 start-50 translate-middle p-2 ps-3 pe-3 rounded-circle container-resize btn-drag"><i class="bi bi-arrows-move"></i></div><div class="position-absolute top-50 end-0 translate-middle-y p-2 ps-3 pe-3 rounded-circle container-rotate btn-rotate"><i class="bi bi-arrow-clockwise"></i></div><div class="position-absolute text-center w-100 pt-1 container-checkbox pb-4" style="background: rgb(255,255,255,0.8); bottom: 0; cursor: pointer;"><div class="form-switch"><input form="form_pdf" class="form-check-input checkbox-page" role="switch" type="checkbox" checked="checked" style="cursor: pointer;" value="'+pdfLetter+page.pageNumber+'" /></div></div><p class="position-absolute text-center w-100 ps-2 pe-2 pb-0 mb-1 opacity-75" style="bottom: 0; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">Page '+page.pageNumber+' - '+filename+'</p><input type="hidden" value="0" id="input_rotate_'+pageIndex+'" /></div>');

                let canvasContainer = document.getElementById('canvas-container-' + pageIndex);
                canvasContainer.addEventListener('dragstart', function(e) {
                    this.querySelector('.container-checkbox').classList.add('d-none');
                    this.querySelector('.container-resize').classList.add('d-none');
                    this.querySelector('.canvas-pdf').classList.add('shadow-lg');
                    this.querySelector('.canvas-pdf').style.border = '2px dashed #777';
                    e.dataTransfer.setData('element', this.id);
                    this.style.opacity = 0.4;
                    document.querySelector('#container-bar').classList.add('d-none');
                });
                canvasContainer.addEventListener('dragend', function(e) {
                    this.querySelector('.container-checkbox').classList.remove('d-none');
                    this.querySelector('.container-resize').classList.remove('d-none');
                    this.querySelector('.canvas-pdf').classList.remove('shadow-lg');
                    this.querySelector('.canvas-pdf').style.border = '2px solid transparent';
                    this.style.opacity = 1;
                    document.querySelector('#container-bar').classList.remove('d-none');
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

                canvasContainer.querySelector('input[type=checkbox]').addEventListener('click', function(e) {
                    e.stopPropagation();
                });
                canvasContainer.querySelector('input[type=checkbox]').addEventListener('change', function(e) {
                    stateCheckbox(this);
                    stateCheckboxAll();
                });
                canvasContainer.querySelector('.container-checkbox').addEventListener('click', function(e) {
                    let checkbox = this.querySelector('input[type=checkbox]');
                    checkbox.checked = !checkbox.checked;
                    stateCheckbox(checkbox);
                    stateCheckboxAll();
                });
                canvasContainer.querySelector('.btn-rotate').addEventListener('click', function(e) {
                    let inputRotate = document.querySelector('#input_rotate_'+pageIndex);
                    inputRotate.value = parseInt(inputRotate.value) + 90;
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

var pageRender = function(pageIndex) {
  let page = pages[pageIndex];
  let rotation = parseInt(document.querySelector('#input_rotate_'+pageIndex).value);
  let viewport = page.getViewport({scale: 1, rotation: rotation});
  let size =  (document.getElementById('container-pages').offsetWidth - (12*nbPagePerLine) - 12) / nbPagePerLine;
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

  page.render({
  canvasContext: context,
  viewport: viewport,
  });
}

var stateCheckbox = function(checkbox) {
    let checkboxContainer = checkbox.parentNode.parentNode.parentNode;

    if(checkbox.checked) {
        checkboxContainer.querySelector('.canvas-pdf').style.opacity = '1';
        checkboxContainer.querySelector('.canvas-pdf').style.cursor = 'inherit';
        checkboxContainer.querySelector('.container-resize').classList.remove('d-none');
        checkboxContainer.querySelector('.container-checkbox').style.background = 'rgb(255,255,255,0.8)';
    } else {
        checkboxContainer.querySelector('.canvas-pdf').style.opacity = '0.3';
        checkboxContainer.querySelector('.canvas-pdf').style.cursor = 'pointer';
        checkboxContainer.querySelector('.container-resize').classList.add('d-none');
        checkboxContainer.querySelector('.container-checkbox').style.background = 'transparent';
    }
};

var stateCheckboxAll = function() {
    document.querySelector('#checkbox_all_pages').checked = (document.querySelectorAll('.checkbox-page:checked').length == document.querySelectorAll('.checkbox-page').length);
};

var createEventsListener = function() {
    document.querySelector('#checkbox_all_pages').addEventListener('change', function() {
        let checkboxAll = this;
        document.querySelectorAll('.checkbox-page').forEach(function(checkbox) {
            checkbox.checked = checkboxAll.checked;
            stateCheckbox(checkbox);
        });
    });
    document.getElementById('save').addEventListener('click', function(event) {
        let order = [];
        document.querySelectorAll('.checkbox-page').forEach(function(checkbox) {
            if(checkbox.checked) {
                order.push(checkbox.value);
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
    document.getElementById('page-upload').classList.add('d-none');
    document.getElementById('page-organization').classList.remove('d-none');

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