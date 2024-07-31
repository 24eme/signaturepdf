<!doctype html>
<html lang="<?php echo $TRANSLATION_LANGUAGE ?>" dir="<?php echo $DIRECTION_LANGUAGE ?>" style="direction: <?php echo $DIRECTION_LANGUAGE ?>;" class="<?php echo $DIRECTION_LANGUAGE ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Logiciel libre de signature de PDF en ligne">
    <link href="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap.<?php echo $DIRECTION_LANGUAGE ?>.min.css?5.1.1" rel="stylesheet">
    <link href="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap-icons.css?1.11.1" rel="stylesheet">
    <link href="<?php echo $REVERSE_PROXY_URL; ?>/css/app.css?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/css/app.css") ?>" rel="stylesheet">
    <title><?php echo _("Administration panel"); ?></title>
</head>
<body>
    <?php include('components/navtab.html.php'); ?>
    <div class="row">
        <div class="col-2"></div>
        <div class="col-8">
            <h1 class="px-4 py-4 display-5 fw-bold mb-0 mt-3 text-center"><i class="bi bi-wrench-adjustable"></i> <?php echo _("Configuration"); ?></h1>
            <h2 class="my-4"><?php echo _("Server installation"); ?></h2>

            <table class="table">
                <tbody>
                    <tr>
                        <th class="align-top">PHP</th>
                        <td class="text-muted"><?php echo 'PHP version ' . phpversion(); ?></td>
                        <td>
                            <?php if (version_compare(phpversion(), "5.6.0", ">=")): ?>
                                <i class="bi bi-check-square text-success" title="<?php echo _("Minimal version required : 5.6.0"); ?>"></i>
                            <?php else: ?>
                                <span class="text-danger">
                                    <i class="bi bi-exclamation-octagon-fill"></i> (Minimal version required : 5.6.0)
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="align-top">rsvg-convert</th>
                        <?php $rsvgConvert = implode(PDFSignature::isrsvgConvertInstalled()); ?>
                        <td class="text-muted"><?php echo $rsvgConvert; ?></td>
                        <td>
                            <?php if ($rsvgConvert): ?>
                                <i class="bi bi-check-square text-success"></i>
                            <?php else: ?>
                                <span class="text-danger">
                                    <i class="bi bi-exclamation-octagon-fill"></i> (<?php echo _("Package librsvg2-bin required"); ?>)
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="align-top">pdftk</th>
                        <?php $pdftk = implode(PDFSignature::ispdftkInstalled()); ?>
                        <td class="text-muted"><?php if ($pdftk) { echo substr($pdftk, 0, 24); }; ?></td>
                        <td>
                            <?php if ($pdftk): ?>
                                <i class="bi bi-check-square text-success"></i>
                            <?php else: ?>
                                <span class="text-danger">
                                    <i class="bi bi-exclamation-octagon-fill"></i> (<?php echo _("Package pdftk required"); ?>)
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="align-top">image magick</th>
                        <?php $imageMagick = implode(Image2SVG::isimageMagickInstalled()); ?>
                        <td class="text-muted"><?php if ($imageMagick) { echo substr($imageMagick, 9, 21); }; ?></td>
                        <td>
                            <?php if ($imageMagick): ?>
                                <i class="bi bi-check-square text-success"></i>
                            <?php else: ?>
                                <span class="text-danger">
                                    <i class="bi bi-exclamation-octagon-fill"></i> (<?php echo _("Package imagemagick required"); ?>)
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="align-top">potrace</th>
                        <?php $potrace = implode(Image2SVG::ispotraceInstalled()); ?>
                        <td class="text-muted"><?php if ($potrace) { echo substr($potrace, 0, 12); } ?></td>
                        <td>
                            <?php if ($potrace): ?>
                                <i class="bi bi-check-square text-success"></i>
                            <?php else: ?>
                                <span class="text-danger">
                                    <i class="bi bi-exclamation-octagon-fill"></i> (<?php echo _("Package potrace required"); ?>)
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="align-top">ghostscript</th>
                        <?php $ghostscript = implode(Compression::isgsInstalled()); ?>
                        <td class="text-muted"><?php if ($ghostscript) { echo 'Ghostscript version ' . $ghostscript; } ?></td>
                        <td>
                            <?php if ($ghostscript): ?>
                                <i class="bi bi-check-square text-success"></i>
                            <?php else: ?>
                                <span class="text-danger">
                                    <i class="bi bi-exclamation-octagon-fill"></i> (<?php echo _("Package ghostscript required"); ?>)
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="align-top">gpg</th>
                        <?php $gpg = implode(GPGCryptography::isGpgInstalled()); ?>
                        <td class="text-muted"><?php if ($gpg) { echo substr($gpg, 0, 18); } ?></td>
                        <td>
                            <?php if ($gpg): ?>
                                <i class="bi bi-check-square text-success"></i>
                            <?php else: ?>
                                <span class="text-warning">
                                    <i class="bi bi-exclamation-octagon-fill"></i> (<?php echo _("Package gpg missing"); ?>)
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>



                </tbody>
            </table>
            <h5 class="py-2"><?php echo _("List of authorized IP for debugging purposes : "); ?></h4>
            <p>
                <?php foreach ($AUTHORIZED_IP as $ip): ?>
                    <?php echo $ip . ' '; ?>
                <?php endforeach; ?>
            </p>

        </div>
    </div>
</body>
</html>
