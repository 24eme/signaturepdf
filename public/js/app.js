
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

    opentype.load('/vendor/fonts/Caveat-Regular.ttf', function(err, font) {
        fontCaveat = font;
    });
    
    
    var displaysSVG = function() {
        document.getElementById('svg_list').innerHTML = "";
        svgCollections.forEach((svg, i) => {
            var inputRadio = document.createElement('input');
            inputRadio.type = "radio";
            inputRadio.classList.add("btn-check");
            inputRadio.id="radio_svg_"+i;
            inputRadio.name = "svg_2_add";
            inputRadio.autocomplete = "off";
            inputRadio.value = svg;
            var svgButton = document.createElement('label');
            svgButton.classList.add('btn');
            svgButton.classList.add('btn-lg');
            svgButton.classList.add('btn-outline-secondary');
            svgButton.htmlFor = "radio_svg_"+i;
            var svgImg = document.createElement('img');
            svgImg.src = svg;
            svgImg.style = "max-width: 180px;max-height: 70px;";
            svgButton.appendChild(svgImg);
            document.getElementById('svg_list').appendChild(inputRadio);
            document.getElementById('svg_list').appendChild(svgButton);
        });
    }
    
    displaysSVG();
    
    document.getElementById('btn_modal_ajouter').addEventListener('click', function() {
        if(document.getElementById('nav-draw-tab').classList.contains('active')) {
            svgCollections.push(signaturePad.toDataURL("image/svg+xml"));
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
            svgCollections.push("data:image/svg+xml;base64,"+btoa(textCanvas.toSVG()));
        }
        if(document.getElementById('nav-import-tab').classList.contains('active')) {
            svgCollections.push(document.getElementById('img-upload').src);
        }
        displaysSVG();
        
        document.querySelector('#svg_list label:last-child').click();
    });
    
    var signaturePad = new SignaturePad(document.getElementById('signature-pad'), {
        penColor: 'rgb(0, 0, 0)',
        onEnd: function() { document.getElementById('btn_modal_ajouter').focus() }
    });
    
    document.querySelectorAll('#modalAddSvg .nav-link').forEach(function(item) { item.addEventListener('shown.bs.tab', function (event) {
        var firstInput = document.querySelector(event.target.dataset.bsTarget).querySelector('input');
        if(firstInput) {
            firstInput.focus();
        }
    })});

    document.getElementById('modalAddSvg').addEventListener('shown.bs.modal', function (event) {
        document.querySelector('#modalAddSvg #nav-tab button:first-child').focus()
    })

    document.getElementById('modalAddSvg').addEventListener('hidden.bs.modal', function (event) {
        signaturePad.clear();
        document.getElementById('input-text-signature').value = null;
        document.getElementById('input-image-upload').value = null;
        document.getElementById('img-upload').src = null;
        document.getElementById('img-upload').classList.add("d-none");
        bootstrap.Tab.getOrCreateInstance(document.querySelector('#modalAddSvg #nav-tab button:first-child')).show();
    })
    
    document.getElementById('input-text-signature').addEventListener('keypress', function(event) {
        if(event.key == 'Enter') {
            document.getElementById('btn_modal_ajouter').click()
        }
    })

    document.getElementById('input-image-upload').addEventListener('change', function(event) {
        var data = new FormData();    
        data.append('file', document.getElementById('input-image-upload').files[0]);
        
        xhr = new XMLHttpRequest();

        xhr.open( 'POST', document.getElementById('form-image-upload').action, true );
        xhr.onreadystatechange = function () { 
            var svgImage = "data:image/svg+xml;base64,"+btoa(this.responseText);
            document.getElementById('img-upload').src = svgImage;
            document.getElementById('img-upload').classList.remove("d-none");
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

          var canvasEdition = new fabric.Canvas('canvas-edition-' + pageIndex);
          
          canvasEdition.on('mouse:move', function(event) {
              activeCanvas = this;
              activeCanvasPointer = event.pointer;
          });

          canvasEdition.on('mouse:dblclick', function(event) {
              x = event.pointer.x
              y = event.pointer.y
              if(document.querySelector('input[name="svg_2_add"]:checked')) {
                  fabric.loadSVGFromURL(document.querySelector('input[name="svg_2_add"]:checked').value, function(objects, options) {
                      var svg = fabric.util.groupSVGElements(objects, options);
                      svg.scaleToHeight(100);
                      svg.top = y - (svg.getScaledHeight() / 2);
                      svg.left = x - (svg.getScaledWidth() / 2);
                      canvasEdition.add(svg).renderAll();
                  });
              }
              
              /*if(document.getElementById('radio_signature_text_classic').checked) {
                var textSignature = new fabric.Text(document.getElementById('input-signature-text-classic').value, { fontSize: 16 });
                textSignature.top = y - (textSignature.getScaledHeight() / 2);    
                textSignature.left = x - (textSignature.getScaledWidth() / 2);    
                canvasEdition.add(textSignature).renderAll();
            }*/
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