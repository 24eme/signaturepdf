<!doctype html>
<html lang="<?php echo $TRANSLATION_LANGUAGE ?>" dir="<?php echo $DIRECTION_LANGUAGE ?>" style="direction: <?php echo $DIRECTION_LANGUAGE ?>;" class="<?php echo $DIRECTION_LANGUAGE ?>">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Signature PDF est un logiciel libre en ligne qui permet de signer, parapher, tamponner, compléter un pdf seul ou à plusieurs.">
    <link href="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap.<?php echo $DIRECTION_LANGUAGE ?>.min.css?5.3.3" rel="stylesheet">
    <link href="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap-icons.min.css?1.11.3" rel="stylesheet">
    <link href="<?php echo $REVERSE_PROXY_URL; ?>/css/app.css?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/css/app.css") ?>" rel="stylesheet">
    <title>Signature PDF Libre - Signer un PDF en ligne</title>
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
        <h1 class="display-5 fw-bold mb-0 mt-3"><?php echo sprintf(_("%s Sign a PDF"), '<i class="bi bi-vector-pen"></i>'); ?></h1>
        <p class="fw-light mb-3 subtitle text-dark text-nowrap" style="overflow: hidden; text-overflow: ellipsis;"><?php echo _("Sign, initial, stamp, complete a document") ?></p>
        <div class="col-md-6 col-lg-5 col-xl-4 col-xxl-3 mx-auto">
            <div class="col-12">
                <label class="form-label mt-3" for="input_pdf_upload"><?php echo _("Choose a PDF"); ?> <small class="opacity-75" style="cursor: help" title="<?php echo _("The PDF should not exceed"); ?> <?php echo round($maxSize / 1024 / 1024) ?> <?php echo _("MB and"); ?> <?php echo $maxPage ?> <?php echo _("pages"); ?>"><i class="bi bi-info-circle"></i></small></label>
                    <input id="input_pdf_upload" placeholder="<?php echo _("Choose a PDF") ?>" class="form-control form-control-lg" type="file" accept=".pdf,application/pdf" />
                    <p class="mt-2 small fw-light text-dark"><?php echo _("The PDF will be processed by the server without being retained or stored") ?></p>
                    <?php if($PDF_DEMO_LINK): ?>
                        <a class="btn btn-sm btn-link opacity-75" href="#<?php echo $PDF_DEMO_LINK ?>"><?php echo _("Test with a demo PDF") ?></a>
                    <?php endif; ?>
            </div>
        </div>
    </div>
    <footer class="text-center text-muted mb-2 fixed-bottom opacity-75">
        <small><?php echo _("Free open-source software"); ?> <span class="d-none d-md-inline"><?php echo _("under AGPL-3.0 license"); ?></span> : <a href="https://github.com/24eme/signaturepdf"><?php echo _("see the source code"); ?></a><?php if($COMMIT): ?> <span class="d-none d-md-inline small">[<a href="https://github.com/24eme/signaturepdf/tree/<?php echo $COMMIT ?>"><?php echo $COMMIT ?></a>]</span><?php endif; ?></small>
    </footer>
</div>
<div id="page-signature" class="d-none decalage-pdf-div">
    <?php if(isset($hash)): ?>
        <div id="alert-signature-help" class="position-relative d-none">
            <div class="alert alert-primary alert-dismissible position-absolute top-0 start-50 translate-middle-x text-center mt-4 pb-2 w-50 opacity-100" style="z-index: 100;" role="alert">
                <h5 class="alert-heading"><?php echo _("How to sign?") ?></h5>
                <strong><?php echo _("By clicking directly on the document page"); ?></strong> <?php echo _("to insert the selected item in the right column"); ?> <small>(<?php echo _("signature, initial, text, stamp, etc ..."); ?>)</small>
                <div class="mt-1 fs-3"><i class="bi bi-box-arrow-down"></i></div>
                <button type="button" class="btn-close btn-sm" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>
    <div style="height: 65px;" class="d-md-none"></div>
    <div id="container-pages" class="col-12 pt-1 pb-1 text-center vh-100" dir="auto">
    </div>
    <div style="height: 55px;" class="d-md-none"></div>
    <div class="offcanvas offcanvas-end show d-none d-md-block shadow-sm" data-bs-backdrop="false" data-bs-scroll="true" data-bs-keyboard="false" tabindex="-1" id="sidebarTools" aria-labelledby="sidebarToolsLabel">
        <a class="btn btn-close btn-sm position-absolute opacity-25 d-none d-sm-none d-md-block" title="<?php echo _("Close this PDF and return to the homepage"); ?>" style="position: absolute; top: 2px; right: 2px; font-size: 10px;" href="<?php echo $REVERSE_PROXY_URL; ?>/signature"></a>
        <div class="offcanvas-header mb-0 pb-0">
            <h5 class="mb-1 d-block w-100" id="sidebarToolsLabel"><?php echo _("PDF Signature"); ?> <?php if(isset($hash)): ?><span class="float-end small me-2" title="<?php echo _("This PDF is shared with others to be signed by multiple people"); ?>"><span class="nblayers"></span> <i class="bi bi-people-fill"></i></span><?php else: ?><span class="float-end me-2" title="<?php echo _("This PDF is stored on your computer to be signed by you only"); ?>"><i class="bi bi-person-workspace"></i></span><?php endif; ?></h5>
            <button type="button" class="btn-close text-reset d-md-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-0">
            <p id="text_document_name" class="text-muted" style="text-overflow: ellipsis; white-space: nowrap; overflow: hidden;" title=""><?php if (isset($isPdfEncrypted) && $isPdfEncrypted): ?><i class="bi bi-lock-fill" title="<?php echo _("This PDF is stored encrypted on the server."); ?>"></i><?php endif;?><i class="bi bi-files"></i> <span></span></p>
            <div class="form-check form-switch mb-2 small d-none">
                <input class="form-check-input" type="checkbox" id="add-lock-checkbox" disabled="disabled">
                <label style="cursor: pointer;" class="form-check-label" for="add-lock-checkbox"> <?php echo _("Keep the selection active"); ?></label>
            </div>
            <div id="svg_list_signature" class="list-item-add"></div>
            <div class="d-grid gap-2 mb-2 list-item-add">
                <input type="radio" class="btn-check" id="radio_svg_signature_add" name="svg_2_add" autocomplete="off" value="signature">
                <label data-bs-toggle="modal" data-bs-target="#modalAddSvg" data-type="signature" class="btn btn-outline-secondary text-black text-start btn-add-svg-type" for="radio_svg_signature_add" id="label_svg_signature_add"><?php echo sprintf(_("%s Signature"), '<i class="bi bi-vector-pen"></i>'); ?> <small class="text-muted float-end"><?php echo _("Create"); ?></small></label>
            </div>
            <div id="svg_list_initials" class="list-item-add"></div>
            <div class="d-grid gap-2 mb-2 list-item-add">
                <input type="radio" class="btn-check" id="radio_svg_initials_add" name="svg_2_add" autocomplete="off" value="intials">
                <label data-bs-toggle="modal" data-bs-target="#modalAddSvg" data-type="initials" data-modalnav="#nav-type-tab" class="btn btn-outline-secondary text-black text-start btn-add-svg-type" for="radio_svg_initials_add" id="label_svg_initials_add"><?php echo sprintf(_("%s Initial"), '<i class="bi bi-type"></i>'); ?> <small class="text-muted float-end"><?php echo _("Create"); ?></small></label>
            </div>
            <div id="svg_list_rubber_stamber" class="list-item-add"></div>
            <div class="d-grid gap-2 mb-2 list-item-add">
                <input type="radio" class="btn-check" id="radio_svg_rubber_stamber_add" name="svg_2_add" autocomplete="off" value="rubber_stamber">
                <label data-bs-toggle="modal" data-bs-target="#modalAddSvg" data-type="rubber_stamber" data-modalnav="#nav-import-tab" class="btn btn-outline-secondary text-black text-start btn-add-svg-type" for="radio_svg_rubber_stamber_add" id="label_svg_rubber_stamber_add"><?php echo sprintf(_("%s Stamp"), '<i class="bi bi-card-text"></i>'); ?> <small class="text-muted float-end"><?php echo _("Create"); ?></small></label>
            </div>
            <div class="d-grid gap-2 mb-2 list-item-add">
                  <input type="radio" class="btn-check" id="radio_svg_text" data-svg="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgZmlsbD0iY3VycmVudENvbG9yIiBjbGFzcz0iYmkgYmktdGV4dGFyZWEtdCIgdmlld0JveD0iMCAwIDE2IDE2Ij48cGF0aCBkPSJNMS41IDIuNUExLjUgMS41IDAgMCAxIDMgMWgxMGExLjUgMS41IDAgMCAxIDEuNSAxLjV2My41NjNhMiAyIDAgMCAxIDAgMy44NzRWMTMuNUExLjUgMS41IDAgMCAxIDEzIDE1SDNhMS41IDEuNSAwIDAgMS0xLjUtMS41VjkuOTM3YTIgMiAwIDAgMSAwLTMuODc0VjIuNXptMSAzLjU2M2EyIDIgMCAwIDEgMCAzLjg3NFYxMy41YS41LjUgMCAwIDAgLjUuNWgxMGEuNS41IDAgMCAwIC41LS41VjkuOTM3YTIgMiAwIDAgMSAwLTMuODc0VjIuNUEuNS41IDAgMCAwIDEzIDJIM2EuNS41IDAgMCAwLS41LjV2My41NjN6TTIgN2ExIDEgMCAxIDAgMCAyIDEgMSAwIDAgMCAwLTJ6bTEyIDBhMSAxIDAgMSAwIDAgMiAxIDEgMCAwIDAgMC0yeiIvPjxwYXRoIGQ9Ik0xMS40MzQgNEg0LjU2Nkw0LjUgNS45OTRoLjM4NmMuMjEtMS4yNTIuNjEyLTEuNDQ2IDIuMTczLTEuNDk1bC4zNDMtLjAxMXY2LjM0M2MwIC41MzctLjExNi42NjUtMS4wNDkuNzQ4VjEyaDMuMjk0di0uNDIxYy0uOTM4LS4wODMtMS4wNTQtLjIxLTEuMDU0LS43NDhWNC40ODhsLjM0OC4wMWMxLjU2LjA1IDEuOTYzLjI0NCAyLjE3MyAxLjQ5NmguMzg2TDExLjQzNCA0eiIvPjwvc3ZnPgo=" name="svg_2_add" autocomplete="off" value="text">
                  <label draggable="true" id="label_svg_text" class="btn btn-outline-secondary text-black text-start btn-svg" for="radio_svg_text"><?php echo sprintf(_("%s Text"), '<i class="bi bi-textarea-t"></i>'); ?></label>
              </div>
              <div class="d-grid gap-2 mb-2 list-item-add">
                  <input type="radio" class="btn-check" id="radio_svg_strikethrough" data-svg="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgZmlsbD0iY3VycmVudENvbG9yIiBjbGFzcz0iYmkgYmktdHlwZS1zdHJpa2V0aHJvdWdoIiB2aWV3Qm94PSIwIDAgMTYgMTYiPgogIDxwYXRoIGQ9Ik02LjMzMyA1LjY4NmMwIC4zMS4wODMuNTgxLjI3LjgxNEg1LjE2NmEyLjggMi44IDAgMCAxLS4wOTktLjc2YzAtMS42MjcgMS40MzYtMi43NjggMy40OC0yLjc2OCAxLjk2OSAwIDMuMzkgMS4xNzUgMy40NDUgMi44NWgtMS4yM2MtLjExLTEuMDgtLjk2NC0xLjc0My0yLjI1LTEuNzQzLTEuMjMgMC0yLjE4LjYwMi0yLjE4IDEuNjA3em0yLjE5NCA3LjQ3OGMtMi4xNTMgMC0zLjU4OS0xLjEwNy0zLjcwNS0yLjgxaDEuMjNjLjE0NCAxLjA2IDEuMTI5IDEuNzAzIDIuNTQ0IDEuNzAzIDEuMzQgMCAyLjMxLS43MDUgMi4zMS0xLjY3NSAwLS44MjctLjU0Ny0xLjM3NC0xLjkxNC0xLjY3NUw4LjA0NiA4LjVIMXYtMWgxNHYxaC0zLjUwNGMuNDY4LjQzNy42NzUuOTk0LjY3NSAxLjY5NyAwIDEuODI2LTEuNDM2IDIuOTY3LTMuNjQ0IDIuOTY3Ij48L3BhdGg+Cjwvc3ZnPgo=" name="svg_2_add" autocomplete="off" value="strikethrough">
                  <label draggable="true" id="label_svg_strikethrough" class="btn btn-outline-secondary text-black text-start btn-svg" for="radio_svg_strikethrough"><i class="bi bi-type-strikethrough"></i> <?php echo _("Strikethrough"); ?></label>
              </div>
              <div class="d-grid gap-2 mb-2 list-item-add">
                  <input type="radio" class="btn-check" id="radio_svg_check" data-height="18" name="svg_2_add" autocomplete="off" value="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgZmlsbD0iY3VycmVudENvbG9yIiBjbGFzcz0iYmkgYmktY2hlY2stbGciIHZpZXdCb3g9IjAgMCAxNiAxNiI+CiAgPHBhdGggZD0iTTEyLjczNiAzLjk3YS43MzMuNzMzIDAgMCAxIDEuMDQ3IDBjLjI4Ni4yODkuMjkuNzU2LjAxIDEuMDVMNy44OCAxMi4wMWEuNzMzLjczMyAwIDAgMS0xLjA2NS4wMkwzLjIxNyA4LjM4NGEuNzU3Ljc1NyAwIDAgMSAwLTEuMDYuNzMzLjczMyAwIDAgMSAxLjA0NyAwbDMuMDUyIDMuMDkzIDUuNC02LjQyNWEuMjQ3LjI0NyAwIDAgMSAuMDItLjAyMloiLz4KPC9zdmc+Cg==">
                  <label draggable="true" id="label_svg_check" class="btn btn-outline-secondary text-black text-start btn-svg" for="radio_svg_check"><?php echo sprintf(_("%s Check box"), '<i class="bi bi-check-square"></i>'); ?></label>
              </div>
              <div id="svg_list" class="d-grid gap-2 mt-2 mb-2 list-item-add"></div>

              <div class="d-grid gap-2 mt-2">
                  <button type="button" id="btn-add-svg" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#modalAddSvg"><?php echo sprintf(_("%s Create an element"), '<i class="bi bi-plus-circle"></i>'); ?></button>
              </div>
              <div id="form_block" class="position-absolute bottom-0 pb-2 ps-0 pe-4 w-100">
                  <?php if(!isset($hash)): ?>
                  <?php if(!isset($noSharingMode)): ?>
                          <button class="btn btn-outline-dark w-100" type="button" data-bs-toggle="modal" data-bs-target="#modal-start-share"><?php echo sprintf(_("%s Share to sign %s with multiple people"), '<i class="bi bi-share"></i>',"<i class='bi bi-people-fill'></i>"); ?></button>
                  <?php endif; ?>
                  <form id="form_pdf" action="<?php echo $REVERSE_PROXY_URL; ?>/sign" method="post" enctype="multipart/form-data" class="d-none d-sm-none d-md-block">
                        <input id="input_pdf" name="pdf" type="file" class="d-none" />
                        <input id="input_svg" name="svg[]" type="file" class="d-none" />
                        <button class="btn btn-primary w-100 mt-2" disabled="disabled" type="submit" id="save"><i class="bi bi-download"></i> <?php echo _("Download the signed PDF"); ?></button>
                  </form>
                <?php elseif(!isset($noSharingMode)): ?>
                  <div class="d-none d-sm-none d-md-block position-relative">
                      <a id="btn-signature-help" class="position-absolute top-0 end-0 text-dark" href="" style="z-index: 5;"><i class="bi bi-question-circle"></i></a>
                      <p id="nblayers_text" class="small d-none mb-2 opacity-75"><?php echo sprintf(_("You are %s to have signed this PDF"), "<span class='badge rounded-pill border border-dark text-dark'><span class='nblayers'>0</span> <i class='bi bi-people-fill'></i></span>"); ?></p>
                  </div>
                  <div class="btn-group w-100">
                      <a id="btn_download" class="btn btn-outline-dark w-100" href="<?php echo $REVERSE_PROXY_URL; ?>/signature/<?php echo $hash ?>/pdf"><?php echo sprintf(_("%s Download the PDF"), '<i class="bi bi-download"></i>'); ?></a>
                      <button class="btn btn-outline-dark" type="button" id="btn_share" data-bs-toggle="modal" data-bs-target="#modal-share-informations"><i class="bi bi-share"></i></button>
                  </div>
                  <form id="form_pdf" action="<?php echo $REVERSE_PROXY_URL; ?>/signature/<?php echo $hash ?>/save" method="post" enctype="multipart/form-data" class="d-none d-sm-none d-md-block">
                        <input id="input_svg" name="svg[]" type="file" class="d-none" />

                        <button class="btn btn-primary w-100 mt-2" disabled="disabled" type="submit" id="save"><i class="bi bi-cloud-upload"></i> <?php echo _("Transmit my signature"); ?></button>
                  </form>
                  <?php endif; ?>
              </div>
            </div>
        </div>
        <div class="position-fixed top-0 start-0 bg-white w-100 p-2 shadow-sm d-md-none">
            <div class="d-grid gap-2">
            <button id="btn_svn_select" class="btn btn-light btn-lg" data-bs-toggle="offcanvas" data-bs-target="#sidebarTools" aria-controls="sidebarTools"><?php echo sprintf(_("%s Select a signature"), '<i class="bi bi-hand-index"></i>'); ?></button>
            </div>
            <div id="svg_selected_container" class="text-center d-none position-relative">
                <img id="svg_selected" src="" style="height: 48px;" class="img-fluid"/>
                <button type="button" id="btn_svg_selected_close" class="btn-close text-reset position-absolute" style="top: 9px; right: 9px;"></button>
            </div>
            <div id="svg_object_actions" class="d-none">
                <button id="btn-svg-pdf-delete" class="btn btn-lg btn-light"><i class="bi bi-trash"></i></button>
            </div>
        </div>
        <div class="position-fixed bottom-0 start-0 bg-white w-100 p-2 shadow d-md-none">
            <div class="btn-group position-absolute opacity-25" style="top: -46px;">
            <button id="btn-zoom-decrease" class="btn btn-secondary"><i class="bi bi-dash"></i></button>
            <button id="btn-zoom-increase" class="btn btn-secondary"><i class="bi bi-plus"></i></button>
            </div>
            <div class="d-grid gap-2">
                <?php if(isset($hash)): ?>
                    <button class="btn btn-primary" disabled="disabled" type="submit" id="save_mobile"><i class="bi bi-cloud-upload"></i> <?php echo _("Transmit my signature"); ?></button>
                <?php else: ?>
                    <button class="btn btn-primary" disabled="disabled" type="submit" id="save_mobile"><i class="bi bi-download"></i> <?php echo _("Download the signed PDF"); ?></button>
                <?php endif; ?>
            </div>
        </div>

    <div class="modal fade" id="modalAddSvg" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <nav class="nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link active ps-2 ps-md-3 pe-2 pe-md-3" id="nav-draw-tab" data-bs-toggle="tab" data-bs-target="#nav-draw" type="button" role="tab" aria-controls="nav-draw" aria-selected="true"><i class="bi bi-vector-pen"></i> <?php echo _("Draw"); ?><br /><small><?php echo _("freehand"); ?></small></button>
                    <button class="nav-link ps-2 ps-md-3 pe-2 pe-md-3" id="nav-type-tab" data-bs-toggle="tab" data-bs-target="#nav-type" type="button" role="tab" aria-controls="nav-type" aria-selected="false"><i class="bi bi-fonts"></i> <?php echo _("Enter"); ?><br /><small><?php echo _("text"); ?></small></button>
                    <button class="nav-link ps-2 ps-md-3 pe-2 pe-md-3" id="nav-import-tab" data-bs-toggle="tab" data-bs-target="#nav-import" type="button" role="tab" aria-controls="nav-import" aria-selected="false"><i class="bi bi-image"></i> <?php echo _("Import"); ?><br /><small><?php echo _("an image"); ?></small></button>
                </nav>
                <div class="tab-content mt-3" id="nav-svg-add">
                    <div class="tab-pane fade show active" id="nav-draw" role="tabpanel" aria-labelledby="nav-draw-tab">
                      <small id="signature-pad-reset" class="text-muted opacity-75 position-absolute" style="right: 25px; bottom: 25px; cursor: pointer;" title="<?php echo _("Clear signature"); ?>"><i class="bi bi-trash"></i></small>
                      <canvas id="signature-pad" class="border bg-light" width="462" height="200"></canvas>
                    </div>
                    <div class="tab-pane fade" id="nav-type" role="tabpanel" aria-labelledby="nav-type-tab">
                        <input id="input-text-signature" type="text" class="form-control form-control-lg" placeholder="<?php echo _("My signature"); ?>" />
                    </div>
                    <div class="tab-pane fade" id="nav-import" role="tabpanel" aria-labelledby="nav-import-tab">
                        <div class="text-center">
                        <img id="img-upload" class="d-none" src="" />
                        </div>
                        <form id="form-image-upload" action="<?php echo $REVERSE_PROXY_URL; ?>/image2svg" method="POST" enctype="multipart/form-data">
                        <input id="input-image-upload" class="form-control" name="image" type="file">
                        </form>
                    </div>
                </div>
                <input id="input-svg-type" type="hidden" />
          </div>
          <div class="modal-footer d-block">
            <button tabindex="-1" type="button" class="btn btn-light col-4" data-bs-dismiss="modal"><?php echo _("Cancel"); ?></button>
            <button id="btn_modal_ajouter" type="button" disabled="disabled" data-bs-dismiss="modal" class="btn btn-primary float-end col-4"><span id="btn_modal_ajouter_spinner" class="spinner-border spinner-border-sm d-none"></span><span id="btn_modal_ajouter_check" class="bi bi-check-circle"></span> <?php echo _("Create"); ?></button>
          </div>
        </div>
      </div>
    </div>
    </div>
    <?php if(!isset($hash) && !isset($noSharingMode)): ?>
    <div id="modal-start-share" class="modal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo sprintf(_("%s Share this PDF to sign it with several people"), '<i class="bi bi-share"></i>'); ?> </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?php echo _("By enabling PDF sharing, you will be able to provide a link to the people of your choice so that they can sign this PDF."); ?></p>
                    <p><?php echo sprintf(_("%s This sharing requires the PDF to be transferred and stored on the server for future signers to access."), '<i class="bi bi-hdd-network"></i>'); ?></p>
                    <p><?php echo sprintf(_("%s The PDF will be kept"), '<i class="bi bi-hourglass-split"></i>'); ?> <select name='duration' form='form_sharing'><option value='+1 year'><?php echo _("for one year"); ?></option><option value='+6 month'><?php echo _("for six months"); ?></option><option value='+1 month' selected='selected'><?php echo _("for one month"); ?></option><option value='+1 week'><?php echo _("for one week"); ?></option><option value='+1 day'><?php echo _("for one day"); ?></option><option value='+1 hour'><?php echo _("for one hour"); ?></option></select> <?php echo _("after the last signature."); ?></p>
                    <?php if ($PDF_STORAGE_ENCRYPTION): ?>
                        <p><i class="bi bi-lock-fill"></i> <input type="checkbox" id="checkbox_encryption" name="encryption" value="true" checked="checked" form='form_sharing'/>
                                <label for="checkbox_encryption"><?php echo _("The PDF will be stored encrypted on the server"); ?></label>
                            </p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer text-center d-block">
                    <form id="form_sharing" clas action="<?php echo $REVERSE_PROXY_URL; ?>/share" method="post" enctype="multipart/form-data">
                          <input id="input_pdf_share" name="pdf" type="file" class="d-none" />
                          <input id="input_svg_share" name="svg[]" type="file" class="d-none" />
                          <input id="input_pdf_hash" name="hash" type="hidden" value="" />
                          <button  class="btn col-9 col-md-6 btn-primary" type="submit" id="save_share"><?php echo sprintf(_("%s Start sharing"), '<i class="bi bi-cloud-upload"></i>'); ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php if(isset($hash)): ?>
    <div id="modal-share-informations" class="modal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo sprintf(_("%s Sign this PDF with multiple people"), '<i class="bi bi-people-fill"></i>'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?php echo _("Multiple people can sign this PDF simultaneously."); ?></p>
                    <p><?php echo _("To do so, simply share the link to this page with the people of your choice:"); ?></p>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><?php echo _("Sharing link"); ?></span>
                        <input id="input-share-link" type="text" onclick="this.select();  this.setSelectionRange(0, 99999);" readonly="readonly" class="form-control bg-light font-monospace" value="">
                        <button onclick="navigator.clipboard.writeText(document.getElementById('input-share-link').value); this.innerText = '<?php echo _('Copied !'); ?>';" autofocus="autofocus" class="btn btn-primary" type="button" id="btn-copy-share-link"><i class="bi bi-clipboard"></i> <?php echo _('Copy'); ?></button>
                        <script>document.querySelector('#input-share-link').value = document.location.href;</script>
                    </div>
                    <p class="mb-0"><?php echo _("Each of the signatories can download the latest version of the signed PDF at any time."); ?></p>
                </div>
                <div class="modal-footer text-start">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?php echo _("Close"); ?></button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php if(isset($hash)): ?>
    <div id="modal-signed" class="modal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-check"></i> <?php echo _("Signed PDF"); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1"><i class="bi bi-check-circle text-success"></i> <?php echo _("Your signature has been successfully recorded!"); ?></p>
                </div>
                <div class="modal-footer text-center d-block">
                    <a class="btn btn-outline-dark" href="<?php echo $REVERSE_PROXY_URL; ?>/signature/<?php echo $hash ?>/pdf"><i class="bi bi-download"></i> <?php echo _("Download the PDF"); ?></a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <span id="is_mobile" class="d-md-none"></span>

    <script src="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap.bundle.min.js?5.3.3"></script>
    <script src="<?php echo $REVERSE_PROXY_URL; ?>/vendor/pdf.mjs?4.6.82-legacy" type="module"></script>
    <script type="module">
        pdfjsLib.GlobalWorkerOptions.workerSrc = '<?php echo $REVERSE_PROXY_URL; ?>/vendor/pdf.worker.mjs?4.6.82-legacy';
    </script>
    <script src="<?php echo $REVERSE_PROXY_URL; ?>/vendor/fabric.min.js?5.4.0"></script>
    <script src="<?php echo $REVERSE_PROXY_URL; ?>/vendor/signature_pad.umd.min.js?5.0.3"></script>
    <script src="<?php echo $REVERSE_PROXY_URL; ?>/vendor/opentype.min.js?1.3.4"></script>
    <script>
    var maxSize = <?php echo $maxSize ?>;
    var maxPage = <?php echo $maxPage ?>;
    var sharingMode = <?php echo intval(!isset($noSharingMode)) ?>;
    var pdfHash = null;
    var openModal = null;
    <?php if(Flash::instance()->hasKey('openModal')): ?>
        openModal = "<?php echo Flash::instance()->getKey("openModal"); ?>";
    <?php endif; ?>
    var direction = '<?php echo $DIRECTION_LANGUAGE ?>';
    <?php if(isset($hash)): ?>
    pdfHash = "<?php echo $hash ?>";
    <?php endif; ?>

    var trad = <?php echo json_encode([
        'Text to modify' => _('Text to modify')
    ]); ?>;

    <?php if ($TRANSLATION_LANGUAGE == 'ar'): ?>
    url_font = <?php echo json_encode('/vendor/fonts/Tajawal-Medium.ttf') ?>
    <?php elseif ($TRANSLATION_LANGUAGE == 'kab'): ?>
    url_font = <?php echo json_encode('/vendor/fonts/FiraSans-MediumItalic.ttf') ?>
    <?php else: ?>
    url_font = <?php echo json_encode('/vendor/fonts/Caveat-Regular.ttf') ?>
    <?php endif; ?>
    </script>
    <script src="<?php echo $REVERSE_PROXY_URL; ?>/js/common.js?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/js/common.js") ?>"></script>
    <script src="<?php echo $REVERSE_PROXY_URL; ?>/js/signature.js?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/js/signature.js") ?>"></script>
  </body>
</html>
