<!doctype html>
<html lang="fr">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Logiciel libre de signature de PDF en ligne">
    <link href="/vendor/bootstrap.min.css?5.1.1" rel="stylesheet">
    <link href="/vendor/bootstrap-icons.css?1.8.1" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
    <title>Signature PDF</title>
  </head>
  <body>
    <noscript>
        <div class="alert alert-danger text-center" role="alert">
          <i class="bi bi-exclamation-triangle"></i> Site non fonctionnel sans JavaScript activé
        </div>
    </noscript>
    <div id="page-upload">
        <?php if(!$disableOrganization): ?>
        <ul class="nav justify-content-center nav-tabs mt-2">
          <li class="nav-item">
            <a class="nav-link active" href="/signature"><i class="bi bi-vector-pen"></i> Signer</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/organization"><i class="bi bi-ui-checks-grid"></i> Organiser</a>
          </li>
        </ul>
        <?php endif; ?>
        <div class="px-4 py-4 text-center">
            <h1 class="display-5 fw-bold mb-0 mt-3"><i class="bi bi-vector-pen"></i> Signer un PDF</h1>
            <p class="fw-light mb-3 subtitle text-dark text-nowrap" style="overflow: hidden; text-overflow: ellipsis;">Signer, parapher, tamponner, compléter un document</p>
            <div class="col-md-6 col-lg-5 col-xl-4 col-xxl-3 mx-auto">
                <div class="col-12">
                  <label class="form-label mt-3" for="input_pdf_upload">Choisir un PDF <small class="opacity-75" style="cursor: help" title="Le PDF ne doit pas dépasser <?php echo round($maxSize / 1024 / 1024) ?> Mo et <?php echo $maxPage ?> pages"><i class="bi bi-info-circle"></i></small></label>
                  <input id="input_pdf_upload" placeholder="Choisir un PDF" class="form-control form-control-lg" type="file" accept=".pdf,application/pdf" />
                  <p class="mt-2 small fw-light text-dark">Le PDF sera traité par le serveur sans être conservé ni stocké</p>
                  <?php if($PDF_DEMO_LINK): ?>
                  <a class="btn btn-sm btn-link opacity-75" href="#<?php echo $PDF_DEMO_LINK ?>">Tester avec un PDF de démo</a>
                  <?php endif; ?>
                </div>
            </div>
        </div>
        <footer class="text-center text-muted mb-2 fixed-bottom opacity-75">
            <small>Logiciel libre <span class="d-none d-md-inline">sous license AGPL-3.0 </span>: <a href="https://github.com/24eme/signaturepdf">voir le code source</a></small>
        </footer>
    </div>
    <div id="page-signature" style="padding-right: 350px;" class="d-none">
        <div style="height: 65px;" class="d-md-none"></div>
        <div id="container-pages" class="col-12 pt-1 pb-1 text-center vh-100">
        </div>
        <div style="height: 55px;" class="d-md-none"></div>
        <div class="offcanvas offcanvas-end show d-none d-md-block shadow-sm" data-bs-backdrop="false" data-bs-scroll="true" data-bs-keyboard="false" tabindex="-1" id="sidebarTools" aria-labelledby="sidebarToolsLabel">
            <a class="btn btn-close btn-sm position-absolute opacity-25 d-none d-sm-none d-md-block" title="Fermer ce PDF et retourner à l'accueil" style="position: absolute; top: 2px; right: 2px; font-size: 10px;" href="/signature"></a>
            <div class="offcanvas-header mb-0 pb-0">
                <h5 class="mb-1 d-block w-100" id="sidebarToolsLabel">Signature du PDF <?php if(isset($hash)): ?><span class="float-end small me-2" title="Ce PDF est partagé avec d'autres personnes pour être signé à plusieurs"><span class="nblayers"></span> <i class="bi bi-people-fill"></i></span><?php else: ?><span class="float-end me-2" title="Ce PDF est stocké sur votre ordinateur pour être signé par vous uniquement"><i class="bi bi-person-workspace"></i></span><?php endif; ?></h5>
                <button type="button" class="btn-close text-reset d-md-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body pt-0">
                <p id="text_document_name" class="text-muted" style="text-overflow: ellipsis; white-space: nowrap; overflow: hidden;" title=""><i class="bi bi-files"></i> <span></span></p>
                <div class="form-check form-switch mb-2 small d-none">
                  <input class="form-check-input" type="checkbox" id="add-lock-checkbox" disabled="disabled">
                  <label style="cursor: pointer;" class="form-check-label" for="add-lock-checkbox"> Garder la séléction active</label>
                </div>
              <div id="svg_list_signature" class="list-item-add"></div>
              <div class="d-grid gap-2 mb-2 list-item-add">
                  <input type="radio" class="btn-check" id="radio_svg_signature_add" name="svg_2_add" autocomplete="off" value="signature">
                  <label data-bs-toggle="modal" data-bs-target="#modalAddSvg" data-type="signature" class="btn btn-outline-secondary text-black text-start btn-add-svg-type" for="radio_svg_signature_add" id="label_svg_signature_add"><i class="bi bi-vector-pen"></i> Signature <small class="text-muted float-end">Créer</small></label>
              </div>
              <div id="svg_list_initials" class="list-item-add"></div>
              <div class="d-grid gap-2 mb-2 list-item-add">
                  <input type="radio" class="btn-check" id="radio_svg_initials_add" name="svg_2_add" autocomplete="off" value="intials">
                  <label data-bs-toggle="modal" data-bs-target="#modalAddSvg" data-type="initials" data-modalnav="#nav-type-tab" class="btn btn-outline-secondary text-black text-start btn-add-svg-type" for="radio_svg_initials_add" id="label_svg_initials_add"><i class="bi bi-type"></i> Paraphe <small class="text-muted float-end">Créer</small></label>
              </div>
              <div id="svg_list_rubber_stamber" class="list-item-add"></div>
              <div class="d-grid gap-2 mb-2 list-item-add">
                  <input type="radio" class="btn-check" id="radio_svg_rubber_stamber_add" name="svg_2_add" autocomplete="off" value="rubber_stamber">
                  <label data-bs-toggle="modal" data-bs-target="#modalAddSvg" data-type="rubber_stamber" data-modalnav="#nav-import-tab" class="btn btn-outline-secondary text-black text-start btn-add-svg-type" for="radio_svg_rubber_stamber_add" id="label_svg_rubber_stamber_add"><i class="bi bi-card-text"></i> Tampon <small class="text-muted float-end">Créer</small></label>
              </div>
              <div class="d-grid gap-2 mb-2 list-item-add">
                  <input type="radio" class="btn-check" id="radio_svg_text" data-svg="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgZmlsbD0iY3VycmVudENvbG9yIiBjbGFzcz0iYmkgYmktdGV4dGFyZWEtdCIgdmlld0JveD0iMCAwIDE2IDE2Ij48cGF0aCBkPSJNMS41IDIuNUExLjUgMS41IDAgMCAxIDMgMWgxMGExLjUgMS41IDAgMCAxIDEuNSAxLjV2My41NjNhMiAyIDAgMCAxIDAgMy44NzRWMTMuNUExLjUgMS41IDAgMCAxIDEzIDE1SDNhMS41IDEuNSAwIDAgMS0xLjUtMS41VjkuOTM3YTIgMiAwIDAgMSAwLTMuODc0VjIuNXptMSAzLjU2M2EyIDIgMCAwIDEgMCAzLjg3NFYxMy41YS41LjUgMCAwIDAgLjUuNWgxMGEuNS41IDAgMCAwIC41LS41VjkuOTM3YTIgMiAwIDAgMSAwLTMuODc0VjIuNUEuNS41IDAgMCAwIDEzIDJIM2EuNS41IDAgMCAwLS41LjV2My41NjN6TTIgN2ExIDEgMCAxIDAgMCAyIDEgMSAwIDAgMCAwLTJ6bTEyIDBhMSAxIDAgMSAwIDAgMiAxIDEgMCAwIDAgMC0yeiIvPjxwYXRoIGQ9Ik0xMS40MzQgNEg0LjU2Nkw0LjUgNS45OTRoLjM4NmMuMjEtMS4yNTIuNjEyLTEuNDQ2IDIuMTczLTEuNDk1bC4zNDMtLjAxMXY2LjM0M2MwIC41MzctLjExNi42NjUtMS4wNDkuNzQ4VjEyaDMuMjk0di0uNDIxYy0uOTM4LS4wODMtMS4wNTQtLjIxLTEuMDU0LS43NDhWNC40ODhsLjM0OC4wMWMxLjU2LjA1IDEuOTYzLjI0NCAyLjE3MyAxLjQ5NmguMzg2TDExLjQzNCA0eiIvPjwvc3ZnPgo=" name="svg_2_add" autocomplete="off" value="text">
                  <label draggable="true" id="label_svg_text" class="btn btn-outline-secondary text-black text-start btn-svg" for="radio_svg_text"><i class="bi bi-textarea-t"></i> Texte</label>
              </div>
              <div class="d-grid gap-2 mb-2 list-item-add">
                  <input type="radio" class="btn-check" id="radio_svg_check" data-height="18" name="svg_2_add" autocomplete="off" value="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgZmlsbD0iY3VycmVudENvbG9yIiBjbGFzcz0iYmkgYmktY2hlY2stbGciIHZpZXdCb3g9IjAgMCAxNiAxNiI+CiAgPHBhdGggZD0iTTEyLjczNiAzLjk3YS43MzMuNzMzIDAgMCAxIDEuMDQ3IDBjLjI4Ni4yODkuMjkuNzU2LjAxIDEuMDVMNy44OCAxMi4wMWEuNzMzLjczMyAwIDAgMS0xLjA2NS4wMkwzLjIxNyA4LjM4NGEuNzU3Ljc1NyAwIDAgMSAwLTEuMDYuNzMzLjczMyAwIDAgMSAxLjA0NyAwbDMuMDUyIDMuMDkzIDUuNC02LjQyNWEuMjQ3LjI0NyAwIDAgMSAuMDItLjAyMloiLz4KPC9zdmc+Cg==">
                  <label draggable="true" id="label_svg_check" class="btn btn-outline-secondary text-black text-start btn-svg" for="radio_svg_check"><i class="bi bi-check-square"></i> Case à cocher</label>
              </div>
              <div id="svg_list" class="d-grid gap-2 mt-2 mb-2 list-item-add"></div>

              <div class="d-grid gap-2 mt-2">
                  <button type="button" id="btn-add-svg" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#modalAddSvg"><i class="bi bi-plus-circle"></i> Créer un élément</button>
              </div>
              <div id="form_block" class="position-absolute bottom-0 pb-2 ps-0 pe-4 w-100">
                  <?php if(!isset($hash)): ?>
                  <?php if(!isset($noSharingMode)): ?>
                    <button class="btn btn-outline-dark w-100" type="button" data-bs-toggle="modal" data-bs-target="#modal-start-share"><i class="bi bi-share"></i> Partager pour signer <i class="bi bi-people-fill"></i> à plusieurs</button>
                  <?php endif; ?>
                  <form id="form_pdf" action="/sign" method="post" enctype="multipart/form-data" class="d-none d-sm-none d-md-block">
                        <input id="input_pdf" name="pdf" type="file" class="d-none" />
                        <input id="input_svg" name="svg[]" type="file" class="d-none" />
                        <button class="btn btn-primary w-100 mt-2" disabled="disabled" type="submit" id="save"><i class="bi bi-download"></i> Télécharger le PDF signé</button>
                  </form>
                <?php elseif(!isset($noSharingMode)): ?>
                  <div class="d-none d-sm-none d-md-block">
                  <p id="nblayers_text" class="small d-none mb-2 opacity-75">Vous êtes <span class="badge rounded-pill border border-dark text-dark"><span class="nblayers">0</span> <i class="bi bi-people-fill"></i></span> à avoir signé ce PDF</p></div>
                  <div class="btn-group w-100">
                      <a id="btn_download" class="btn btn-outline-dark w-100" href="/signature/<?php echo $hash ?>/pdf"><i class="bi bi-download"></i> Télécharger le PDF</a>
                      <button class="btn btn-outline-dark" type="button" id="btn_share" data-bs-toggle="modal" data-bs-target="#modal-share-informations"><i class="bi bi-share"></i></button>
                  </div>
                  <form id="form_pdf" action="/signature/<?php echo $hash ?>/save" method="post" enctype="multipart/form-data" class="d-none d-sm-none d-md-block">
                        <input id="input_svg" name="svg[]" type="file" class="d-none" />
                        <button class="btn btn-primary w-100 mt-2" disabled="disabled" type="submit" id="save"><i class="bi bi-cloud-upload"></i> Transmettre ma signature</button>
                  </form>
                  <?php endif; ?>
              </div>
            </div>
        </div>
        <div class="position-fixed top-0 start-0 bg-white w-100 p-2 shadow-sm d-md-none">
            <div class="d-grid gap-2">
            <button id="btn_svn_select" class="btn btn-light btn-lg" data-bs-toggle="offcanvas" data-bs-target="#sidebarTools" aria-controls="sidebarTools"><i class="bi bi-hand-index"></i> Séléctionner une signature</button>
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
                    <button class="btn btn-primary" disabled="disabled" type="submit" id="save_mobile"><i class="bi bi-cloud-upload"></i> Transmettre ma signature</button>
                <?php else: ?>
                    <button class="btn btn-primary" disabled="disabled" type="submit" id="save_mobile"><i class="bi bi-download"></i> Télécharger le PDF signé</button>
                <?php endif; ?>
            </div>
        </div>

    <div class="modal fade" id="modalAddSvg" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <nav class="nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link active ps-2 ps-md-3 pe-2 pe-md-3" id="nav-draw-tab" data-bs-toggle="tab" data-bs-target="#nav-draw" type="button" role="tab" aria-controls="nav-draw" aria-selected="true"><i class="bi bi-vector-pen"></i> Dessiner<br /><small>à main levée</small></button>
                    <button class="nav-link ps-2 ps-md-3 pe-2 pe-md-3" id="nav-type-tab" data-bs-toggle="tab" data-bs-target="#nav-type" type="button" role="tab" aria-controls="nav-type" aria-selected="false"><i class="bi bi-fonts"></i> Saisir<br /><small>du texte</small></button>
                    <button class="nav-link ps-2 ps-md-3 pe-2 pe-md-3" id="nav-import-tab" data-bs-toggle="tab" data-bs-target="#nav-import" type="button" role="tab" aria-controls="nav-import" aria-selected="false"><i class="bi bi-image"></i> Importer<br /><small>une image</small></button>
                </nav>
                <div class="tab-content mt-3" id="nav-svg-add">
                    <div class="tab-pane fade show active" id="nav-draw" role="tabpanel" aria-labelledby="nav-draw-tab">
                      <small id="signature-pad-reset" class="text-muted opacity-75 position-absolute" style="right: 25px; bottom: 25px; cursor: pointer;" title="Effacer la signature"><i class="bi bi-trash"></i></small>
                      <canvas id="signature-pad" class="border bg-light" width="462" height="200"></canvas>
                    </div>
                    <div class="tab-pane fade" id="nav-type" role="tabpanel" aria-labelledby="nav-type-tab">
                        <input id="input-text-signature" type="text" class="form-control form-control-lg" placeholder="Ma signature" />
                    </div>
                    <div class="tab-pane fade" id="nav-import" role="tabpanel" aria-labelledby="nav-import-tab">
                        <div class="text-center">
                        <img id="img-upload" class="d-none" src="" />
                        </div>
                        <form id="form-image-upload" action="/image2svg" method="POST" enctype="multipart/form-data">
                        <input id="input-image-upload" class="form-control" name="image" type="file">
                        </form>
                    </div>
                </div>
                <input id="input-svg-type" type="hidden" />
          </div>
          <div class="modal-footer d-block">
            <button tabindex="-1" type="button" class="btn btn-light col-4" data-bs-dismiss="modal">Annuler</button>
            <button id="btn_modal_ajouter" type="button" disabled="disabled" data-bs-dismiss="modal" class="btn btn-primary float-end col-4"><span id="btn_modal_ajouter_spinner" class="spinner-border spinner-border-sm d-none"></span><span id="btn_modal_ajouter_check" class="bi bi-check-circle"></span> Créer</button>
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
                    <h5 class="modal-title"><i class="bi bi-share"></i> Partager ce PDF pour le signer à plusieurs </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>En activant le partage de ce PDF vous allez pouvoir proposer un lien aux personnes de votre choix pour qu'elles puissent signer ce PDF.</p>
                    <p><i class="bi bi-hdd-network"></i> Ce partage nécessite que le PDF soit transféré et stocké sur le serveur afin d'être accessible aux futurs signataires.</p>
                    <p class="mb-0"><i class="bi bi-hourglass-split"></i> Le PDF sera conservé <select name="duration" form="form_sharing"><option value="+1 year">un an</option><option value="+6 month">six mois</option><option value="+1 month" selected="selected">un mois</option><option value="+1 week">une semaine</option><option value="+1 day">un jour</option><option value="+1 hour">une heure</option></select> après la dernière signature.</p>
                </div>
                <div class="modal-footer text-center d-block">
                    <form id="form_sharing" clas action="/share" method="post" enctype="multipart/form-data">
                          <input id="input_pdf_share" name="pdf" type="file" class="d-none" />
                          <input id="input_svg_share" name="svg[]" type="file" class="d-none" />
                          <button  class="btn col-9 col-md-6 btn-primary" type="submit" id="save_share"><i class="bi bi-cloud-upload"></i> Démarrer le partage</button>
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
                    <h5 class="modal-title"><i class="bi bi-people-fill"></i> Signer ce PDF à plusieurs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Plusieurs personnes peuvent signer ce PDF en même temps.</p>
                    <p>Pour cela il vous suffit de partager avec les personnes de votre choix le lien vers cette page :</p>
                    <div class="input-group mb-3">
                        <span class="input-group-text">Lien à partager</span>
                        <input id="input-share-link" type="text" onclick="this.select();  this.setSelectionRange(0, 99999);" readonly="readonly" class="form-control bg-light font-monospace" value="">
                        <button onclick="navigator.clipboard.writeText(document.getElementById('input-share-link').value); this.innerText = 'Copié !';" autofocus="autofocus" class="btn btn-primary" type="button" id="btn-copy-share-link"><i class="bi bi-clipboard"></i> Copier</button>
                        <script>document.querySelector('#input-share-link').value = document.location.href.replace(/#.*/, '');</script>
                    </div>
                    <p class="mb-0">Chacun des signataires pourra à tout moment télécharger la dernière version du PDF signé.</p>
                </div>
                <div class="modal-footer text-start">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
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
                    <h5 class="modal-title"><i class="bi bi-file-earmark-check"></i> PDF signé</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1"><i class="bi bi-check-circle text-success"></i> Votre signature a bien été prise en compte&nbsp;!</p>
                </div>
                <div class="modal-footer text-center d-block">
                    <a class="btn btn-outline-dark" href="/signature/<?php echo $hash ?>/pdf"><i class="bi bi-download"></i> Télécharger le PDF</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <span id="is_mobile" class="d-md-none"></span>

    <script src="/vendor/bootstrap.min.js?5.1.3"></script>
    <script src="/vendor/pdf.js?legacy"></script>
    <script src="/vendor/fabric.min.js?4.6.0"></script>
    <script src="/vendor/signature_pad.umd.min.js?3.0.0-beta.3"></script>
    <script src="/vendor/opentype.min.js?1.3.3"></script>
    <script>
    var maxSize = <?php echo $maxSize ?>;
    var maxPage = <?php echo $maxPage ?>;
    var sharingMode = <?php echo intval(!isset($noSharingMode)) ?>;
    var hash = null;
    <?php if(isset($hash)): ?>
    hash = "<?php echo $hash ?>";
    <?php endif; ?>
    </script>
    <script src="/js/signature.js?202204270035"></script>
  </body>
</html>
