<!doctype html>
<html lang="fr_FR">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="/vendor/bootstrap.min.css?5.1.1" rel="stylesheet">
    <link href="/vendor/bootstrap-icons.css?1.5.0" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/favicon-organization.ico">

    <title>Organiser un PDF</title>
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
            <a class="nav-link active" href="/organization"><i class="bi bi-ui-checks-grid"></i> Organiser</a>
          </li>
        </ul>
        <div class="px-4 py-5 text-center">
            <h1 class="display-5 fw-bold"><i class="bi bi-ui-checks-grid"></i> Organiser des PDF</h1>
            <div class="col-md-6 col-lg-5 col-xl-4 col-xxl-3 mx-auto">
                <div class="col-12">
                  <label class="form-label mt-2" for="input_pdf_upload">Choisir un PDF <small class="opacity-75" title="Le PDF ne doit pas dépasser <?php echo round($maxSize / 1024 / 1024) ?> Mo"><i class="bi bi-info-circle"></i></small></label>
                  <input id="input_pdf_upload" placeholder="Choisir un PDF" class="form-control form-control-lg" type="file" accept=".pdf,application/pdf" />
                  <p class="mt-2 small text-muted opacity-75">Le PDF sera traité par le serveur sans être conservé ni stocké</p>
                  <a class="btn btn-sm btn-link opacity-75" href="#https://raw.githubusercontent.com/24eme/signaturepdf/master/tests/files/document.pdf">Tester avec un PDF de démo</a>
                </div>
            </div>
        </div>
        <footer class="text-center text-muted mb-2 fixed-bottom">
            <small>Logiciel libre sous license AGPL-3.0 : <a href="https://github.com/24eme/signaturepdf">voir le code source</a></small>
        </footer>
    </div>
    <div id="page-organization" style="padding-right: 350px;" class="d-none">
        <div style="height: 65px;" class="d-md-none"></div>
        <div id="container-pages" class="col-12 pt-1 vh-100 d-flex align-content-start flex-wrap" style="padding-bottom: 60px; overflow-y: scroll;">
        </div>
        <div class="btn-group-vertical position-absolute" style="top: 6px; right: 368px;">
            <button id="btn-zoom-increase" class="btn btn-outline-dark bg-white text-dark"><i class="bi bi-zoom-in"></i></button>
            <button id="btn-zoom-decrease" class="btn btn-outline-dark bg-white text-dark"><i class="bi bi-zoom-out"></i></button>
        </div>
        <div style="height: 55px;" class="d-md-none"></div>
        <div class="offcanvas offcanvas-end show d-none d-md-block shadow-sm" data-bs-backdrop="false" data-bs-scroll="true" data-bs-keyboard="false" tabindex="-1" id="sidebarTools" aria-labelledby="sidebarToolsLabel">
            <a class="btn btn-close btn-sm position-absolute opacity-25 d-none d-sm-none d-md-block" title="Fermer ce PDF et retourner à l'accueil" style="position: absolute; top: 2px; right: 2px; font-size: 10px;" href="/organization"></a>
            <div class="offcanvas-header mb-0 pb-0">
                <h5 class="mb-1 d-block w-100" id="sidebarToolsLabel">Organisation de PDF <span class="float-end me-2" title="Ce PDF est stocké sur votre ordinateur pour être signé par vous uniquement"><i class="bi-ui-checks-grid"></i></span></h5>
                <button type="button" class="btn-close text-reset d-md-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body pt-3">
                <ul id="list_pdf" class="list-group">
                </ul>
                <div class="d-grid gap-2 mt-2">
                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="document.getElementById('input_pdf_upload_2').click();"><i class="bi bi-plus-circle"></i> Ajouter un PDF</button>
                    <input id="input_pdf_upload_2" class="form-control d-none" type="file" accept=".pdf,application/pdf">
                </div>
                <hr />
                <div id="container_btn_select" class="opacity-50 card">
                    <div class="card-header small text-center p-1"><span>Aucune</span> page(s) séléctionnée(s) <button id="btn_cancel_select" type="button" class="btn-close btn-close-white float-end" aria-label="Close"></button></div>
                    <div class="card-body d-grid gap-2 p-2">
                        <button id="btn_rotate_select" disabled="disabled" type="button" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i> Tourner de 90°</button>
                        <button id="btn_delete_select" disabled="disabled" type="button" class="btn btn-sm btn-outline-secondary"><i class="bi bi-trash"></i> Supprimer</button>
                        <button id="save-select" class="btn btn-sm btn-outline-secondary" disabled="disabled" form="form_pdf" type="submit"><i class="bi bi-download"></i> Télécharger</button>
                    </div>
                </div>

                <div class="position-absolute bottom-0 pb-2 ps-0 pe-4 w-100">
                    <form id="form_pdf" action="/organize" method="post" enctype="multipart/form-data">
                        <input id="input_pdf" name="pdf[]" type="file" class="d-none" />
                        <input id="input_pages" type="hidden" value="" name="pages" />
                        <div class="d-grid gap-2 mt-2">
                            <button class="btn btn-primary" type="submit" id="save"><i class="bi bi-download"></i> Télécharger le PDF</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <span id="is_mobile" class="d-md-none"></span>

    <script src="/vendor/bootstrap.min.js?5.1.3"></script>
    <script src="/vendor/pdf.js?legacy"></script>
    <script>
    var maxSize = <?php echo $maxSize ?>;
    </script>
    <script src="/js/organization.js?202205210106"></script>
  </body>
</html>