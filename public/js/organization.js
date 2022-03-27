var pdfRenderTasks = [];
var pdfPages = [];
var resizeTimeout;
var currentScale = 1.5;
var windowWidth = window.innerWidth;

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
                var scale = 0.5;
                var viewport = page.getViewport({scale: scale});
                if(viewport.width > document.getElementById('container-pages').clientWidth - 40) {
                  viewport = page.getViewport({scale: 1});
                  scale = (document.getElementById('container-pages').clientWidth - 40) / viewport.width;
                  viewport = page.getViewport({ scale: scale });
                }

                currentScale = scale;

                var pageIndex = page.pageNumber - 1;

                document.getElementById('container-pages').insertAdjacentHTML('beforeend', '<div class="position-relative mt-1 ms-1 me-1 d-inline-block" id="canvas-container-' + pageIndex +'"><canvas id="canvas-pdf-'+pageIndex+'" class="shadow-sm canvas-pdf"></canvas><div class="position-absolute text-center" style="bottom: 7px; width: 100%; background: rgba(0,0,0,0.2);"><input form="form_pdf" class="form-check-input checkbox-page" type="checkbox" checked="checked" value="'+page.pageNumber+'" name="pages[]" /></div></div>');

                let canvasContainer = document.getElementById('canvas-container-' + pageIndex);
                let canvasCheckbox = canvasContainer.querySelector('input[type=checkbox]');
                canvasCheckbox.addEventListener('click', function(e) {
                    e.stopPropagation();
                })
                canvasContainer.addEventListener('click', function(e) {
                    this.querySelector('input[type=checkbox]').checked = !this.querySelector('input[type=checkbox]').checked;
                    document.querySelector('#checkbox_all_pages').checked = (document.querySelectorAll('.checkbox-page:checked').length == document.querySelectorAll('.checkbox-page').length);
                })
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

var createEventsListener = function() {
    document.querySelector('#checkbox_all_pages').addEventListener('change', function() {
        let checkboxAll = this;
        document.querySelectorAll('.checkbox-page').forEach(function(checkbox) {
            checkbox.checked = checkboxAll.checked;
        });
    })
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

            alert("Le PDF ne doit pas dépasser <?php echo round($maxSize / 1024 / 1024) ?> Mo");
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