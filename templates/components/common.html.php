<div class="modal" id="modalLoading" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body text-center my-5 text-white fs-4">
                <p></p>
                <div class="spinner-border" role="status"></div>
            </div>
        </div>
    </div>
</div>
<span id="is_mobile" class="d-md-none"></span>
<script src="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap.bundle.min.js?5.3.3"></script>
<?php if(isset($loadJs['pdf.js'])): ?>
<script src="<?php echo $REVERSE_PROXY_URL; ?>/vendor/pdf.min.mjs?4.6.82-legacy" type="module"></script>
<script type="module">
    pdfjsLib.GlobalWorkerOptions.workerSrc = '<?php echo $REVERSE_PROXY_URL; ?>/vendor/pdf.worker.min.mjs?4.6.82-legacy';
</script>
<?php endif; ?>
<?php if(isset($loadJs['pdf-lib.js'])): ?>
<script src="<?php echo $REVERSE_PROXY_URL; ?>/vendor/pdf-lib.min.js?2.2.4"></script>
<?php endif; ?>
<script src="<?php echo $REVERSE_PROXY_URL; ?>/js/common.js?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/js/common.js") ?>"></script>
<script>
var trad = <?php echo json_encode([
    'Select this page' => _('Select this page'),
    'Delete this page' => _('Delete this page'),
    'Restore this page' => _('Restore this page'),
    'Move this page' => _('Move this page'),
    'Move here' => _('Move here'),
    'Turn this page' => _('Turn this page'),
    'Download this page' => _('Download this page'),
    'Page' => _('Page')
]); ?>;
</script>
