<!doctype html>
<html lang="fr_FR">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap.min.css?5.1.1" rel="stylesheet">
    <link href="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap-icons.css?1.5.0" rel="stylesheet">
    <link href="<?php echo $REVERSE_PROXY_URL; ?>/css/app.css?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/css/app.css") ?>" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?php echo $REVERSE_PROXY_URL; ?>/favicon-metadata.ico">

    <title><?php echo _("Compress PDF"); ?></title>
</head>
<body>
<noscript>
    <div class="alert alert-danger text-center" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?php echo _("Site not functional without JavaScript enabled");  ?>
    </div>
</noscript>
<div id="page-upload">
    <div class="dropdown position-absolute top-0 end-0 mt-2 me-2">
        <button class="btn btn-outline-secondary btn-sm  dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="d-none d-md-inline"><?php echo _("Language"); ?></span>
            <span class="d-md-none"><i class="bi bi-translate"></i></span>
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
            <?php foreach ($LANGUAGES as $key => $langue):?>
                <li><a class="dropdown-item" href="?lang=<?php echo $key ?>"><?php echo $langue ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <ul class="nav justify-content-center nav-tabs mt-2">
        <li class="nav-item">
            <a class="nav-link" href="<?php echo $REVERSE_PROXY_URL; ?>/signature"> <?php echo sprintf(_("%s Sign"), '<i class="bi bi-vector-pen"></i>'); ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo $REVERSE_PROXY_URL; ?>/organization"> <?php echo sprintf(_("%s Organize"), '<i class="bi bi-ui-checks-grid"></i>'); ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo $REVERSE_PROXY_URL; ?>/metadata"> <?php echo sprintf(_("%s Metadata"), '<i class="bi bi-tags"></i>'); ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="<?php echo $REVERSE_PROXY_URL; ?>/compress"> <?php echo sprintf(_("%s Compress"), '<i class="bi bi-chevron-bar-contract"></i>'); ?></a>
        </li>
    </ul>
    <div class="px-4 py-4 text-center">
        <form method="post" action="<?php echo $REVERSE_PROXY_URL; ?>/compress" enctype="multipart/form-data">
            <h1 class="display-5 fw-bold mb-0 mt-3"> <?php echo sprintf(_("%s Compress a PDF"), '<i class="bi bi-chevron-bar-contract"></i>'); ?></h1>
            <p class="fw-light mb-3 subtitle text-dark text-nowrap" style="overflow: hidden; text-overflow: ellipsis;"><?php echo _("Reduce the size of a PDF"); ?></p>
            <div class="col-md-6 col-lg-5 col-xl-4 col-xxl-3 mx-auto">
                <div class="col-12">
                    <label class="form-label mt-3" for="input_pdf_upload"><?php echo _("Choose a PDF"); ?></label>
                    <input name="input_pdf_upload" id="input_pdf_upload" placeholder="<?php echo _("Choose a PDF"); ?>" class="form-control form-control-lg" type="file" accept=".pdf,application/pdf" onchange="handleFileChange()" />
                    <p class="mt-2 small fw-light text-dark">&nbsp;</p>
                    <div class="btn-group">
                        <button type="submit" name="compressionType" value="medium" id="compressBtn" class="btn btn-primary" disabled><?php echo sprintf(_("%s Compress"), '<i class="bi bi-download"></i>'); ?></button>
                        <button type="button" id="dropdownMenuReference" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent" disabled>
                            <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuReference">
                            <button type="submit" name="compressionType" value="low" id="lowCompressBtn" class="dropdown-item"><?php echo _("Low compression (highest quality)"); ?></button>
                            <button type="submit" name="compressionType" value="medium" id="mediumCompressBtn" class="dropdown-item"><?php echo sprintf(_("%s Medium compression %s (default)"), "<strong>", "</strong>"); ?></strong></button>
                            <button type="submit" name="compressionType" value="high" id="highCompressBtn" class="dropdown-item"><?php echo _("High compression (lowest quality)"); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <footer class="text-center text-muted mb-2 fixed-bottom opacity-75">
        <small><?php echo _("Free open-source software"); ?> <span class="d-none d-md-inline"><?php echo _("under AGPL-3.0 license"); ?></span> : <a href="https://github.com/24eme/signaturepdf"><?php echo _("see the source code"); ?></a><?php if($COMMIT): ?> <span class="d-none d-md-inline small">[<a href="https://github.com/24eme/signaturepdf/tree/<?php echo $COMMIT ?>"><?php echo $COMMIT ?></a>]</span><?php endif; ?></small>
    </footer>
</div>

<span id="is_mobile" class="d-md-none"></span>
<script src="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap.bundle.min.js?5.1.3"></script>
<script src="<?php echo $REVERSE_PROXY_URL; ?>/vendor/pdf.js?legacy"></script>
<script src="<?php echo $REVERSE_PROXY_URL; ?>/vendor/pdf-lib.min.js?1.17.1"></script>
<script>
    var defaultFields = <?php echo json_encode(isset($METADATA_DEFAULT_FIELDS) ? $METADATA_DEFAULT_FIELDS : array()); ?>;
</script>
<script src="<?php echo $REVERSE_PROXY_URL; ?>/js/compress.js?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/js/compress.js") ?>"></script>
</body>
</html>
