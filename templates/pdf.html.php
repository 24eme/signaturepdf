<!doctype html>
<html lang="fr_FR">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat&display=swap" rel="stylesheet">
    <title>Signature PDF</title>
  </head>
  <body>
    <div class="container-fluid">
        <div class="row">
            <div id="container-pages" class="col-lg-10 col-md-9 col-sm-8 col-xs-6 bg-light text-center"></div>
            <aside class="col-lg-2 col-md-3 col-sm-4 col-xs-6 mt-2 position-fixed end-0 bg-white">
                <div class="form-check form-switch float-end"><input class="form-check-input" type="radio" name="radio_signature" id="radio_signature_pad"></div>
                <h5><i class="bi bi-vector-pen"></i> À main levée</h5>
                <canvas id="signature-pad" class="border bg-light" width=235 height=125></canvas>
                <hr />
                <div class="form-check form-switch float-end"><input class="form-check-input" type="radio" name="radio_signature" id="radio_signature_text"></div>
                <h5><i class="bi bi-fonts"></i> Texte à la main</h5>
                <input id="input-text-signature" type="text" class="form-control" placeholder="Ma signature" style="font-family: 'Caveat', cursive; font-size: 24px;" />
                <hr />
                <div class="form-check form-switch float-end"><input class="form-check-input" type="radio" name="radio_signature" id="radio_signature_image"></div>
                <h5><i class="bi bi-image"></i> Image</h5> 
                <div class="text-center">
                <img id="img-upload" class="d-none" style="max-height: 80px; max-width: 235px;" src="" />
                </div>
                <form id="form-image-upload" action="/image2svg" method="POST" enctype="multipart/form-data">
                    <input id="input-image-upload" class="form-control" name="image" type="file">
                </form>
                <hr />
                <div class="form-check form-switch float-end"><input class="form-check-input" type="radio" name="radio_signature" id="radio_signature_text_classic"></div>
                <h5><i class="bi bi-type"></i> Texte classique</h5>
                <input id="input-signature-text-classic" type="text" class="form-control" placeholder="" />
                <hr />
                <p><small class="text-muted"><i class="bi bi-hand-index"></i><i class="bi bi-hand-index"></i> <i class="bi bi-plus-circle-fill"></i> Double-cliquez sur le PDF pour ajouter l'élément sélectionné</small></p>
                <form id="form_pdf" action="/<?php echo $key ?>/save" method="post">
                    <div class="position-fixed bottom-0 mb-2">
                        <button class="btn btn-primary" type="submit" id="save"><i class="bi bi-download"></i> Télécharger le PDF Signé</button>
                    </div>
                </form>
            </aside>
        </div>
    </div>

    <script src="/vendor/pdf.js?legacy"></script>
    <script src="/vendor/fabric.min.js?4.4.0"></script>
    <script src="/vendor/signature_pad.umd.min.js?v3.0.0-beta.3"></script>
    <script src="/vendor/opentype.min.js?v1.3.3"></script>
    <script>
    var url = '/<?php echo $key ?>/pdf';
    </script>
    <script src="/js/app.js"></script>
  </body>
</html>
