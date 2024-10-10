<!doctype html>
<html lang="<?php echo $TRANSLATION_LANGUAGE ?>" dir="<?php echo $DIRECTION_LANGUAGE ?>" style="direction: <?php echo $DIRECTION_LANGUAGE ?>;">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap.<?php echo $DIRECTION_LANGUAGE ?>.min.css?5.3.3" rel="stylesheet">
    <link href="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap-icons.min.css?1.11.3" rel="stylesheet">
    <link href="<?php echo $REVERSE_PROXY_URL; ?>/css/app.css?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/css/app.css") ?>" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?php echo $REVERSE_PROXY_URL; ?>/favicon-metadata.ico">

    <title><?php echo _("Editing PDF metadata"); ?></title>
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
        <h1 class="display-5 fw-bold mb-0 mt-3"> <?php echo sprintf(_("%s Edit metadata"), '<i class="bi bi-tags"></i>'); ?></h1>
        <p class="fs-5 fw-light mb-3 subtitle text-dark text-nowrap mt-2" style="overflow: hidden; text-overflow: ellipsis;"><?php echo _("Add, edit, or remove metadata from a PDF"); ?></p>
        <div class="col-md-6 col-lg-5 col-xl-4 col-xxl-3 mx-auto">
            <div class="col-12">
                <label class="form-label mt-3" for="input_pdf_upload"><?php echo _("Choose a PDF"); ?></label>
                <input id="input_pdf_upload" placeholder="<?php echo _("Choose a PDF"); ?>" class="form-control form-control-lg" type="file" accept=".pdf,application/pdf" />
                <p class="mt-2 small fw-light text-dark">&nbsp;</p>
                <?php if($PDF_DEMO_LINK): ?>
                    <a class="btn btn-sm btn-link opacity-75" href="#<?php echo $PDF_DEMO_LINK ?>"><?php echo _("Test with a demo PDF"); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include('components/footer.html.php'); ?>
</div>
<div id="page-metadata" class="d-none">
    <div id="div-margin-top" style="height: 88px;" class="d-md-none"></div>
    <div style="overflow: auto;" class="vh-100" id="container-main">
        <div id="form-metadata" class="mx-auto w-75 pt-3 pb-5">
            <h3><?php echo _("List of PDF metadata"); ?></h3>
            <div id="form-metadata-container">
            </div>
            <form id="form_metadata_add" class="position-relative">
                <hr class="text-muted mt-4 mb-3" />
                <div class="mb-3">
                    <label class="form-label text-muted" for="input_metadata_key"><?php echo _("Add new metadata"); ?></label>
                    <div class="form-floating">
                        <input id="input_metadata_key" name="metadata_key" type="text" class="form-control" required value="" style="border-bottom-right-radius: 0;  border-bottom-left-radius: 0;">
                        <label><?php echo _("Key"); ?></label>
                    </div>
                    <input id="input_metadata_value" readonly="readonly" style="border-top: 0; border-top-right-radius: 0;  border-top-left-radius: 0;" name="metadata_value" type="text" class="form-control bg-light opacity-50" value="" placeholder="<?php echo _("Value"); ?>" style="border-bottom-right-radius: 0;  border-bottom-left-radius: 0;">
                </div>
                <button type="submit" type="button" class="btn btn-outline-secondary float-end"><?php echo sprintf(_("%s Add"), '<i class="bi bi-plus-circle"></i>'); ?></button>
            </form>
        </div>
    </div>
    <div id="div-margin-bottom" style="height: 55px;" class="d-md-none"></div>
    <div class="offcanvas offcanvas-end show d-none d-md-block shadow-sm" data-bs-backdrop="false" data-bs-scroll="true" data-bs-keyboard="false" tabindex="-1" id="sidebarTools" aria-labelledby="sidebarToolsLabel">
        <a class="btn btn-close btn-sm position-absolute opacity-25 d-none d-sm-none d-md-block" title="<?php echo _("Close this PDF and return to the home page"); ?>" style="position: absolute; top: 2px; right: 2px; font-size: 10px;" href="/metadata"></a>
        <div class="offcanvas-header d-block mb-0 pb-0 border-bottom">
            <h5 class="mb-1 d-block w-100" id="sidebarToolsLabel"><?php echo _("Edit metadata"); ?><span class=\"float-end me-2\"><i class=\"bi bi-tags\"></i></span></h5>
            <button type="button" class="btn-close text-reset d-md-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            <p id="text_document_name" class="text-muted mb-2" style="text-overflow: ellipsis; white-space: nowrap; overflow: hidden;" title=""><i class="bi bi-files"></i> <span></span></p>
        </div>
        <div class="offcanvas-body bg-light" style="padding-bottom: 60px;">
            <div id="container-pages" dir="auto">
            </div>
        </div>
        <div class="position-absolute bg-white bottom-0 pb-2 ps-2 pe-2 w-100 border-top shadow-lg">
            <div id="btn_container" class="d-grid gap-2 mt-2">
                <button class="btn btn-primary" type="submit" id="save"><i class="bi bi-download"></i> <?php echo _("Save and download the PDF"); ?></button>
            </div>
        </div>
    </div>
    <div id="bottom_bar" class="position-fixed bottom-0 start-0 bg-white w-100 p-2 shadow-sm d-md-none">
        <div id="bottom_bar_action" class="d-grid gap-2">
            <button class="btn btn-primary" id="save_mobile"><i class="bi bi-download"></i> <?php echo _("Download the PDF"); ?></button>
        </div>
    </div>
</div>

<span id="is_mobile" class="d-md-none"></span>
<script src="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap.bundle.min.js?5.3.3"></script>
<script src="<?php echo $REVERSE_PROXY_URL; ?>/vendor/pdf.mjs?4.6.82-legacy" type="module"></script>
<script type="module">
    pdfjsLib.GlobalWorkerOptions.workerSrc = '<?php echo $REVERSE_PROXY_URL; ?>/vendor/pdf.worker.mjs?4.6.82-legacy';
</script>
<script src="<?php echo $REVERSE_PROXY_URL; ?>/vendor/pdf-lib.min.js?1.17.1"></script>
<script>
    var defaultFields = <?php echo json_encode(isset($METADATA_DEFAULT_FIELDS) ? $METADATA_DEFAULT_FIELDS : array()); ?>;
</script>
<script src="<?php echo $REVERSE_PROXY_URL; ?>/js/common.js?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/js/common.js") ?>"></script>
<script src="<?php echo $REVERSE_PROXY_URL; ?>/js/metadata.js?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/js/metadata.js") ?>"></script>
</body>
</html>
