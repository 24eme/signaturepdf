<footer class="text-center text-muted mb-2 mt-3 opacity-75 small">
    <a href="https://github.com/24eme/signaturepdf?tab=readme-ov-file#signature-pdf-libre">Signature PDF</a> - <?php echo _("Free open-source software"); ?> <span class="d-none d-md-inline"><?php echo _("under AGPL-3.0 license"); ?> : <a href="https://github.com/24eme/signaturepdf"><?php echo _("see the source code"); ?></a><?php if($COMMIT): ?> [<a href="https://github.com/24eme/signaturepdf/tree/<?php echo $COMMIT ?>"><?php echo $COMMIT ?></a>]</span><?php endif; ?>
    <?php if ($IS_ADMIN): ?>
        - <a title="Button visible just for ip authorized" class="small <?php if($activeTab === 'admin'): ?>active<?php endif; ?>" href="<?php echo $REVERSE_PROXY_URL; ?>/administration"><i class="bi bi-wrench-adjustable"></i> <?php echo _("Administration panel"); ?></a>
    <?php endif; ?>
</footer>
