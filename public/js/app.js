
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

    opentype.load('/vendor/fonts/Caveat-Regular.ttf', function(err, font) {
        fontCaveat = font;
    });
    
    var signaturePad = new SignaturePad(document.getElementById('signature-pad'), {
        backgroundColor: 'rgba(255, 255, 255, 0)',
        penColor: 'rgb(0, 0, 0)',
        minWidth: 0.75,
        maxWidth: 1.1,
        onEnd: function() {
            document.getElementById('radio_signature_pad').checked = true;
        }
    });
    
    document.getElementById('input-text-signature').addEventListener('keypress', function(event) {
        document.getElementById('radio_signature_text').checked = true;
    });
    
    document.getElementById('input-signature-text-classic').addEventListener('keypress', function(event) {
        document.getElementById('radio_signature_text_classic').checked = true;
    });
    
    var svgImage = null;
    
    document.getElementById('input-image-upload').addEventListener('change', function(event) {
        var data = new FormData();    
        data.append('file', document.getElementById('input-image-upload').files[0]);
        
        xhr = new XMLHttpRequest();

        xhr.open( 'POST', document.getElementById('form-image-upload').action, true );
        xhr.onreadystatechange = function () { 
            svgImage = "data:image/svg+xml;base64,"+btoa(this.responseText);
            document.getElementById('radio_signature_image').checked = true;
            document.getElementById('img-upload').src = svgImage;
            document.getElementById('img-upload').classList.remove("d-none");
        };
        xhr.send( data );

        event.preventDefault();
    });
    
    var canvasEditions = [];
    
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

          var canvasEdition = new fabric.Canvas('canvas-edition-' + pageIndex);
          
          canvasEdition.on('mouse:move', function(event) {
              activeCanvas = this;
              activeCanvasPointer = event.pointer;
          });

          canvasEdition.on('mouse:dblclick', function(event) {
              x = event.pointer.x
              y = event.pointer.y
              
              var svg2add = null;
              
              if(document.getElementById('radio_signature_pad').checked) {
                 svg2add = signaturePad.toDataURL("image/svg+xml");
              }
              
              if(document.getElementById('radio_signature_image').checked) {
                svg2add = svgImage;
              }
              
              if(svg2add) {
                  fabric.loadSVGFromURL(svg2add, function(objects, options) {
                      var svg = fabric.util.groupSVGElements(objects, options);
                      svg.scaleToHeight(100);
                      svg.top = y - (svg.getScaledHeight() / 2);
                      svg.left = x - (svg.getScaledWidth() / 2);
                      canvasEdition.add(svg).renderAll();
                  });
              }

              if(document.getElementById('radio_signature_text').checked) {
                var fontPath = fontCaveat.getPath(document.getElementById('input-text-signature').value, 0, 0, 42);
                var fabricPath = new fabric.Path(fontPath.toPathData());
                fabricPath.top = y - (fabricPath.getScaledHeight() / 2);    
                fabricPath.left = x - (fabricPath.getScaledWidth() / 2);    
                canvasEdition.add(fabricPath).renderAll();
              }
              
              if(document.getElementById('radio_signature_text_classic').checked) {
                var textSignature = new fabric.Text(document.getElementById('input-signature-text-classic').value, { fontSize: 16 });
                textSignature.top = y - (textSignature.getScaledHeight() / 2);    
                textSignature.left = x - (textSignature.getScaledWidth() / 2);    
                canvasEdition.add(textSignature).renderAll();
              }
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