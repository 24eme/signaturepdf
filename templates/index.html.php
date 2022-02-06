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
            <div class="col-12">
              <label for="input_pdf_upload" class="form-label">Choisir un PDF</label>
              <input id="input_pdf_upload" class="form-control form-control-lg" type="file" accept=".pdf,application/pdf">
              <p class="mt-1 opacity-50"><small class="text-muted">Le PDF ne doit pas dépasser <?php echo round($maxSize / 1024 / 1024) ?> Mo et <?php echo $maxPage ?> pages</small></p>
              <a class="btn btn-sm btn-link opacity-75" href="/#https://raw.githubusercontent.com/24eme/signaturepdf/master/tests/files/document.pdf">Tester avec un PDF de démo</a>
            </div>
        </div>
    </div>
    <footer class="text-center text-muted mb-2 fixed-bottom">
        <small>Logiciel libre sous license AGPL-3.0 : <a href="https://github.com/24eme/signaturepdf">voir le code source</a></small>
    </footer>

    <script>
        (async function () {
            const cache = await caches.open('pdf');
            var maxSize = <?php echo $maxSize ?>;
            document.getElementById('input_pdf_upload').addEventListener('change', async function(event) {
                    if(document.getElementById('input_pdf_upload').files[0].size > maxSize) {

                    alert("Le PDF ne doit pas dépasser <?php echo round($maxSize / 1024 / 1024) ?> Mo");
                    document.getElementById('input_pdf_upload').value = "";
                    return;
                }
                let filename = document.getElementById('input_pdf_upload').files[0].name;
                var response = new Response(document.getElementById('input_pdf_upload').files[0], { "status" : 200, "statusText" : "OK" });
                let urlPdf = '/pdf/#'+filename;
                await cache.put(urlPdf, response);
                document.location = '/sign/#'+filename;
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
            if(window.location.hash && window.location.hash.match(/^\#http/)) {
                uploadFromUrl(window.location.hash.replace(/^\#/, ''));
            }
            window.addEventListener('hashchange', function() {
                uploadFromUrl(window.location.hash.replace(/^\#/, ''));
            })
        })();
    </script>
</body>
</html>
