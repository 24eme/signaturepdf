<?php

setlocale(LC_ALL, "");

require(__DIR__.'/lib/GPGCryptography.class.php');
require(__DIR__.'/lib/NSSCryptography.class.php');
require(__DIR__.'/lib/PDFSignature.class.php');
require(__DIR__.'/lib/Image2SVG.class.php');
require(__DIR__.'/lib/Compression.class.php');
require(__DIR__.'/lib/OCR.class.php');
require(__DIR__.'/lib/MainController.class.php');
require(__DIR__.'/lib/ApiController.class.php');

$f3 = require(__DIR__.'/vendor/fatfree/base.php');


$f3->set('FALLBACK', null);
$f3->language(isset($f3->get('HEADERS')['Accept-Language']) ? $f3->get('HEADERS')['Accept-Language'] : '');

session_start();

if(getenv("DEBUG")) {
    $f3->set('DEBUG', getenv("DEBUG"));
}

$f3->set('SUPPORTED_LANGUAGES',
    [
        'ar' => 'العربية',
        'de' => 'Deutsch',
        'en' => 'English',
        'es' => 'Español',
        'eu' => 'Euskara',
        'fr' => 'Français',
        'it' => 'Italiano',
        'gl' => 'Galego',
        'kab' => 'Taqbaylit',
        'nl'  => 'Nederlands',
        'oc' => 'Occitan',
        'pl' => 'Polski',
        'ro' => 'Română',
        'ta' => 'தமிழ்',
        'tr' => 'Türkçe'
    ]);

$f3->set('XFRAME', null); // Allow use in an iframe
$f3->set('ROOT', __DIR__);
$f3->set('UI', $f3->get('ROOT')."/templates/");
$f3->set('UPLOADS', sys_get_temp_dir()."/");
$f3->set('COMMIT', getCommit());

$f3->config(__DIR__.'/config/config.ini');
if (!$f3->exists('REVERSE_PROXY_URL')) {
    $f3->set('REVERSE_PROXY_URL', '');
}

if($f3->get('PDF_STORAGE_PATH') && !preg_match('|/$|', $f3->get('PDF_STORAGE_PATH'))) {
    $f3->set('PDF_STORAGE_PATH', $f3->get('PDF_STORAGE_PATH').'/');
}

$f3->set('disableOrganization', false);
if($f3->get('DISABLE_ORGANIZATION')) {
    $f3->set('disableOrganization', $f3->get('DISABLE_ORGANIZATION'));
}

$f3->set('ADMIN_AUTHORIZED_IP', array_merge(["localhost", "127.0.0.1", "::1"], explode(' ', $f3->get('ADMIN_AUTHORIZED_IP') . '')));
$f3->set('IS_ADMIN', in_array(@$_SERVER["REMOTE_ADDR"], $f3->get('ADMIN_AUTHORIZED_IP')));



if ($f3->get('GET.lang')) {
    selectLanguage($f3->get('GET.lang'), $f3, true);
} elseif (isset($_COOKIE['LANGUAGE'])) {
    selectLanguage($_COOKIE['LANGUAGE'], $f3, true);
} else {
    selectLanguage($f3->get('LANGUAGE'), $f3);
}

if (!$f3->exists('PDF_STORAGE_ENCRYPTION')) {
    $f3->set('PDF_STORAGE_ENCRYPTION', false);
}

if($f3->get('PDF_STORAGE_ENCRYPTION') && !GPGCryptography::isGpgInstalled()) {
    $f3->set('PDF_STORAGE_ENCRYPTION', false);
}

if ($f3->exists('NSS3_DIRECTORY') && $f3->exists('NSS3_PASSWORD') && $f3->exists('NSS3_NICK')) {
    NSSCryptography::getInstance($f3->get('NSS3_DIRECTORY'), $f3->get('NSS3_PASSWORD'), $f3->get('NSS3_NICK'));
}


$domain = basename(glob($f3->get('ROOT')."/locale/application_*.pot")[0], '.pot');

bindtextdomain($domain, $f3->get('ROOT')."/locale");
textdomain($domain);

$f3->set('TRANSLATION_LANGUAGE', _("en"));
$f3->set('DIRECTION_LANGUAGE', 'ltr');
if($f3->get('TRANSLATION_LANGUAGE') == "ar") {
    $f3->set('DIRECTION_LANGUAGE', 'rtl');
}

if($f3->get('PDF_DEMO_LINK') === null || $f3->get('PDF_DEMO_LINK') === true) {
    if ($f3->get('TRANSLATION_LANGUAGE') == "ar") {
        $f3->set('PDF_DEMO_LINK', 'https://raw.githubusercontent.com/24eme/signaturepdf/master/tests/files/document_ar.pdf');
    } else {
        $f3->set('PDF_DEMO_LINK', 'https://raw.githubusercontent.com/24eme/signaturepdf/master/tests/files/document.pdf');
    }
}

$f3->route('GET|HEAD @index: /', 'MainController->index');
$f3->route('GET @signature: /signature', 'MainController->signature');
$f3->route('GET @signature_hash: /signature/@hash', 'MainController->signatureHash');
$f3->route('POST @image2svg: /image2svg', 'MainController->image2svg');
$f3->route('POST @sign: /sign', 'MainController->sign');
$f3->route('POST @share: /share', 'MainController->share');
$f3->route('GET @signature_deletion: /signature/@hash/delete/@key', 'MainController->signatureDeletion');
$f3->route('GET @signature_pdf: /signature/@hash/pdf', 'MainController->signaturePdf');
$f3->route('POST @signature_save: /signature/@hash/save', 'MainController->signatureSave');
$f3->route('GET @signature_nblayers: /signature/@hash/nblayers', 'MainController->signatureNblayers');
$f3->route('GET @cron: /cron', 'MainController->cron');
$f3->route('GET @organization: /organization', 'MainController->organization');
$f3->route('GET @metadata: /metadata', 'MainController->metadata');
$f3->route('POST @ocr: /ocr', 'MainController->ocr');
$f3->route('GET @administration: /administration', 'MainController->administration');
$f3->route('GET @compression: /compress', 'MainController->compression');
$f3->route('POST @compress: /compress', 'MainController->compress');

$f3->route('GET @api_file_get: /api/file/get', 'ApiController->fileGet');
$f3->route('GET @api_file_save: /api/file/get', 'ApiController->fileSave');
$f3->route('POST @api_share_new: /api/share/new', 'ApiController->shareNew');
$f3->route('GET @api_share_get: /api/share/get/@hash/@symmkey', 'ApiController->shareGet');
$f3->route('GET @api_share_delete: /api/share/delete/@hash/@adminkey', 'ApiController->shareDelete');

function getApiLocalFilePath($f3) {
    $localRootFolder = $f3->get('PDF_LOCAL_PATH');
    if (!$localRootFolder) {
        $f3->error(403);
        return false;
    }
    $pdf_path = $localRootFolder . '/' . $f3->get('GET.path');
    if (strpos($pdf_path, '..') !== false) {
        $f3->error(403);
        return false;
    }
    if (strpos(realpath($pdf_path), realpath($localRootFolder)) === false) {
        $f3->error(403);
        return false;
    }
    if (!file_exists($pdf_path)) {
        $f3->error(403);
        return false;
    }
    return $pdf_path;
}

function getCommit() {
    if(!file_exists(__DIR__.'/.git/HEAD')) {

        return null;
    }

    $head = str_replace(["ref: ", "\n"], "", file_get_contents(__DIR__.'/.git/HEAD'));
    $commit = null;

    if(strpos($head, "refs/") !== 0) {
        $commit = $head;
    }

    if(file_exists(__DIR__.'/.git/'.$head)) {
        $commit = str_replace("\n", "", file_get_contents(__DIR__.'/.git/'.$head));
    }

    return substr($commit, 0, 7);
}

function selectLanguage($lang, $f3, $putCookie = false) {
    $langSupported = null;
    foreach(explode(',', $lang) as $l) {
        if(array_key_exists($l, $f3->get('SUPPORTED_LANGUAGES'))) {
            $langSupported = $l;
            break;
        }
    }
    if(!$langSupported) {
        return null;
    }
    if($putCookie) {
        $cookieDate = strtotime('+1 year');
        setcookie("LANGUAGE", $langSupported, ['expires' => $cookieDate, 'samesite' => 'Strict', 'path' => "/"]);
    }
    putenv("LANGUAGE=$langSupported");
}

function convertPHPSizeToBytes($sSize)
{
    $sSuffix = strtoupper(substr($sSize, -1));
    if (!in_array($sSuffix,array('P','T','G','M','K'))){
        return (int)$sSize;
    }
    $iValue = substr($sSize, 0, -1);
    switch ($sSuffix) {
        case 'P': $iValue *= 1000;
        case 'T': $iValue *= 1000;
        case 'G': $iValue *= 1000;
        case 'M': $iValue *= 1000;
        case 'K': $iValue *= 1000; break;
    }
    return (int)$iValue;
}

return $f3;
