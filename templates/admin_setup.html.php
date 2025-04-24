<!doctype html>
<html lang="<?php echo $TRANSLATION_LANGUAGE ?>" dir="<?php echo $DIRECTION_LANGUAGE ?>" style="direction: <?php echo $DIRECTION_LANGUAGE ?>;" class="<?php echo $DIRECTION_LANGUAGE ?>">
<head>
    <?php include('components/header.html.php'); ?>
    <title><?php echo _("Administration panel"); ?></title>
    <meta name="robots" content="noindex">
</head>
<body>
    <?php include('components/navtab.html.php'); ?>
    <div class="container fullpage">
    <div class="row">
        <div class="col-1"></div>
        <div class="col-10">
            <h1 class="px-4 py-4 display-5 fw-bold mb-0 mt-3 text-center"><i class="bi bi-wrench-adjustable"></i> <?php echo _("Configuration"); ?></h1>
            <h2 class="my-4"><?php echo _("Server installation"); ?></h2>

            <table class="table">
                <tbody>
                    <tr>
                        <th class="align-top col-3">PHP</th>
                        <td class="text-muted col-4"><?php echo 'PHP version ' . phpversion(); ?></td>
                        <td class="col-5">
                            <?php if (version_compare(phpversion(), "5.6.0", ">=")): ?>
                                <i class="bi bi-check-square text-success" title="<?php echo sprintf(_("Minimal version required : %s"), "5.6.0"); ?>"></i>
                            <?php else: ?>
                                <span class="text-danger">
                                    <i class="bi bi-exclamation-octagon-fill"></i> <?php echo sprintf(_("Minimal version required : %s"), "5.6.0"); ?>
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
                                    <i class="bi bi-exclamation-octagon-fill"></i> (<?php echo sprintf(_("Package %s required"), "librsvg2-bin"); ?>)
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
                                    <i class="bi bi-exclamation-octagon-fill"></i> (<?php echo sprintf(_("Package %s required"), "pdftk"); ?>)
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
                                    <i class="bi bi-exclamation-octagon-fill"></i> (<?php echo sprintf(_("Package %s required"), "imagemagick"); ?>)
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="align-top">potrace (bitmap to svg)</th>
                        <?php $potrace = implode(Image2SVG::ispotraceInstalled()); ?>
                        <td class="text-muted"><?php if ($potrace) { echo substr($potrace, 0, 12); } ?></td>
                        <td>
                            <?php if ($potrace): ?>
                                <i class="bi bi-check-square text-success"></i>
                            <?php else: ?>
                                <span class="text-danger">
                                    <i class="bi bi-exclamation-octagon-fill"></i> (<?php echo sprintf(_("Package %s required"), "potrace"); ?>)
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
                                    <i class="bi bi-exclamation-octagon-fill"></i>
                                    (<?php echo sprintf(_("Package %s required", "ghostscript")); ?>)
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
                                    <i class="bi bi-exclamation-octagon-fill"></i> (<?php echo sprintf(_("Package %s missing"), "gpg"); ?>)
                                /span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="align-top">CertUtil</th>
                        <?php $certUtil = NSSCryptography::isCertUtilInstalled(); ?>
                        <td class="text-muted"><?php if ($certUtil) { echo $certUtil; } ?></td>
                        <td>
                            <?php if ($certUtil): ?>
                                <i class="bi bi-check-square text-success"></i>
                            <?php else: ?>
                                <span class="text-warning">
                                    <i class="bi bi-exclamation-octagon-fill"></i> (<?php echo sprintf(_("Package %s missing"), "libnss3-tools"); ?>)
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="align-top">Pdfsig</th>
                        <?php $pdfsig = NSSCryptography::isPDFSigInstalled(); ?>
                        <td class="text-muted"><?php if ($pdfsig) { echo $pdfsig; } ?></td>
                        <td>
                            <?php if ($pdfsig): ?>
                                <i class="bi bi-check-square text-success"></i>
                            <?php else: ?>
                                <span class="text-warning">
                                    <i class="bi bi-exclamation-octagon-fill"></i> (<?php echo sprintf(_("Package %s missing"), "poppler-utils"); ?>)
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <h2 class="my-4"><?php echo _("File Configuration"); ?></h2>
            <table class="table">
                <tbody>
                    <tr>
                        <th class="align-top col-3">PDF_STORAGE_PATH</th>
                        <?php $storage_path_exists = isset($PDF_STORAGE_PATH) && is_dir($PDF_STORAGE_PATH);?>
                        <td class="text-muted col-4"><?php echo isset($PDF_STORAGE_PATH) ? $PDF_STORAGE_PATH : null ?></td>
                        <td class="col-5">
                            <?php if ($storage_path_exists): ?>
                                <i class="bi bi-check-square text-success"></i>
                            <?php else: ?>
                                <span class="text-danger">
                                    <i class="bi bi-exclamation-octagon-fill"></i> (<?php echo sprintf(_("The directory %s should exist and be writeable"), "PDF_STORAGE_PATH"); ?>)
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="align-top">UPLOADS</th>
                        <?php $uploads_path_exists = $UPLOADS && is_dir($UPLOADS);?>
                        <td class="text-muted"><?php if ($UPLOADS) { echo $UPLOADS; } ?></td>
                        <td>
                            <?php if ($uploads_path_exists): ?>
                                <i class="bi bi-check-square text-success"></i>
                            <?php else: ?>
                                <span class="text-danger">
                                    <i class="bi bi-exclamation-octagon-fill"></i> (<?php echo sprintf(_("The directory %s should exist and be writeable"), "UPLOADS"); ?>)
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <h5 class="py-2">List of authorized IP :</h5>
            <p>
                <?php foreach ($ADMIN_AUTHORIZED_IP as $ip): ?>
                    <?php echo $ip; ?>
                <?php endforeach; ?>
            </p>

        </div>
    </div>
    </div>
    <?php include('components/footer.html.php'); ?>
</body>
</html>
