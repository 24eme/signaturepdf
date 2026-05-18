<div class="p-2 mb-3">
    <nav class=" rounded pb-2 mx-auto position-relative">
        <a class="link-dark" href="<?php echo $REVERSE_PROXY_URL; ?>/"><img src="logo-small.svg" alt="Logo de signature pdf" style="height: 42px; position: absolute;top: 6px;left: 8px;" />
        <span class="d-none d-lg-inline" style="font-size: 15px; position: absolute;top: 10px;left: 56px;">Signature PDF</span>
        <span class="opacity-50 d-none d-lg-inline" style="font-size: 12px; position: absolute;top: 30px;left: 56px;"><?php echo _("Sign and manipulate PDFs freely"); ?></span></a>
        <div class="dropdown position-absolute top-0 end-0 mt-2 me-2">
            <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                <i class='bi bi-translate'></i><span class="d-none d-lg-inline"> <?php echo _("Language"); ?></span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                <?php foreach ($SUPPORTED_LANGUAGES as $key => $langue): ?>
                    <li><a class="dropdown-item" href="?lang=<?php echo $key; ?>"><?php echo $langue; ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <ul class="nav nav-pills justify-content-center pt-2">
            <?php if (!$disableOrganization): ?>
            <li class="nav-item d-none d-sm-block">
                <a class="nav-link <?php if ($activeTab === "index"): ?>active<?php endif; ?>" href="<?php echo $REVERSE_PROXY_URL; ?>/"><i class="bi bi-search"></i><span class="d-none d-md-inline-block"></span></a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link <?php if ($activeTab === "sign"): ?>active<?php endif; ?>" href="<?php echo $REVERSE_PROXY_URL; ?>/signature"><i class="bi bi-vector-pen"></i> <span class="d-none d-md-inline-block"><?php echo _("Sign"); ?></span></a>
            </li>
            <?php if (!$disableOrganization): ?>
            <li class="nav-item">
                <a class="nav-link <?php if ($activeTab === "organize"): ?>active<?php endif; ?>" href="<?php echo $REVERSE_PROXY_URL; ?>/organization"><i class="bi bi-ui-checks-grid"></i> <span class="d-none d-md-inline-block"><?php echo _("Organize",); ?></span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php if ($activeTab === "metadata"): ?>active<?php endif; ?>" href="<?php echo $REVERSE_PROXY_URL; ?>/metadata"><i class="bi bi-tags"></i> <span class="d-none d-md-inline-block"><?php echo _("Metadata"); ?></span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php if ($activeTab === "compress"): ?>active<?php endif; ?>"  href="<?php echo $REVERSE_PROXY_URL; ?>/compress"><i class="bi bi-chevron-bar-contract"></i> <span class="d-none d-md-inline-block"><?php echo _("Compress"); ?></span></a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
