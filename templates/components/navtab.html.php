<nav>
<?php if(!$disableOrganization): ?>
    <?php if ($IS_ADMIN): ?>
        <div class="position-absolute top-0 start-0 mt-2 ms-2">
            <a title="Button visible just for ip authorized" class="btn btn-outline-danger opacity-25 btn-sm <?php if($activeTab === 'admin'): ?>active<?php endif; ?>" href="<?php echo $REVERSE_PROXY_URL; ?>/administration"><i class="bi bi-wrench-adjustable"></i> <span class="d-none d-sm-inline-block"><?php echo _("Administration panel"); ?></span></a>
        </div>
    <?php endif; ?>
    <div class="dropdown position-absolute top-0 end-0 mt-2 me-2">
        <button class="btn btn-outline-secondary btn-sm  dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="d-none d-md-inline"><i class='bi bi-translate'></i> <?php echo _("Language"); ?></span>
            <span class="d-md-none"><i class="bi bi-translate"></i></span>
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
            <?php foreach ($SUPPORTED_LANGUAGES as $key => $langue):?>
                <li><a class="dropdown-item" href="?lang=<?php echo $key ?>"><?php echo $langue ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <ul class="nav justify-content-center nav-tabs mt-2">
        <li class="nav-item">
            <a class="nav-link <?php if($activeTab === 'sign'): ?>active<?php endif; ?>" href="<?php echo $REVERSE_PROXY_URL; ?>/signature"><i class="bi bi-vector-pen"></i> <span class="d-none d-sm-inline-block"><?php echo _("Sign"); ?></span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php if($activeTab === 'organize'): ?>active<?php endif; ?>" href="<?php echo $REVERSE_PROXY_URL; ?>/organization"><i class="bi bi-ui-checks-grid"></i> <span class="d-none d-sm-inline-block"><?php echo _("Organize"); ?></span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php if($activeTab === 'metadata'): ?>active<?php endif; ?>" href="<?php echo $REVERSE_PROXY_URL; ?>/metadata"><i class="bi bi-tags"></i> <span class="d-none d-sm-inline-block"><?php echo _("Metadata"); ?></span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php if($activeTab === 'compress'): ?>active<?php endif; ?>"  href="<?php echo $REVERSE_PROXY_URL; ?>/compress"><i class="bi bi-chevron-bar-contract"></i> <span class="d-none d-sm-inline-block"><?php echo _("Compress"); ?></span></a>
        </li>
    </ul>
<?php endif; ?>
</nav>
