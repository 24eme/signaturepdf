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

    .offcanvas .list-item-add label:hover, .offcanvas .list-item-add label:active, .offcanvas .list-item-add label.active, .offcanvas .list-item-add .btn-check:active + .btn-outline-secondary, .offcanvas .list-item-add .btn-check:checked + .btn-outline-secondary {
        background: #c9d1d8;
        border: 1px solid #000;
        box-shadow: 0 .25rem .5rem rgba(0,0,0,.075) !important
    }
    </style>
    <title>Signature PDF</title>
  </head>
  <body class="bg-light">
    <div class="container-fluid">
        <div style="height: 65px;" class="d-md-none"></div>
        <div class="row">
            <div id="container-pages" class="col-xxl-9 col-xl-8 col-lg-7 col-md-6 col-sm-12 col-xs-12 text-center">
            </div>
        </div>
        <div style="height: 55px;" class="d-md-none"></div>
        <div class="offcanvas offcanvas-end show d-none d-md-block" data-bs-backdrop="false" data-bs-scroll="true" data-keyboard="false" tabindex="-1" id="offcanvasTop" aria-labelledby="offcanvasTopLabel">
            <div class="offcanvas-header">
                <h5 id="offcanvasTopLabel">Signature du PDF</h5>
                <button type="button" class="btn-close text-reset d-md-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
              <div id="svg_list_signature" class="list-item-add"></div>
              <div class="d-grid gap-2 mb-2 list-item-add">
                  <input type="radio" class="btn-check" id="radio_svg_signature_add" name="svg_2_add" autocomplete="off" value="signature">
                  <label data-bs-toggle="modal" data-bs-target="#modalAddSvg" data-type="signature" class="btn btn-outline-secondary text-black text-start btn-add-svg-type" for="radio_svg_signature_add"><i class="bi bi-vector-pen"></i> Signature <small class="text-muted float-end">Ajouter</small></label>
              </div>
              <div id="svg_list_initials" class="list-item-add"></div>
              <div class="d-grid gap-2 mb-2 list-item-add">
                  <input type="radio" class="btn-check" id="radio_svg_initials_add" name="svg_2_add" autocomplete="off" value="intials">
                  <label data-bs-toggle="modal" data-bs-target="#modalAddSvg" data-type="initials" data-modalnav="#nav-type-tab" class="btn btn-outline-secondary text-black text-start btn-add-svg-type" for="radio_svg_initials_add"><i class="bi bi-type"></i> Paraphe <small class="text-muted float-end">Ajouter</small></label>
              </div>
              <div id="svg_list_rubber_stamber" class="list-item-add"></div>
              <div class="d-grid gap-2 mb-2 list-item-add">
                  <input type="radio" class="btn-check" id="radio_svg_rubber_stamber_add" name="svg_2_add" autocomplete="off" value="rubber_stamber">
                  <label data-bs-toggle="modal" data-bs-target="#modalAddSvg" data-type="rubber_stamber" data-modalnav="#nav-import-tab" class="btn btn-outline-secondary text-black text-start btn-add-svg-type" for="radio_svg_rubber_stamber_add"><i class="bi bi-card-text"></i> Tampon <small class="text-muted float-end">Ajouter</small></label>
              </div>
              <div class="d-grid gap-2 mb-2 list-item-add">
                  <input type="radio" class="btn-check" id="radio_svg_text" name="svg_2_add" autocomplete="off" value="text">
                  <label draggable="true" style="cursor: grab;" class="btn btn-outline-secondary text-black text-start btn-svg" for="radio_svg_text"><i class="bi bi-textarea-t"></i> Texte</label>
              </div>
              <div id="svg_list" class="d-grid gap-2 mt-2 mb-2 list-item-add"></div>

              <div class="d-grid gap-2 mt-2">
                  <button type="button" id="btn-add-svg" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#modalAddSvg"><i class="bi bi-plus-circle"></i> Ajouter un élément</button>
              </div>

              <form class="position-absolute bottom-0 pb-2 ps-0 pe-4 w-100 d-none d-sm-none d-md-block" id="form_pdf" action="/<?php echo $key ?>/save" method="post">
                    <div class="d-grid gap-2 mt-2">
                        <button class="btn btn-primary" disabled="disabled" type="submit" id="save"><i    class="bi bi-download"></i> Télécharger le PDF Signé</button>
                    </div>
              </form>
            </div>
        </div>
        <div class="position-fixed top-0 start-0 bg-white w-100 p-2 shadow-sm d-md-none">
            <div class="d-grid gap-2">
            <button id="btn_svn_select" class="btn btn-light btn-lg" data-bs-toggle="offcanvas" data-bs-target="#offcanvasTop" aria-controls="offcanvasTop"><i class="bi bi-hand-index"></i> Séléctionner une signature</button>
            </div>
            <div id="svg_selected_container" class="d-grid gap-2 d-none">
                <div class="btn-group">
                    <button class="btn btn-outline-secondary"><img id="svg_selected" src="" style="max-height: 40px;" class="img-fluid"/></button>
                    <button class="btn btn-link" data-bs-toggle="offcanvas" data-bs-target="#offcanvasTop" aria-controls="offcanvasTop"> Changer</button>
                </div>
            </div>
        </div>
        <div class="position-fixed bottom-0 start-0 bg-white w-100 p-2 shadow d-md-none">
            <div class="d-grid gap-2">
                <button class="btn btn-primary" disabled="disabled" type="submit" id="save_mobile"><i class="bi bi-download"></i> Télécharger le PDF Signé</button>
            </div>
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
                <input id="input-svg-type" type="hidden" />
          </div>
          <div class="modal-footer">
            <button tabindex="-1" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button id="btn_modal_ajouter" type="button" disabled="disabled" class="btn btn-primary" data-bs-dismiss="modal">Ajouter</button>
          </div>
        </div>
      </div>
    </div>
    <span id="is_mobile" class="d-md-none"></span>

    <script src="/vendor/bootstrap.min.js?5.1.1"></script>
    <script src="/vendor/pdf.js?legacy"></script>
    <script src="/vendor/fabric.min.js?4.6.0"></script>
    <script src="/vendor/signature_pad.umd.min.js?3.0.0-beta.3"></script>
    <script src="/vendor/opentype.min.js?1.3.3"></script>
    <script>
    var url = '/<?php echo $key ?>/pdf';
    </script>
    <script src="/js/app.js"></script>
  </body>
</html>
