<!doctype html>
<html lang="fr_FR">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="/vendor/bootstrap.min.css?5.1.1" rel="stylesheet">
    <link href="/vendor/bootstrap-icons.css?1.5.0" rel="stylesheet">
    <link href="/css/app.css?202210080134" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/favicon-organization.ico">

    <title>Métadonnées d'un PDF</title>
  </head>
  <body>
    <noscript>
        <div class="alert alert-danger text-center" role="alert">
          <i class="bi bi-exclamation-triangle"></i> Site non fonctionnel sans JavaScript activé
        </div>
    </noscript>
    <div id="page-upload">
        <ul class="nav justify-content-center nav-tabs mt-2">
          <li class="nav-item">
            <a class="nav-link" href="/signature"><i class="bi bi-vector-pen"></i> Signer</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/organization"><i class="bi bi-ui-checks-grid"></i> Organiser</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="/metadata"><i class="bi bi-ui-checks-grid"></i> Metadonnées</a>
          </li>
        </ul>
        <div class="px-4 py-4 text-center">
            <h1 class="display-5 fw-bold mb-0 mt-3"><i class="bi bi-ui-checks-grid"></i> Metadonnées d'un PDF</h1>
            <p class="fw-light mb-3 subtitle text-dark text-nowrap" style="overflow: hidden; text-overflow: ellipsis;"></p>
            <div class="col-md-6 col-lg-5 col-xl-4 col-xxl-3 mx-auto">
                <div class="col-12">
                  <label class="form-label mt-3" for="input_pdf_upload">Choisir un PDF <small class="opacity-75" style="cursor: help" title="Le PDF ne doit pas dépasser <?php echo round($maxSize / 1024 / 1024) ?> Mo"><i class="bi bi-info-circle"></i></small></label>
                  <input id="input_pdf_upload" placeholder="Choisir un PDF" class="form-control form-control-lg" type="file" accept=".pdf,application/pdf" />
                  <p class="mt-2 small fw-light text-dark">Le PDF sera traité par le serveur sans être conservé ni stocké</p>
                  <?php if($PDF_DEMO_LINK): ?>
                  <a class="btn btn-sm btn-link opacity-75" href="#<?php echo $PDF_DEMO_LINK ?>">Tester avec un PDF de démo</a>
                  <?php endif; ?>
                </div>
            </div>
        </div>
        <footer class="text-center text-muted mb-2 fixed-bottom opacity-75">
            <small>Logiciel libre <span class="d-none d-md-inline">sous license AGPL-3.0 </span>: <a href="https://github.com/24eme/signaturepdf">voir le code source</a></small>
        </footer>
    </div>
    <div id="page-metadata" class="d-none">
        <div id="div-margin-top" style="height: 88px;" class="d-md-none"></div>
        <div style="width: 60%; overflow: auto;" class="vh-100" id="container-main">
                <div id="form-metadata" class="mx-auto w-75 pt-3 pb-5">
                    <h3>Liste des métadonnées du PDF</h3>
                    <div id="form-metadata-container">
                    </div>
                    <form id="form_metadata_add" class="position-relative">
                        <hr class="text-muted mt-4 mb-3" />
                        <div class="mb-3">
                            <label class="form-label text-muted" for="input_metadata_key">Ajouter une nouvelle métadonnée</label>
                            <div class="form-floating">
                              <input id="input_metadata_key" name="metadata_key" type="text" class="form-control" required value="" style="border-bottom-right-radius: 0;  border-bottom-left-radius: 0;">
                              <label>Clé</label>
                            </div>
                            <input id="input_metadata_value" readonly="readonly" style="border-top: 0; border-top-right-radius: 0;  border-top-left-radius: 0;" name="metadata_value" type="text" class="form-control bg-light opacity-50" value="" placeholder="Valeur" style="border-bottom-right-radius: 0;  border-bottom-left-radius: 0;">
                        </div>
                        <button type="submit" type="button" class="btn btn-outline-secondary float-end"><i class="bi bi-plus-circle"></i> Ajouter</button>
                    </form>
                </div>
        </div>
        <div id="div-margin-bottom" style="height: 55px;" class="d-md-none"></div>
        <div style="width: 40%;" class="offcanvas offcanvas-end show d-none d-md-block shadow-sm" data-bs-backdrop="false" data-bs-scroll="true" data-bs-keyboard="false" tabindex="-1" id="sidebarTools" aria-labelledby="sidebarToolsLabel">
            <a class="btn btn-close btn-sm position-absolute opacity-25 d-none d-sm-none d-md-block" title="Fermer ce PDF et retourner à l'accueil" style="position: absolute; top: 2px; right: 2px; font-size: 10px;" href="/metadata"></a>
            <div class="offcanvas-header d-block mb-0 pb-0 border-bottom">
                <h5 class="mb-1 d-block w-100" id="sidebarToolsLabel">Édition de métadonnées<span class="float-end me-2" title="Ce PDF est stocké sur votre ordinateur pour être signé par vous uniquement"><i class="bi-ui-checks-grid"></i></span></h5>
                <button type="button" class="btn-close text-reset d-md-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                <p id="text_document_name" class="text-muted mb-2" style="text-overflow: ellipsis; white-space: nowrap; overflow: hidden;" title=""><i class="bi bi-files"></i> <span></span></p>
            </div>
            <div class="offcanvas-body bg-light" style="padding-bottom: 60px;">
                <div id="container-pages">
                </div>
            </div>
            <div class="position-absolute bg-white bottom-0 pb-2 ps-2 pe-2 w-100 border-top shadow-lg">
                <form id="form_pdf" action="/organize" method="post" enctype="multipart/form-data">
                    <input id="input_pdf" name="pdf[]" type="file" class="d-none" />
                    <input id="input_pages" type="hidden" value="" name="pages" />
                    <div id="btn_container" class="d-grid gap-2 mt-2">
                        <button class="btn btn-primary" type="submit" id="save"><i class="bi bi-download"></i> Enregistrer et télécharger le PDF</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="bottom_bar" class="position-fixed bottom-0 start-0 bg-white w-100 p-2 shadow-sm d-md-none">
            <div id="bottom_bar_action" class="d-grid gap-2">
                <button class="btn btn-primary" type="submit" id="save_mobile"><i class="bi bi-download"></i> Télécharger le PDF</button>
            </div>
        </div>
    </div>

    <span id="is_mobile" class="d-md-none"></span>

    <script src="/vendor/bootstrap.min.js?5.1.3"></script>
    <script src="/vendor/pdf.js?legacy"></script>
    <script>
    var maxSize = <?php echo $maxSize ?>;
    </script>
    <script src="/js/metadata.js?202212070154"></script>
  </body>
</html>
