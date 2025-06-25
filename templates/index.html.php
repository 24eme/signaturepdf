<!doctype html>
<html lang="<?php echo $TRANSLATION_LANGUAGE ?>" dir="<?php echo $DIRECTION_LANGUAGE ?>" style="direction: <?php echo $DIRECTION_LANGUAGE ?>;">
<head>
    <?php include('components/header.html.php'); ?>
    <title>Signature PDF - Signer et manipuler des PDF en ligne librement</title>
    <meta name="description" content="Signature PDF est un logiciel libre en ligne pour signer (seul ou à plusieurs), organiser ou compresser des fichiers PDF">
</head>
<body>
<noscript>
    <div class="alert alert-danger text-center" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?php echo _("Site not functional without JavaScript enabled");  ?>
    </div>
</noscript>
<?php include('components/navtab.html.php'); ?>
<div class="container pt-4 fullpage">
    <p class="lead text-center mb-2"><img src="logo.svg" style="height: 200px;" class="text-center" /></p>
    <h1 class="text-center h4 d-lg-none">Signature PDF</h1>
    <p class="lead mt-3 text-center mb-0"><?php echo _("Free open-source software for signing and manipulating PDFs") ?></p>
    <div class="row">
    <div class="col-xs-12 col-sm-6 mt-4">
        <div class="card">
          <div class="card-header"><?php echo _("Sign a PDF") ?></div>
          <div class="list-group list-group-flush">
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/signature" class="list-group-item list-group-item-action"><i class="bi bi-vector-pen"></i> <?php echo _("Add a signature") ?></a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/signature" class="list-group-item list-group-item-action"><i class="bi bi-type"></i> <?php echo _("Add initials") ?></a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/signature" class="list-group-item list-group-item-action"><i class="bi bi-card-text"></i> <?php echo _("Add a stamp") ?></a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/signature" class="list-group-item list-group-item-action"><i class="bi bi-textarea-t"></i> <?php echo _("Type text") ?></a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/signature" class="list-group-item list-group-item-action"><i class="bi bi-type-strikethrough"></i> <?php echo _("Strike through text") ?></a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/signature" class="list-group-item list-group-item-action"><i class="bi bi-people-fill"></i> <?php echo _("Allow multiple people to sign via a shared link") ?></a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/signature" class="list-group-item list-group-item-action"><i class="bi bi-patch-check"></i> <?php echo _("Sign with the server certificate") ?></a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/signature" class="list-group-item list-group-item-action"><i class="bi bi-droplet-half"></i> <?php echo _("Add a watermark") ?></a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/signature" class="list-group-item list-group-item-action"><i class="bi bi-bounding-box"></i> <?php echo _("Redact areas") ?></a>
        </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-6 mt-4">
        <div class="card">
          <div class="card-header"><?php echo _("Organize PDFs") ?></div>
          <div class="list-group list-group-flush">
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/organization" class="list-group-item list-group-item-action"><i class="bi bi-file-earmark-plus"></i> <?php echo _("Combine multiple PDF files") ?></a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/organization" class="list-group-item list-group-item-action"><i class="bi bi-arrows-move"></i> <?php echo _("Reorder pages") ?></a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/organization" class="list-group-item list-group-item-action"><i class="bi bi-download"></i> <?php echo _("Extract pages") ?></a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/organization" class="list-group-item list-group-item-action"><i class="bi bi-arrow-clockwise"></i> <?php echo _("Rotate pages") ?></a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/organization" class="list-group-item list-group-item-action"><i class="bi bi-trash"></i> <?php echo _("Delete pages") ?></a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/organization" class="list-group-item list-group-item-action"><i class="bi bi-images"></i> <?php echo _("Convert images to PDF") ?></a>

            </div>
        </div>
        <div class="card mt-4">
          <div class="card-header">
            <?php echo _("Edit a PDF"); ?>
          </div>
          <div class="list-group list-group-flush">
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/metadata" class="list-group-item list-group-item-action"><i class="bi bi-tags"></i> <?php echo _("Add, edit, or remove metadata from a PDF") ?></a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/compress" class="list-group-item list-group-item-action"><i class="bi bi-chevron-bar-contract"></i> <?php echo _("Compress and reduce the size of a PDF") ?></a>
            </div>
        </div>
    </div>
    </div>
</div>
<?php include('components/footer.html.php'); ?>

<span id="is_mobile" class="d-md-none"></span>
<script src="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap.bundle.min.js?5.3.3"></script>
<script src="<?php echo $REVERSE_PROXY_URL; ?>/js/common.js?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/js/common.js") ?>"></script>
</body>
</html>
