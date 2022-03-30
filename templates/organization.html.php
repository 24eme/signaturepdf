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
    <div id="page-organization" class="d-none">
        <div id="container-pages" class="col-12 pt-1 vh-100" style="padding-bottom: 60px;">
        </div>
        <div class="position-fixed bottom-0 start-0 bg-white w-100 p-2 shadow-lg">
            <form id="form_pdf" action="/organize" method="post" enctype="multipart/form-data">
                <input id="input_pdf" name="pdf" type="file" class="d-none" />
                <input id="input_pages" type="hidden" value="" name="pages" />
                <div class="row">
                    <div class="col-3 d-none d-sm-none d-md-block">
                        <div class="form-switch mt-2 ms-2">
                            <input class="form-check-input" checked="checked" type="checkbox"  id="checkbox_all_pages">
                            <label class="form-check-label" for="checkbox_all_pages">Séléctionner toutes les pages</label>
                        </div>
                    </div>
                    <div class="col-2 d-none d-sm-none d-md-block">
                    </div>
                    <div class="col-1 d-none d-sm-none d-md-block">
                        <select class="form-select">
                          <option selected>4 pages</option>
                        </select>
                    </div>
                    <div class="col-4 d-none d-sm-none d-md-block">
                    </div>
                    <div class="col-sm-12 col-md-2">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" type="submit" id="save"><i class="bi bi-download"></i> Télécharger le PDF</button>
                        </div>
                    </div>
            </form>
        </div>
    </div>
    <span id="is_mobile" class="d-md-none"></span>

    <script src="/vendor/bootstrap.min.js?5.1.3"></script>
    <script src="/vendor/pdf.js?legacy"></script>
    <script>
    var maxSize = <?php echo $maxSize ?>;
    </script>
    <script src="/js/organization.js?202203261059"></script>
  </body>
</html>