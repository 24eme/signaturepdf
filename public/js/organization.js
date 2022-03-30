var pdfRenderTasks = [];
var pdfPages = [];
var windowWidth = window.innerWidth;
var nbPagePerLine = 4;

var loadPDF = async function(pdfBlob, filename) {
    var pdfjsLib = window['pdfjs-dist/build/pdf'];
    pdfjsLib.GlobalWorkerOptions.workerSrc = '/vendor/pdf.worker.js?legacy';
    let url = await URL.createObjectURL(pdfBlob);

    let dataTransfer = new DataTransfer();
    dataTransfer.items.add(new File([pdfBlob], filename, {
        type: 'application/pdf'
    }));
    document.getElementById('input_pdf').files = dataTransfer.files;

    var loadingTask = pdfjsLib.getDocument(url);
    loadingTask.promise.then(function(pdf) {
        for(var pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++ ) {
            pdf.getPage(pageNumber).then(function(page) {
                let viewport = page.getViewport({scale: 1});
                let scale = (document.getElementById('container-pages').clientWidth - (8*nbPagePerLine) - 10) / viewport.width / nbPagePerLine;
                viewport = page.getViewport({scale: scale});

                var pageIndex = page.pageNumber - 1;

                document.getElementById('container-pages').insertAdjacentHTML('beforeend', '<div class="position-relative mt-1 ms-1 me-1 d-inline-block canvas-container" id="canvas-container-' + pageIndex +'" draggable="true"><canvas id="canvas-pdf-'+pageIndex+'" class="shadow-sm canvas-pdf" style="box-sizing: border-box;"></canvas><div class="position-absolute top-50 start-50 translate-middle p-2 ps-3 pe-3 rounded-circle container-resize btn-drag"><i class="bi bi-arrows-move"></i></div><div class="position-absolute text-center w-100 pt-2 pb-0 container-checkbox" style="background: rgb(255,255,255,0.8); bottom: 7px; cursor: pointer;"><div class="form-switch"><input form="form_pdf" class="form-check-input checkbox-page" role="switch" type="checkbox" checked="checked" style="cursor: pointer;" value="'+page.pageNumber+'"" /></div><p class="mt-2 mb-0" style="font-size: 10px;">Page '+page.pageNumber+' - '+filename+'</p></div></div>');

                let canvasContainer = document.getElementById('canvas-container-' + pageIndex);
                canvasContainer.addEventListener('dragstart', function(e) {
                    this.querySelector('.canvas-pdf').classList.add('shadow-lg');
                    e.dataTransfer.setData('element', this.id);
                });
                canvasContainer.addEventListener('dragend', function(e) {
                    this.querySelector('.canvas-pdf').classList.remove('shadow-lg');
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
                canvasContainer.addEventListener('click', function(e) {
                    let checkbox = this.querySelector('input[type=checkbox]');
                    checkbox.checked = !checkbox.checked;
                    stateCheckbox(checkbox);
                    stateCheckboxAll();
                });

                var canvasPDF = document.getElementById('canvas-pdf-' + pageIndex);

                // Prepare canvas using PDF page dimensions
                var context = canvasPDF.getContext('2d');
                canvasPDF.height = viewport.height;
                canvasPDF.width = viewport.width;

                var renderContext = {
                canvasContext: context,
                viewport: viewport,
                enhanceTextSelection: true
                };
                var renderTask = page.render(renderContext);
                pdfRenderTasks.push(renderTask);
                pdfPages.push(page);
            });
        }
    }, function (reason) {
        console.error(reason);
    });
};

var is_mobile = function() {
    return !(window.getComputedStyle(document.getElementById('is_mobile')).display === "none");
};

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
    loadPDF(pdfBlob, filename);
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