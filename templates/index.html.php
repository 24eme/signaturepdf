<!doctype html>
<html lang="<?php echo $TRANSLATION_LANGUAGE ?>" dir="<?php echo $DIRECTION_LANGUAGE ?>" style="direction: <?php echo $DIRECTION_LANGUAGE ?>;">
<head>
    <?php include('components/header.html.php'); ?>
    <title>Signature PDF - Accueil</title>
    <meta name="description" content="">
    <link rel="icon" type="image/x-icon" href="<?php echo $REVERSE_PROXY_URL; ?>/favicon.ico">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo $REVERSE_PROXY_URL; ?>/favicon.png" />
</head>
<body>
<noscript>
    <div class="alert alert-danger text-center" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?php echo _("Site not functional without JavaScript enabled");  ?>
    </div>
</noscript>
<?php include('components/navtab.html.php'); ?>
<div class="container">
    <p class="lead mt-4 text-center mb-3"><img src="logo.svg" style="height: 200px;" class="text-center" /></p>
    <p class="lead mt-4 text-center mb-0"><?php echo _("Free open-source software for signing and manipulating PDFs") ?></p>
    <div class="row">
    <div class="col-xs-12 col-sm-6 mt-4">
        <div class="card">
          <div class="card-header">Signer un PDF</div>
          <div class="list-group list-group-flush">
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/signature" class="list-group-item list-group-item-action"><i class="bi bi-vector-pen"></i> Ajouter une signature</a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/signature" class="list-group-item list-group-item-action"><i class="bi bi-type"></i> Ajouter une paraphe</a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/signature" class="list-group-item list-group-item-action"><i class="bi bi-card-text"></i> Ajouter un tampon</a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/signature" class="list-group-item list-group-item-action"><i class="bi bi-textarea-t"></i> Saisir du texte</a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/signature" class="list-group-item list-group-item-action"><i class="bi bi-type-strikethrough"></i> Rayer du texte</a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/signature" class="list-group-item list-group-item-action"><i class="bi bi-people-fill"></i> Signer à plusieurs via <i class="bi bi-share"></i> un partage</a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/signature" class="list-group-item list-group-item-action"><i class="bi bi-patch-check"></i> Signer avec le certificat du serveur</a>
        </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-6 mt-4">
        <div class="card">
          <div class="card-header">Organiser des PDF</div>
          <div class="list-group list-group-flush">
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/organization" class="list-group-item list-group-item-action"><i class="bi bi-file-earmark-plus"></i> Fusionner des PDF</a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/organization" class="list-group-item list-group-item-action"><i class="bi bi-arrows-move"></i> Réorganiser les pages</a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/organization" class="list-group-item list-group-item-action"><i class="bi bi-download"></i> Extraire des pages</a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/organization" class="list-group-item list-group-item-action"><i class="bi bi-arrow-clockwise"></i> Pivoter des pages</a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/organization" class="list-group-item list-group-item-action"><i class="bi bi-trash"></i> Supprimer des pages</a>
            </div>
        </div>

        <div class="card mt-4">
          <div class="card-header">
            Modifier un PDF
          </div>
          <div class="list-group list-group-flush">
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/metadata" class="list-group-item list-group-item-action"><i class="bi bi-tags"></i> Ajouter, modifier ou supprimer les métadonnées</a>
            <a href="<?php echo $REVERSE_PROXY_URL; ?>/compress" class="list-group-item list-group-item-action"><i class="bi bi-chevron-bar-contract"></i> Compresser et réduire la taille d'un PDF</a>
            </div>
        </div>
    </div>
    </div>
    <?php include('components/footer.html.php'); ?>
</div>

<span id="is_mobile" class="d-md-none"></span>
</body>
</html>
