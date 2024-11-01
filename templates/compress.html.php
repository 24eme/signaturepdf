<!doctype html>
<html lang="<?php echo $TRANSLATION_LANGUAGE ?>" dir="<?php echo $DIRECTION_LANGUAGE ?>" style="direction: <?php echo $DIRECTION_LANGUAGE ?>;">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap.<?php echo $DIRECTION_LANGUAGE ?>.min.css?5.3.3" rel="stylesheet">
    <link href="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap-icons.min.css?1.11.3" rel="stylesheet">
    <link href="<?php echo $REVERSE_PROXY_URL; ?>/css/app.css?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/css/app.css") ?>" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?php echo $REVERSE_PROXY_URL; ?>/favicon-compress.ico">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo $REVERSE_PROXY_URL; ?>/favicon-compress.png" />
    <title><?php echo _("Compress PDF"); ?></title>
</head>
<body>
<noscript>
    <div class="alert alert-danger text-center" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?php echo _("Site not functional without JavaScript enabled");  ?>
    </div>
</noscript>
<div id="page-upload">
    <?php include('components/navtab.html.php'); ?>
    <div class="px-4 py-4 text-center">
        <form method="post" action="<?php echo $REVERSE_PROXY_URL; ?>/compress" enctype="multipart/form-data">
            <h1 class="display-5 fw-bold mb-0 mt-3"> <?php echo sprintf(_("%s Compress a PDF"), '<i class="bi bi-chevron-bar-contract"></i>'); ?></h1>
            <p class="fs-5 fw-light mb-3 subtitle text-dark text-nowrap mt-2" style="overflow: hidden; text-overflow: ellipsis;"><?php echo _("Reduce the size of a PDF"); ?></p>
            <div class="col-md-6 col-lg-5 col-xl-4 col-xxl-3 mx-auto">
                <div class="col-12">
                    <label class="form-label mt-3" for="input_pdf_upload"><?php echo _("Choose a PDF"); ?> <small class="opacity-75" style="cursor: help" title="<?php echo _("The PDF must not exceed "); ?> <?php echo round($maxSize / 1024 / 1024) ?> <?php echo _("Mb"); ?>"><i class="bi bi-info-circle"></i></small></label>
                    <input name="input_pdf_upload" id="input_pdf_upload" placeholder="<?php echo _("Choose a PDF"); ?>" class="form-control form-control-lg" type="file" accept=".pdf,application/pdf" onchange="handleFileChange()" />
                    <p class="mt-2 small fw-light text-dark"><?php echo _("The PDF will be processed by the server without being retained or stored") ?></p>
                    <?php if ($error_message == "PDF optimized"): ?>
                        <div class="alert alert-danger">
                            <p><?php echo _("Your pdf is already optimized"); ?></p>
                        </div>
                    <?php endif; ?>
                    <div class="btn-group">
                        <button type="submit" name="compressionType" value="medium" id="compressBtn" class="btn btn-primary" disabled><i class="bi bi-download"></i> <?php echo _("Compress"); ?></button>
                        <button type="button" id="dropdownMenuReference" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent" disabled>
                            <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuReference">
                            <button type="submit" name="compressionType" value="low" id="lowCompressBtn" class="dropdown-item"><?php echo _("Low compression (maximum quality)"); ?></button>
                            <button type="submit" name="compressionType" value="medium" id="mediumCompressBtn" class="dropdown-item"><?php echo sprintf(_("%s Medium compression %s (default)"), "<strong>", "</strong>"); ?></button>
                            <button type="submit" name="compressionType" value="high" id="highCompressBtn" class="dropdown-item"><?php echo _("High compression (minimum quality)"); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php include('components/footer.html.php'); ?>
</div>

<span id="is_mobile" class="d-md-none"></span>
<script src="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap.bundle.min.js?5.1.3"></script>
<script>
    var defaultFields = <?php echo json_encode(isset($METADATA_DEFAULT_FIELDS) ? $METADATA_DEFAULT_FIELDS : array()); ?>;
</script>
<script src="<?php echo $REVERSE_PROXY_URL; ?>/js/compress.js?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/js/compress.js") ?>"></script>
</body>
</html>
