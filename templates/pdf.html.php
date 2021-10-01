<!doctype html>
<html lang="fr_FR">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="/vendor/bootstrap.min.css?5.1.1" rel="stylesheet">
    <link href="/vendor/bootstrap-icons.css?1.5.0" rel="stylesheet">
    <style>
    @font-face {
      font-family: 'Caveat';
      font-style: normal;
      font-weight: 400;
      src: url(/vendor/fonts/Caveat-Regular.ttf) format('truetype');
    }
    </style>
    <title>Signature PDF</title>
  </head>
  <body>
    <div class="container-fluid">
        <div class="row">
            <div id="container-pages" class="col-lg-10 col-md-9 col-sm-8 col-xs-6 bg-light text-center"></div>
            <aside class="col-lg-2 col-md-3 col-sm-4 col-xs-6 mt-2 position-fixed end-0 bg-white">
                <h5 class="mb-0">Motifs</h5>
                <small class="text-muted">Signature, paraphe, tampon, etc.</small>
                <div id="svg_list" class="d-grid gap-2 mt-2"></div>
                <div class="d-grid gap-2 mt-2">
                    <button type="button" id="btn-add-svg" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddSvg"><i class="bi bi-plus-circle"></i> Ajouter un motif</button>
                </div>
                <h5 class="mt-3">Texte</h5>
                <div class="d-grid gap-2 mt-3">
                    <input type="radio" class="btn-check" id="radio_svg_text" name="svg_2_add" autocomplete="off" value="text">
                    <label class="btn btn-outline-secondary text-black text-start" for="radio_svg_text"><i class="bi bi-textarea-t"></i> Zone de texte</label>
                </div>

                <form class="position-fixed bottom-0 pb-2 pe-2" id="form_pdf" action="/<?php echo $key ?>/save" method="post">
                    <p class="fs-6"><small class="text-muted"><i class="bi bi-hand-index"></i> + <i class="bi bi-hand-index"></i> = <i class="bi bi-plus-circle-fill"></i><br />Double-cliquez sur le PDF pour ajouter le motif ou l'élément sélectionné</small></p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success " disabled="disabled" type="submit" id="save"><i class="bi bi-download"></i> Télécharger le PDF Signé</button>
                    </div>
                </form>
            </aside>
        </div>
    </div>

    <div class="modal fade" id="modalAddSvg" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link active" id="nav-draw-tab" data-bs-toggle="tab" data-bs-target="#nav-draw" type="button" role="tab" aria-controls="nav-draw" aria-selected="true"><i class="bi bi-vector-pen"></i> Dessiner</button>
                    <button class="nav-link" id="nav-type-tab" data-bs-toggle="tab" data-bs-target="#nav-type" type="button" role="tab" aria-controls="nav-type" aria-selected="false"><i class="bi bi-fonts"></i> Saisir</button>
                    <button class="nav-link" id="nav-import-tab" data-bs-toggle="tab" data-bs-target="#nav-import" type="button" role="tab" aria-controls="nav-import" aria-selected="false"><i class="bi bi-image"></i> Importer</button>
                    </div>
                </nav>
                <div class="tab-content mt-3" id="nav-svg-add">
                    <div class="tab-pane fade show active" id="nav-draw" role="tabpanel" aria-labelledby="nav-draw-tab">
                      <canvas id="signature-pad" class="border bg-light" width="462" height="200"></canvas>
                    </div>
                    <div class="tab-pane fade" id="nav-type" role="tabpanel" aria-labelledby="nav-type-tab">
                        <input id="input-text-signature" type="text" class="form-control form-control-lg" placeholder="Ma signature" style="font-family: Caveat; font-size: 48px;" />
                    </div>
                    <div class="tab-pane fade" id="nav-import" role="tabpanel" aria-labelledby="nav-import-tab">
                        <div class="text-center">
                        <img id="img-upload" class="d-none" style="max-width: 460px; max-height: 200px;" src="" />
                        </div>
                        <form id="form-image-upload" action="/image2svg" method="POST" enctype="multipart/form-data">
                        <input id="input-image-upload" class="form-control" name="image" type="file">
                        </form>
                </div>
                </div>
            </div>
          <div class="modal-footer">
            <button tabindex="-1" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button id="btn_modal_ajouter" type="button" disabled="disabled" class="btn btn-primary" data-bs-dismiss="modal">Ajouter</button>
          </div>
        </div>
      </div>
    </div>
    
    <script src="/vendor/bootstrap.min.js?5.1.1"></script>
    <script src="/vendor/pdf.js?legacy"></script>
    <script src="/vendor/fabric.min.js?4.4.0"></script>
    <script src="/vendor/signature_pad.umd.min.js?3.0.0-beta.3"></script>
    <script src="/vendor/opentype.min.js?1.3.3"></script>
    <script>
    var url = '/<?php echo $key ?>/pdf';
    </script>
    <script src="/js/app.js"></script>
  </body>
</html>
