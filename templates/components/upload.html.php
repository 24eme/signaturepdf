<div id="upload_drag_zone" class="block-drag mt-5 col-md-8 col-lg-6 col-xl-5 col-xxl-4 mx-auto border rounded-2 <?php if ($PDF_DEMO_LINK && !isset($uploadNoDemo)): ?>rounded-bottom-0<?php endif; ?> border-dark-subtle p-4 shadow-sm position-relative" style="border-style: dashed !important; cursor: pointer;">
    <?php if(isset($uploadHelp)): ?>
    <small class="opacity-75 position-absolute" style="cursor: help; right: 8px; top: 5px;" title="<?php echo $uploadHelp ?>"><i class="bi bi-question-circle"></i></small>
    <?php endif; ?>
    <label class="form-label" for="input_pdf_upload"><?php echo _("Choose a PDF"); ?><?php if(isset($uploadImgAuhtorized)): ?><small class="opacity-50 d-block"><?php echo _("or an image"); ?></small><?php else: ?><small class="d-block">&nbsp;</small><?php endif; ?></label>
    <div><i class="bi bi-upload fs-2"></i></div>
    <input id="input_pdf_upload" name="input_pdf_upload" placeholder="<?php echo _(  "Choose a PDF"); ?>" class="form-control d-none mt-3" type="file" accept=".pdf,application/pdf<?php if(isset($uploadImgAuhtorized)): ?>image/png,image/jpeg<?php endif; ?>" <?php if(isset($uploadMultiple) && $uploadMultiple): ?>multiple="true"<?php endif; ?> />
</div>
<?php if ($PDF_DEMO_LINK && !isset($uploadNoDemo)): ?>
    <a href="#<?php echo $PDF_DEMO_LINK; ?>" class="block-drag d-block col-md-8 col-lg-6 col-xl-5 col-xxl-4 mx-auto border rounded-2 rounded-top-0 border-top-0 border-dark-subtle p-2 shadow-sm position-relative small text-secondary" style="border-style: dashed !important; cursor: pointer;">
    <?php echo _("Test with a demo PDF"); ?>
</a>
<?php endif; ?>
<?php if(isset($uploadOffline)): ?>
<p class="mt-3 mb-3 small opacity-50"><i class="bi bi-wifi-off"></i> <?php echo _("Your file never leave your device"); ?></p>
<?php else: ?>
<p class="mt-3 mb-3 small opacity-50"><i class="bi bi-wifi"></i> <?php echo _("The PDF will be processed by the server without being retained or stored"); ?></p>
<?php endif; ?>

<script>
    document.querySelector('#upload_drag_zone').addEventListener('click', function(e) {
        this.querySelector('input[type=file]').click();
    });
    document.querySelector('#upload_drag_zone input[type=file]').addEventListener('change', function(e) {
        document.querySelector('#upload_drag_zone .bi-upload').classList.add('d-none');
        //document.querySelector('#upload_drag_zone').classList.add('active');
        this.classList.remove('d-none')
    });
    /*document.querySelector('#upload_drag_zone').addEventListener('drop', function(e) {
        const files = [...e.dataTransfer.items]
          .map((item) => item.getAsFile())
          .filter((file) => file);
        console.log(files);
        let dataTransfer = new DataTransfer();
        for(file of files) {
            dataTransfer.items.add(file);
        }
        this.querySelector('input[type=file]').files = dataTransfer.files;
        this.querySelector('input[type=file]').dispatchEvent(new Event("change"));
    });
    document.querySelector('#upload_drag_zone').addEventListener("dragover", (e) => {
        this.classList.add('drag-enter');
      const fileItems = [...e.dataTransfer.items].filter(
        (item) => item.kind === "file",
      );
      if (fileItems.length > 0) {
        e.preventDefault();
        e.dataTransfer.dropEffect = "copy";
      }
    });

    window.addEventListener("drop", (e) => {
      if ([...e.dataTransfer.items].some((item) => item.kind === "file")) {
        e.preventDefault();
      }
  });*/
</script>
