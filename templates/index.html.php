<!doctype html>
<html lang="fr_FR">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="/vendor/bootstrap.min.css?5.1.1" rel="stylesheet">
    <link href="/vendor/bootstrap-icons.css?1.5.0" rel="stylesheet">
    <title>Signature PDF</title>
</head>
<body>
    <div class="px-4 py-5 my-5 text-center">
        <h1 class="display-5 fw-bold"><i class="bi bi-vector-pen"></i> Signer un PDF</h1>
        <div class="col-lg-3 mx-auto">
            <form id="form_pdf_upload" action="/upload" method="POST" class="row g-3" enctype="multipart/form-data">
                <div class="col-12">
                  <label for="input_pdf_upload" class="form-label">Choisir un PDF</label>
                  <input id="input_pdf_upload" class="form-control form-control-lg" name="pdf" type="file">
                  <a class="btn btn-sm btn-link opacity-75" href="/#https://raw.githubusercontent.com/24eme/signaturepdf/master/tests/files/document.pdf">(Tester avec le PDF de démo)</a>
                </div>
                <div class="col-12">
                    <div class="d-grid gap-2">
                        <button class="btn btn-light" type="submit" id="save"><i class="bi bi-upload"></i> Transmettre le PDF</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <footer class="text-center text-muted mb-2 fixed-bottom">
        <small>Logiciel libre sous license AGPL-3.0 : <a href="https://github.com/24eme/signaturepdf">voir le code source</a></small>
    </footer>

    <script>
        document.getElementById('input_pdf_upload').addEventListener('change', function(event) {
            document.getElementById('form_pdf_upload').submit();
        });
        async function uploadFromUrl(url) {
            var response = await fetch(url);
            if(response.status != 200) {
                return;
            }
            var pdfBlob = await response.blob();
            if(pdfBlob.type != 'application/pdf' && pdfBlob.type != 'application/octet-stream') {
                return;
            }
            var dataTransfer = new DataTransfer();
            var filename = url.replace(/^.*\//, '');
            dataTransfer.items.add(new File([pdfBlob], filename, {
                type: 'application/pdf'
            }));
            document.getElementById('input_pdf_upload').files = dataTransfer.files;
            document.getElementById('input_pdf_upload').dispatchEvent(new Event("change"));

            history.replaceState({}, "Signature de PDF", "/");
        }
        if(window.location.hash) {
            uploadFromUrl(window.location.hash.replace(/^\#/, ''));
        }
        window.addEventListener('hashchange', function() {
            uploadFromUrl(window.location.hash.replace(/^\#/, ''));
        })
    </script>
</body>
</html>
