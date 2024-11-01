<!doctype html>
<html lang="<?php echo $TRANSLATION_LANGUAGE ?>" dir="<?php echo $DIRECTION_LANGUAGE ?>" style="direction: <?php echo $DIRECTION_LANGUAGE ?>;" class="<?php echo $DIRECTION_LANGUAGE ?>">
  <head>
    <?php include('components/header.html.php'); ?>

    <link rel="icon" type="image/x-icon" href="<?php echo $REVERSE_PROXY_URL; ?>/favicon-organization.ico">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo $REVERSE_PROXY_URL; ?>/favicon-organization.png" />
    <title><?php echo _("Organize PDF"); ?></title>
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
            <p class="fs-5 fw-light mb-3 subtitle text-dark text-nowrap mt-2" style="overflow: hidden; text-overflow: ellipsis;"><?php echo _("Merge, sort, rotate, delete, extract pages"); ?></p>
            <div class="col-md-6 col-lg-5 col-xl-4 col-xxl-3 mx-auto">
                <div class="col-12">
                  <label class="form-label mt-3" for="input_pdf_upload"><?php echo _("Choose a PDF"); ?></label>
                  <input id="input_pdf_upload" placeholder="<?php echo _("Choose a PDF"); ?>" class="form-control form-control-lg" type="file" accept=".pdf,application/pdf" multiple="true" />
                  <?php if($PDF_DEMO_LINK): ?>
                  <p class="mt-2 small fw-light text-dark">&nbsp;</p>
                  <a class="btn btn-sm btn-link opacity-75" href="#<?php echo $PDF_DEMO_LINK ?>"><?php echo _("Test with a demo PDF"); ?></a>
                  <?php endif; ?>
                </div>
            </div>
        </div>
        <?php include('components/footer.html.php'); ?>
    </div>
    <div id="page-organization" class="d-none decalage-pdf-div">
        <div id="div-margin-top" style="height: 88px;" class="d-md-none"></div>
        <div style="top: 62px;" class="w-100 position-absolute text-center text-muted opacity-50 d-md-none"><em><?php echo _("Touch a page to select it"); ?></em></div>
        <div id="container-main">
            <div id="container-pages" class="col-12 pt-1 vh-100 d-flex align-content-start flex-wrap position-relative" style="overflow-y: scroll; overflow-x: hidden;" dir="auto">
            </div>
        </div>
        <div id="container-btn-zoom" class="btn-group-vertical position-fixed">
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
                    <button id="btn_liste_pdf" type="button" data-bs-toggle="modal" data-bs-target="#modalFichier" class="btn btn-outline-dark flex-grow-1 me-2"  style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
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
                <button id="save-select_mobile" class="btn btn-outline-primary" type="submit" form="form_pdf"><i class="bi bi-download"></i> <?php echo _("Download the selection"); ?></button>
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

    <?php include('components/common.html.php'); ?>
    <script src="<?php echo $REVERSE_PROXY_URL; ?>/js/organization.js?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/js/organization.js") ?>"></script>
  </body>
</html>
