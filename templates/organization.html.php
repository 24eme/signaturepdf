<!doctype html>
<html lang="<?php echo $TRANSLATION_LANGUAGE ?>"<?php if($TRANSLATION_LANGUAGE == 'ar'): ?> dir="rtl" style="direction: rtl;"<?php endif;?>>
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap.min.css?5.1.1" rel="stylesheet">
    <link href="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap-icons.css?1.5.0" rel="stylesheet">
    <link href="<?php echo $REVERSE_PROXY_URL; ?>/css/app.css?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/css/app.css") ?>" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?php echo $REVERSE_PROXY_URL; ?>/favicon-organization.ico">

    <title><?php _("Organize PDF"); ?></title>
  </head>
  <body>
    <noscript>
        <div class="alert alert-danger text-center" role="alert">
          <i class="bi bi-exclamation-triangle"></i><?php echo _("Site not functional without JavaScript enabled"); ?>
        </div>
    </noscript>
    <div id="page-upload">
        <?php include('components/navtab.html.php'); ?>
        <div class="px-4 py-4 text-center">
            <h1 class="display-5 fw-bold mb-0 mt-3"><?php echo sprintf(_("%s Organize PDF"), '<i class="bi bi-ui-checks-grid"></i>'); ?></h1>
            <p class="fw-light mb-3 subtitle text-dark text-nowrap" style="overflow: hidden; text-overflow: ellipsis;"><?php echo _("Merge, sort, rotate, delete, extract pages"); ?></p>
            <div class="col-md-6 col-lg-5 col-xl-4 col-xxl-3 mx-auto">
                <div class="col-12">
                  <label class="form-label mt-3" for="input_pdf_upload"><?php echo _("Choose a PDF"); ?> <small class="opacity-75" style="cursor: help" title="<?php echo _("The PDF must not exceed "); ?> <?php echo round($maxSize / 1024 / 1024) ?> <?php echo _("Mb"); ?>"><i class="bi bi-info-circle"></i></small></label>
                  <input id="input_pdf_upload" placeholder="<?php echo _("Choose a PDF"); ?>" class="form-control form-control-lg" type="file" accept=".pdf,application/pdf" multiple="true" />
                  <p class="mt-2 small fw-light text-dark"><?php echo _("The PDF will be processed by the server without being retained or stored"); ?></p>
                  <?php if($PDF_DEMO_LINK): ?>
                  <a class="btn btn-sm btn-link opacity-75" href="#<?php echo $PDF_DEMO_LINK ?>"><?php echo _("Test with a demo PDF"); ?></a>
                  <?php endif; ?>
                </div>
            </div>
        </div>
        <footer class="text-center text-muted mb-2 fixed-bottom opacity-75">
            <small><?php echo _("Free open-source software"); ?> <span class="d-none d-md-inline"><?php echo _("under AGPL-3.0 license"); ?></span> : <a href="https://github.com/24eme/signaturepdf"><?php echo _("see the source code"); ?></a><?php if($COMMIT): ?> <span class="d-none d-md-inline small">[<a href="https://github.com/24eme/signaturepdf/tree/<?php echo $COMMIT ?>"><?php echo $COMMIT ?></a>]</span><?php endif; ?></small>
        </footer>
    </div>
    <div id="page-organization" style="padding-right: 350px;" class="d-none">
        <div id="div-margin-top" style="height: 88px;" class="d-md-none"></div>
        <div style="top: 62px;" class="w-100 position-absolute text-center text-muted opacity-50 d-md-none"><em><?php echo _("Touch a page to select it"); ?></em></div>
        <div id="container-main">
            <div id="container-pages" class="col-12 pt-1 vh-100 d-flex align-content-start flex-wrap position-relative" style="overflow-y: scroll; overflow-x: hidden;">
            </div>
        </div>
        <div id="container-btn-zoom" class="btn-group-vertical position-fixed" style="top: 6px; right: 368px;">
            <button id="btn-zoom-increase" class="btn btn-outline-dark bg-white text-dark"><i class="bi bi-zoom-in"></i></button>
            <button id="btn-zoom-decrease" class="btn btn-outline-dark bg-white text-dark"><i class="bi bi-zoom-out"></i></button>
        </div>
        <div id="div-margin-bottom" style="height: 55px;" class="d-md-none"></div>
        <div class="offcanvas offcanvas-end show d-none d-md-block shadow-sm" data-bs-backdrop="false" data-bs-scroll="true" data-bs-keyboard="false" tabindex="-1" id="sidebarTools" aria-labelledby="sidebarToolsLabel">
            <a class="btn btn-close btn-sm position-absolute opacity-25 d-none d-sm-none d-md-block" title="<?php echo _("Close this PDF and return to home"); ?>" style="position: absolute; top: 2px; right: 2px; font-size: 10px;" href="<?php echo $REVERSE_PROXY_URL; ?>/organization"></a>
            <div class="offcanvas-header mb-0 pb-0">
                <h5 class="mb-1 d-block w-100" id="sidebarToolsLabel"><?php echo _("PDF organization"); ?> <span class="float-end me-2" title="<?php echo _("This PDF is stored on your computer to be signed by you only"); ?>"><i class="bi-ui-checks-grid"></i></span></h5>
                <button type="button" class="btn-close text-reset d-md-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body pt-3" style="padding-bottom: 60px;">
                <div id="list_pdf_container">
                    <ul id="list_pdf" class="list-group">
                    </ul>
                </div>
                <div class="d-grid gap-2 mt-2">
                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="document.getElementById('input_pdf_upload_2').click();"><?php echo sprintf(_("%s Add a PDF"), '<i class="bi bi-plus-circle"></i>'); ?></button>
                    <input id="input_pdf_upload_2" class="form-control d-none" type="file" accept=".pdf,application/pdf" multiple="true">
                </div>
                <hr />
                <div id="container_btn_select" class="opacity-50 card">
                    <div class="card-header small text-center p-1"><?php echo sprintf(_("%s page(s) selected"), '<span>0</span>'); ?> <button id="btn_cancel_select" type="button" class="btn-close btn-close-white float-end" aria-label="Close"></button></div>
                    <div class="card-body d-grid gap-2 p-2">
                        <button id="btn_rotate_select" disabled="disabled" type="button" class="btn btn-sm btn-outline-secondary"><?php echo sprintf(_("%s Rotate 90°"), '<i class="bi bi-arrow-clockwise"></i>'); ?></button>
                        <button id="btn_drag_select" disabled="disabled" type="button" class="btn btn-sm btn-outline-secondary"><?php echo sprintf(_("%s Move"), '<i class="bi bi-arrows-move"></i>'); ?></button>
                        <button id="btn_delete_select" disabled="disabled" type="button" class="btn btn-sm btn-outline-secondary"><?php echo sprintf(_("%s Delete"), '<i class="bi bi-trash"></i>'); ?></button>
                        <button id="save-select" class="btn btn-sm btn-outline-secondary" disabled="disabled" form="form_pdf" type="submit"><?php echo sprintf(_("%s Extract and download"), '<i class="bi bi-download"></i>'); ?></button>
                    </div>
                    <div class="card-footer d-none small text-center p-1 border-primary bg-primary bg-opacity-25"><a id="btn_cancel_select_footer" type="button" aria-label="Close" style="text-decoration: none;" class="text-primary"><?php echo sprintf(_("%s Cancel selection"), '<i class="bi bi-x-lg"></i>'); ?></a></div>
                </div>

                <div class="position-absolute bottom-0 pb-2 ps-0 pe-4 w-100">
                    <form id="form_pdf" action="<?php echo $REVERSE_PROXY_URL; ?>/organize" method="post" enctype="multipart/form-data">
                        <input id="input_pdf" name="pdf[]" type="file" class="d-none" />
                        <input id="input_pages" type="hidden" value="" name="pages" />
                        <div id="btn_container" class="d-grid gap-2 mt-2">
                            <button class="btn btn-primary" type="submit" id="save"><?php echo sprintf(_("%s Download the full PDF"), '<i class="bi bi-download"></i>'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="top_bar" class="position-fixed top-0 start-0 bg-white w-100 shadow-sm d-md-none p-2">
            <div id="top_bar_action">
                <div class="d-flex" role="group">
                    <button id="btn_liste_pdf" type="button" data-bs-toggle="modal" data-bs-target="#modalFichier" class="btn btn-dark flex-grow-1 me-2"  style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <i class="bi bi-files"></i> <span id="liste_pdf_titre_mobile"></span>
                    </button>
                    <button type="button" class="btn btn-outline-dark position-relative" style="padding-left: 30px;"  onclick="document.getElementById('input_pdf_upload_2').click(); this.blur();"><?php echo sprintf(_("%s Add a PDF"), '<i class="bi bi-plus-circle position-absolute" style="left: 10px;"></i>'); ?></button>
                </div>
            </div>
            <div id="top_bar_action_selection" class="d-none">
                <div id="top_bar_action_selection_recap" class="bg-primary text-white text-center rounded-top p-1 position-relative"><button id="btn_liste_pdf_bar" type="button" style="text-decoration: none;left: 0px; top:0px;" class="btn bg-white bg-opacity-50 text-primary position-absolute p-0 ps-1 pe-1 mt-1 ms-1"><i class="bi bi-files"></i>&nbsp;<span></span> PDF</button><span id="top_bar_action_selection_recap_nb_pages"><?php echo _("No"); ?>></span> <?php echo _("page"); ?><button id="btn_cancel_select_mobile" type="button" style="text-decoration: none;right: 0px; top:0px;" class="btn bg-white bg-opacity-50 text-primary position-absolute p-0 ps-1 pe-1 mt-1 me-1"><i class="bi bi-x-lg"></i>&nbsp;<?php echo _("Cancel"); ?></button></div>
                <div class="btn-group w-100">
                    <button id="btn_rotate_select_mobile" type="button" class="btn btn-outline-primary" style="border-top-left-radius: 0 !important;"><?php echo sprintf(_("%s Turn"), '<i class="bi bi-arrow-clockwise"></i>'); ?></button>
                    <button id="btn_drag_select_mobile" type="button" class="btn btn-outline-primary"><?php echo sprintf(_("%s Move"), '<i class="bi bi-arrows-move"></i>'); ?></button>
                    <button id="btn_delete_select_mobile" type="button" class="btn btn-outline-primary" style="border-top-right-radius: 0 !important;"><?php echo sprintf(_("%s Delete"), '<i class="bi bi-trash"></i>'); ?></button>
                </div>
            </div>
        </div>
        <div id="bottom_bar" class="position-fixed bottom-0 start-0 bg-white w-100 p-2 shadow-sm d-md-none">
            <div id="bottom_bar_action" class="d-grid gap-2">
                <button class="btn btn-primary" type="submit" id="save_mobile"><?php echo sprintf(_("%s Download the full PDF"), '<i class="bi bi-download"></i>'); ?></button>
            </div>
            <div id="bottom_bar_action_selection" class="d-grid gap-2 d-none">
                <button id="save-select_mobile" class="btn btn-outline-primary" type="submit" form="form_pdf"><?php echo sprintf(_("Download the selection"), '<i class="bi bi-download"></i>'); ?></button>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalDrag" tabindex="-1">
      <div class="modal-dialog modal-dialog-scrollable modal-xl modal-fullscreen-md-down">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><?php echo("Page move"); ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body bg-light ps-5">
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="modalFichier" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel"><?php echo _("PDF documents"); ?></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>

    <span id="is_mobile" class="d-md-none"></span>

    <script src="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap.bundle.min.js?5.1.3"></script>
    <script src="<?php echo $REVERSE_PROXY_URL; ?>/vendor/pdf.js?legacy"></script>
    <script>
    var maxSize = <?php echo $maxSize ?>;
    var trad = <?php echo json_encode([
        'Select this page' => _('Select this page'),
        'Delete this page' => _('Delete this page'),
        'Restore this page' => _('Restore this page'),
        'Move this page' => _('Move this page'),
        'Move here' => _('Move here'),
        'Turn this page' => _('Turn this page'),
        'Download this page' => _('Download this page')
    ]); ?>;
    </script>
    <script src="<?php echo $REVERSE_PROXY_URL; ?>/js/organization.js?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/js/organization.js") ?>"></script>
  </body>
</html>
