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
    <div class="px-4 py-5 my-5 text-center">
        <h1 class="display-5 fw-bold"><i class="bi bi-vector-pen"></i> Signer un PDF</h1>
        <div class="col-lg-3 mx-auto">
            <form id="form_pdf_upload" action="/upload" method="POST" class="row g-3" enctype="multipart/form-data">
                <div class="col-12">
                  <label for="formFileLg" class="form-label">Choisir un PDF</label>
                  <input id="input_pdf_upload" class="form-control form-control-lg" name="pdf" type="file">
                </div>
                <div class="col-12">
                    <div class="d-grid gap-2">
                        <button class="btn btn-light" type="submit" id="save"><i class="bi bi-upload"></i> Transmettre le PDF</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
    <script type="text/javascript">
        document.getElementById('input_pdf_upload').addEventListener('change', function(event) {
            document.getElementById('form_pdf_upload').submit();
        });
    </script>
</body>
</html>
