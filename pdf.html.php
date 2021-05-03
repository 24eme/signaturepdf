<!doctype html>
<html lang="fr_FR">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
    <title>Signature PDF</title>
  </head>
  <body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-10 col-md-9 col-sm-8 col-xs-6 bg-light">
                <div class="position-relative mt-2 mb-2">
                    <canvas id="canvas-pdf" class="position-absolute top-0 start-0 shadow"></canvas>
                    <canvas id="canvas-edition" class="position-absolute top-0 start-0"></canvas>
                </div>
            </div>
            <aside class="col-lg-2 col-md-3 col-sm-4 col-xs-6 mt-2 position-fixed end-0 bg-white">
                <h4><i class="bi bi-vector-pen"></i> Signature</h4> 
                <canvas id="signature-pad" class="border bg-light" width=200 height=150></canvas>
                <p><small class="text-muted">Double-cliquez sur le PDF pour ajouter la signature</small></p>
                <form action="/<?php echo $key ?>/save" method="post">
                    <input name="svg" id="data-svg" type="hidden" value="" />
                    <div class="position-fixed bottom-0 mb-2">
                        <button class="btn btn-primary" type="submit" id="save"><i class="bi bi-download"></i> Télécharger le PDF</button>
                    </div>
                </form>
            </aside>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
    <script src="https://mozilla.github.io/pdf.js/build/pdf.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fabric@4.4.0/dist/fabric.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@3.0.0-beta.3/dist/signature_pad.umd.min.js"></script>
    <script>
    var url = '/data/<?php echo $key ?>.pdf';
    </script>
    <script src="/js/app.js"></script>
  </body>
</html>
