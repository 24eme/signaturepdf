
// Loaded via <script> tag, create shortcut to access PDF.js exports.
var pdfjsLib = window['pdfjs-dist/build/pdf'];

// The workerSrc property shall be specified.
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://mozilla.github.io/pdf.js/build/pdf.worker.js';

// Asynchronous download of PDF
var loadingTask = pdfjsLib.getDocument(url);
loadingTask.promise.then(function(pdf) {

    var signaturePad = new SignaturePad(document.getElementById('signature-pad'), {
        backgroundColor: 'rgba(255, 255, 255, 0)',
        penColor: 'rgb(0, 0, 0)',
        minWidth: 0.75,
        maxWidth: 1.1
    });
    
    var canvasEditions = [];
    
    document.getElementById('save').addEventListener('click', function(event) {
        canvasEditions.forEach(function(canvasEdition, index) {
            document.getElementById('data-svg-'+index).value = canvasEdition.toSVG();
        }) 
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

          var canvasEdition = new fabric.Canvas('canvas-edition-' + pageIndex);

          canvasEdition.on('mouse:dblclick', function(event) {
              x = event.pointer.x
              y = event.pointer.y

              fabric.loadSVGFromURL(signaturePad.toDataURL("image/svg+xml"), function(objects, options) {
                  options.left = x - 100;
                  options.top = y - 75;
                  var obj = fabric.util.groupSVGElements(objects, options);
                  console.log(obj);
                  canvasEdition.add(obj).renderAll();
              });

          });

          canvasEditions.push(canvasEdition);

          var renderContext = {
            canvasContext: context,
            viewport: viewport
          };
          var renderTask = page.render(renderContext);
          renderTask.promise.then(function () {
            console.log('Page rendered');
          });
        });
    }
    }, function (reason) {
    console.error(reason);
});