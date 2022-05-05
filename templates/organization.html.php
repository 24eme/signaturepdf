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
  <body class="bg-light">
    <noscript>
        <div class="alert alert-danger text-center" role="alert">
          <i class="bi bi-exclamation-triangle"></i> Site non fonctionnel sans JavaScript activé
        </div>
    </noscript>
    <div id="page-upload">
        <div class="px-4 py-5 my-5 text-center">
            <h1 class="display-5 fw-bold"><i class="bi bi-ui-checks-grid"></i> Organiser un PDF</h1>
            <div class="col-lg-3 mx-auto">
                <div class="col-12">
                  <label for="input_pdf_upload" class="form-label">Choisir un PDF</label>
                  <input id="input_pdf_upload" class="form-control form-control-lg" type="file" accept=".pdf,application/pdf">
                  <p class="mt-1 opacity-50"><small class="text-muted">Le PDF ne doit pas dépasser <?php echo round($maxSize / 1024 / 1024) ?> Mo</small></p>
                  <a class="btn btn-sm btn-link opacity-75" href="/organization#https://raw.githubusercontent.com/24eme/signaturepdf/master/tests/files/document.pdf">Tester avec un PDF de démo</a>
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
            <div class="offcanvas-body pt-4">
                <ul id="list_pdf" class="list-group">
                </ul>
                <div class="d-grid gap-2 mt-2">
                    <button type="button" class="btn btn-light" onclick="document.getElementById('input_pdf_upload_2').click();"><i class="bi bi-plus-circle"></i> Ajouter un PDF</button>
                    <input id="input_pdf_upload_2" class="form-control d-none" type="file" accept=".pdf,application/pdf">
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
    <script src="/js/organization.js?202203301018"></script>
  </body>
</html>