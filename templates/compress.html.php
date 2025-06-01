<!doctype html>
<html lang="<?php echo $TRANSLATION_LANGUAGE ?>" dir="<?php echo $DIRECTION_LANGUAGE ?>" style="direction: <?php echo $DIRECTION_LANGUAGE ?>;">
<head>
    <?php include('components/header.html.php'); ?>
    <title>Signature PDF - Compresser un PDF en ligne</title>
    <meta name="description" content="Logiciel libre en ligne qui permet réduire la taille d'un pdf par compression.">
</head>
<body>
<noscript>
    <div class="alert alert-danger text-center" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?php echo _("Site not functional without JavaScript enabled");  ?>
    </div>
</noscript>
<div id="page-upload">
    <?php include('components/navtab.html.php'); ?>
    <div class="px-4 py-4 text-center fullpage">
        <form id="form_compress" method="post" action="<?php echo $REVERSE_PROXY_URL; ?>/compress" enctype="multipart/form-data">
            <h1 class="display-5 fw-bold mb-0 mt-3"> <?php echo sprintf(_("%s Compress a PDF"), '<i class="bi bi-chevron-bar-contract"></i>'); ?></h1>
            <p class="fs-5 fw-light mb-3 subtitle text-dark text-nowrap mt-2" style="overflow: hidden; text-overflow: ellipsis;"><?php echo _("Reduce the size of a PDF"); ?></p>
            <div class="col-md-6 col-lg-5 col-xl-4 col-xxl-3 mx-auto">
                <label class="form-label mt-4" for="input_pdf_upload"><?php echo _("Choose a PDF"); ?> <small class="opacity-75" style="cursor: help" title="<?php echo sprintf(_("The PDF must not exceed %s Mb"), round($maxSize / 1000 / 1000)); ?>"><i class="bi bi-info-circle"></i></small></label>
                <input name="input_pdf_upload" id="input_pdf_upload" placeholder="<?php echo _("Choose a PDF"); ?>" class="form-control form-control-lg" type="file" accept=".pdf,application/pdf" onchange="handleFileChange()" />
                <p class="mt-2 small fw-light text-dark"><?php echo _("The PDF will be processed by the server without being retained or stored") ?></p>

                <div class="btn-group mt-4 mb-2">
                    <button type="submit" name="compressionType" value="medium" id="compressBtn" class="btn btn-primary" disabled><i class="bi bi-chevron-bar-contract"></i> <?php echo _("Compress"); ?></button>
                    <button type="button" id="dropdownMenuReference" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent" disabled>
                        <span class="visually-hidden">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuReference">
                        <button type="submit" name="compressionType" value="low" id="lowCompressBtn" class="dropdown-item"><i class="bi bi-reception-0"></i> <?php echo _("Low compression (maximum quality)"); ?></button>
                        <button type="submit" name="compressionType" value="medium" id="mediumCompressBtn" class="dropdown-item"><i class="bi bi-reception-2"></i> <?php echo sprintf(_("%s Medium compression %s (default)"), "<strong>", "</strong>"); ?></button>
                        <button type="submit" name="compressionType" value="high" id="highCompressBtn" class="dropdown-item"><i class="bi bi-reception-4"></i> <?php echo _("High compression (minimum quality)"); ?></button>
                    </div>
                </div>
                <div id="error_message" class="alert alert-warning mt-5 d-none"></div>
                <div id="card_resultat" class="card text-bg-light shadow mt-5 d-none">
                  <div class="card-body">
                    <h6 class="card-title strong mt-2"><i class="bi bi-file-earmark"></i> <span id="uploaded_size" data-template-text="<?php echo _("%s Mb"); ?>"></span> <i class="bi bi-caret-right-fill"></i> <strong><i class="bi bi-file-earmark-zip"></i> <span id="size_compressed" data-template-text="<?php echo _("%s Mb"); ?>"></span></strong></h6>
                    <h6 id="pourcentage_compressed" class="card-subtitle mt-2 small text-muted" data-template-text="<?php echo _("Compressed at %s%"); ?>"></h6>
                    <input id="input_pdf_compressed" class="form-control form-control-lg d-none" type="file" />
                    <button type="button" id="downloadBtn" class="btn btn-primary mt-4 mb-2"><i class="bi bi-download"></i> <?php echo _("Download the PDF"); ?></button>
                  </div>
                </div>
            </div>
        </form>
    </div>
    <?php include('components/footer.html.php'); ?>
</div>

<?php include('components/common.html.php'); ?>
<script>
    var maxSize = <?php echo $maxSize ?>;
</script>
<script src="<?php echo $REVERSE_PROXY_URL; ?>/js/compress.js?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/js/compress.js") ?>"></script>
</body>
</html>
