<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap.<?php echo $DIRECTION_LANGUAGE ?>.min.css?5.3.3" rel="stylesheet">
<link href="<?php echo $REVERSE_PROXY_URL; ?>/vendor/bootstrap-icons.min.css?1.11.3" rel="stylesheet">
<link href="<?php echo $REVERSE_PROXY_URL; ?>/css/app.css?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/css/app.css") ?>" rel="stylesheet">
<?php if (file_exists($ROOT."/public/css/app-specific.css")): ?>
<link href="<?php echo $REVERSE_PROXY_URL; ?>/css/app-specific.css?<?php echo ($COMMIT) ? $COMMIT : filemtime($ROOT."/public/css/app-specific.css") ?>" rel="stylesheet">
<?php endif; ?>
<link rel="icon" type="image/x-icon" href="<?php echo $REVERSE_PROXY_URL; ?>/favicon.ico">
<link rel="icon" type="image/png" sizes="192x192" href="<?php echo $REVERSE_PROXY_URL; ?>/favicon.png" />
<style>
    @font-face {
        font-family: 'Caveat';
        font-style: normal;
        font-weight: 400;
        src: url('<?php echo $REVERSE_PROXY_URL; ?>/vendor/fonts/Caveat-Regular.ttf') format('truetype');
    }

    @font-face {
        font-family: 'Tajawal';
        font-style: normal;
        font-weight: 400;
        src: url('<?php echo $REVERSE_PROXY_URL; ?>/vendor/fonts/Tajawal-Medium.ttf') format('truetype');
    }
</style>
