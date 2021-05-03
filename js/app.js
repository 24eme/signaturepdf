
// Loaded via <script> tag, create shortcut to access PDF.js exports.
var pdfjsLib = window['pdfjs-dist/build/pdf'];

// The workerSrc property shall be specified.
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://mozilla.github.io/pdf.js/build/pdf.worker.js';

// Asynchronous download of PDF
var loadingTask = pdfjsLib.getDocument(url);
loadingTask.promise.then(function(pdf) {
    console.log('PDF loaded');

    var canvasEdition = null;

    // Fetch the first page
    var pageNumber = 1;
    pdf.getPage(pageNumber).then(function(page) {
      console.log('Page loaded');
      
      var signaturePad = new SignaturePad(document.getElementById('signature-pad'), {
          backgroundColor: 'rgba(255, 255, 255, 0)',
          penColor: 'rgb(0, 0, 0)',
          minWidth: 0.75,
          maxWidth: 1.1
        });
      
      var scale = 1.0;
      var viewport = page.getViewport({scale: scale});

      var canvasPDF = document.getElementById('canvas-pdf');
      var canvasEditionHTML = document.getElementById('canvas-edition');
      // Prepare canvas using PDF page dimensions
      var context = canvasPDF.getContext('2d');
      canvasPDF.height = viewport.height;
      canvasPDF.width = viewport.width;
      canvasEditionHTML.height = viewport.height;
      canvasEditionHTML.width = viewport.width;
      canvasEdition = new fabric.Canvas('canvas-edition');
      
      canvasEdition.on('mouse:dblclick', function(event) {
          x = event.pointer.x
          y = event.pointer.y
          
          fabric.loadSVGFromURL(signaturePad.toDataURL("image/svg+xml"), function(objects, options) {
              options.left = x
              options.top = y
              var obj = fabric.util.groupSVGElements(objects, options);
              canvasEdition.add(obj).renderAll();
          });

      });

      document.getElementById('save').addEventListener('click', function(event) {
            document.getElementById('data-svg').value = canvasEdition.toSVG();
      });
      
      var renderContext = {
        canvasContext: context,
        viewport: viewport
      };
      var renderTask = page.render(renderContext);
      renderTask.promise.then(function () {
        console.log('Page rendered');
      });
    });
    }, function (reason) {
    console.error(reason);
});