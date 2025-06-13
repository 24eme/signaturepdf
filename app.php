<?php

setlocale(LC_ALL, "");
require(__DIR__.'/lib/GPGCryptography.class.php');
require(__DIR__.'/lib/NSSCryptography.class.php');
require(__DIR__.'/lib/PDFSignature.class.php');
require(__DIR__.'/lib/Image2SVG.class.php');
require(__DIR__.'/lib/Compression.class.php');

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

$f3->route('GET /',
    function($f3) {
        $f3->set('activeTab', 'index');
        echo View::instance()->render('index.html.php');
    }
);
$f3->route('GET /signature',
    function($f3) {
        $f3->set('maxSize',  min(array(convertPHPSizeToBytes(ini_get('post_max_size')), convertPHPSizeToBytes(ini_get('upload_max_filesize')))));
        $f3->set('maxPage',  ini_get('max_file_uploads') - 1);

        if(!$f3->get('PDF_STORAGE_PATH')) {
            $f3->set('noSharingMode', true);
        }

        if ($f3->exists('signature')) {
            $retentions = [];
            foreach ($f3->get('signature.retention') ?? [] as $key => $text) {
                $retentions[$key] = $text;
            }
            $f3->set('signatureRetention', $retentions);
        }

        $f3->set('activeTab', 'sign');

        echo View::instance()->render('signature.html.php');
    }
);

$f3->route('GET /signature/@hash',
    function($f3) {
        $f3->set('hash', Web::instance()->slug($f3->get('PARAMS.hash')));
        $f3->set('maxSize',  min(array(convertPHPSizeToBytes(ini_get('post_max_size')), convertPHPSizeToBytes(ini_get('upload_max_filesize')))));
        $f3->set('maxPage',  ini_get('max_file_uploads') - 1);

        if(!is_dir($f3->get('PDF_STORAGE_PATH').$f3->get('hash'))) {
            $f3->error(404);
        }
        $f3->set('isPdfEncrypted', GPGCryptography::isPathEncrypted($f3->get('PDF_STORAGE_PATH').$f3->get('hash')));
        $f3->set('activeTab', 'sign');
        echo View::instance()->render('signature.html.php');
    }
);

$f3->route('POST /image2svg',
    function($f3) {
        $files = Web::instance()->receive(function($file,$formFieldName){
            if(strpos(Web::instance()->mime($file['tmp_name'], true), 'image/') !== 0) {

                return false;
            }

            return true;
        }, true, function($fileBaseName, $formFieldName) use ($f3) {

            return basename(tempnam($f3->get('UPLOADS'), 'pdfsignature_image2svg'));
	    });

        $imageFile = null;
        foreach($files as $file => $valid) {
            if(!$valid) {
                continue;
            }
            $imageFile = $file;
        }

        if(!$imageFile) {
            $f3->error(403);
        }

        shell_exec(sprintf("convert -background white -flatten %s %s", $imageFile, $imageFile.".bmp"));
        shell_exec(sprintf("mkbitmap -x -f 8 %s -o %s", $imageFile.".bmp", $imageFile.".bpm"));
        shell_exec(sprintf("potrace --svg %s -o %s", $imageFile.".bpm", $imageFile.".svg"));

        header('Content-Type: image/svg+xml');
        echo file_get_contents($imageFile.".svg");

        if($f3->get('DEBUG')) {
            return;
        }

        array_map('unlink', glob($imageFile."*"));
    }
);
$f3->route('POST /sign',
    function($f3) {
        $filename = null;
        $filigrane = $f3->get('POST.watermark');
        $tmpfile = tempnam($f3->get('UPLOADS'), 'pdfsignature_sign_'.uniqid("", true));
        unlink($tmpfile);
        $svgFiles = [];

        $files = Web::instance()->receive(function($file,$formFieldName){
            if($formFieldName == "pdf" && strpos(Web::instance()->mime($file['tmp_name'], true), 'application/pdf') !== 0) {
                $f3->error(403);
            }

            if($formFieldName == "svg" && strpos(Web::instance()->mime($file['tmp_name'], true), 'image/svg+xml') !== 0) {
                $f3->error(403);
            }

            return true;
        }, false, function($fileBaseName, $formFieldName) use ($f3, $tmpfile, &$filename, &$svgFiles) {
            if($formFieldName == "pdf") {
                $filename = str_replace(".pdf", "_signe.pdf", $fileBaseName);
                return basename($tmpfile).".pdf";
            }

            if($formFieldName == "svg") {
                $svgFiles[] = $tmpfile."_".$fileBaseName;

                return basename($tmpfile."_".$fileBaseName);
            }
	    });

        if(!is_file($tmpfile.".pdf")) {
            $f3->error(403);
        }

        if(!count($svgFiles)) {
            $f3->error(403);
        }

        PDFSignature::createPDFFromSvg($svgFiles, $tmpfile.'.svg.pdf');
        PDFSignature::addSvgToPDF($tmpfile.'.pdf', $tmpfile.'.svg.pdf', $tmpfile.'_signe.pdf');
        if ($filigrane) {
            PDFSignature::addFiligrane($filigrane, $tmpfile);
        }

        Web::instance()->send($tmpfile.'_signe.pdf', null, 0, TRUE, $filename);

        if($f3->get('DEBUG')) {
            return;
        }
        array_map('unlink', glob($tmpfile."*"));
    }
);

$f3->route('POST /share',
    function($f3) {
        if (!is_dir($f3->get('PDF_STORAGE_PATH'))) {
            $f3->error(500, 'Sharing folder doesn\'t exist');
        }
        if (!is_writable($f3->get('PDF_STORAGE_PATH'))) {
            $f3->error(500, 'Sharing folder is not writable');
        }

        $hash = Web::instance()->slug($_POST['hash']);
        $symmetricKey = (isset($_COOKIE[$hash])) ? GPGCryptography::protectSymmetricKey($_COOKIE[$hash]) : null;

        $tmpfile = tempnam($f3->get('UPLOADS'), 'pdfsignature_share_'.uniqid($hash, true));
        unlink($tmpfile);

        $svgFiles = [];
        $originalFile = $tmpfile."_original.pdf";
        $originalFileBaseName = null;
        $files = Web::instance()->receive(function($file,$formFieldName){
            if($formFieldName == "pdf" && strpos(Web::instance()->mime($file['tmp_name'], true), 'application/pdf') !== 0) {
                $f3->error(403);
            }
            if($formFieldName == "svg" && strpos(Web::instance()->mime($file['tmp_name'], true), 'image/svg+xml') !== 0) {
                $f3->error(403);
            }

            return true;
        }, false, function($fileBaseName, $formFieldName) use ($tmpfile, $originalFile, &$svgFiles, &$originalFileBaseName) {
                if($formFieldName == "pdf") {
                    $originalFileBaseName = $fileBaseName;
                    return basename($originalFile);
                }
                if($formFieldName == "svg") {
                    $svgFiles[] = $tmpfile."_".$fileBaseName;
                    return basename($tmpfile."_".$fileBaseName);
                }
	    });

        if(!count($files)) {
            $f3->error(403);
        }

        $pdfSignature = new PDFSignature($f3->get('PDF_STORAGE_PATH').$hash, $symmetricKey);
        $pdfSignature->createShare($originalFile, $originalFileBaseName, $f3->get('POST.duration'));
        if(count($svgFiles)) {
            $pdfSignature->addSignature($svgFiles);
        }

        if(!$f3->get('DEBUG')) {
            $pdfSignature->clean();
        }

        \Flash::instance()->setKey('openModal', 'shareinformations');

        $f3->reroute($f3->get('REVERSE_PROXY_URL').'/signature/'.$hash.(($symmetricKey) ? '#'.$symmetricKey : null));
    }

);

$f3->route('GET /signature/@hash/pdf',
    function($f3) {
        $f3->set('activeTab', 'sign');
        $hash = Web::instance()->slug($f3->get('PARAMS.hash'));
        $symmetricKey = (isset($_COOKIE[$hash])) ? GPGCryptography::protectSymmetricKey($_COOKIE[$hash]) : null;
        $pdfSignature = new PDFSignature($f3->get('PDF_STORAGE_PATH').$hash, $symmetricKey);
        if(!$pdfSignature->verifyEncryption()) {
            $f3->error(403, 'Unable to decrypt pdf because of wrong symmetric key');
        }

        Web::instance()->send($pdfSignature->getPDF(), null, 0, TRUE, $pdfSignature->getPublicFilename());

        if($f3->get('DEBUG')) {
            return;
        }

        $pdfSignature->clean();
    }
);

$f3->route('POST /signature/@hash/save',
    function($f3) {
        $hash = Web::instance()->slug($f3->get('PARAMS.hash'));
        $symmetricKey = (isset($_COOKIE[$hash])) ? GPGCryptography::protectSymmetricKey($_COOKIE[$hash]) : null;
        $pdfSignature = new PDFSignature($f3->get('PDF_STORAGE_PATH').$hash, $symmetricKey);
        if(!$pdfSignature->verifyEncryption()) {
            $f3->error(403, 'Unable to decrypt pdf because of wrong symmetric key');
        }

        $tmpfile = tempnam($f3->get('UPLOADS'), 'pdfsignature_save_'.uniqid($hash, true));
        unlink($tmpfile);
        $svgFiles = [];
        $files = Web::instance()->receive(function($file,$formFieldName){
            if($formFieldName == "svg" && strpos(Web::instance()->mime($file['tmp_name'], true), 'image/svg+xml') !== 0) {
                $f3->error(403);
            }
            return true;
        }, false, function($fileBaseName, $formFieldName) use ($f3, $tmpfile, &$svgFiles) {
            if($formFieldName == "svg") {
                $svgFiles[] = $tmpfile."_".$fileBaseName;
                return basename($tmpfile."_".$fileBaseName);
            }
	    });
        if(!count($svgFiles)) {
            $f3->error(403);
        }

        $pdfSignature->addSignature($svgFiles);

        if(!$f3->get('DEBUG')) {
            $pdfSignature->clean();
        }

        \Flash::instance()->setKey('openModal', 'signed');

        $f3->reroute($f3->get('REVERSE_PROXY_URL').'/signature/'.$hash.(($symmetricKey) ? '#'.$symmetricKey : null));
    }
);

$f3->route('GET /signature/@hash/nblayers',
    function($f3) {
        $f3->set('activeTab', 'sign');
        $hash = Web::instance()->slug($f3->get('PARAMS.hash'));
        $symmetricKey = (isset($_COOKIE[$hash])) ? GPGCryptography::protectSymmetricKey($_COOKIE[$hash]) : null;
        $pdfSignature = new PDFSignature($f3->get('PDF_STORAGE_PATH').$hash, $symmetricKey);
        echo count($pdfSignature->getLayers());
    }
);

$f3->route('GET /cron', function($f3) {
    $sharingFolder = $f3->get('PDF_STORAGE_PATH');
    foreach(glob($sharingFolder.'*.expire') as $expireFile) {
        if(filemtime($expireFile) > time()) {
            continue;
        }
        $expiredFolder = str_replace('.expire', '', $expireFile);
        array_map('unlink', glob($expiredFolder."/*"));
        if(file_exists($expiredFolder.'/.lock')) {
            unlink($expiredFolder.'/.lock');
        }
        rmdir($expiredFolder);
        unlink($expireFile);
    }
});

if (!$f3->get('disableOrganization')) {
$f3->route('GET /organization',
    function($f3) {
        $f3->set('activeTab', 'organize');
        echo View::instance()->render('organization.html.php');
    }
);
}
$f3->route('GET /metadata',
    function($f3) {
        $f3->set('activeTab','metadata');
        echo View::instance()->render('metadata.html.php');
    }
);

$f3->route ('GET /administration',
    function ($f3) {
        if (! $f3->get('IS_ADMIN')) {
            die('You ('.$_SERVER['REMOTE_ADDR'].') are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
        }
        $f3->set('activeTab','admin');
        echo View::instance()->render('admin_setup.html.php');
});

$f3->route('GET /compress',
    function($f3) {
        $f3->set('maxSize',  min(array(convertPHPSizeToBytes(ini_get('post_max_size')), convertPHPSizeToBytes(ini_get('upload_max_filesize')))));
        $f3->set('activeTab', 'compress');
        echo View::instance()->render('compress.html.php');
    }
);

$f3->route ('POST /compress',
    function($f3) {
        $filename = null;
        $tmpfile = tempnam($f3->get('UPLOADS'), 'pdfsignature_compress_'.uniqid("", true));
        unlink($tmpfile);

        $files = Web::instance()->receive(function($file,$formFieldName) {
            if ($formFieldName == "pdf" && strpos(Web::instance()->mime($file['tmp_name'], true), 'application/pdf') !== 0) {
                $f3->error(403);
            }
        });

        $compressionType = $f3->get('POST.compressionType');
        if ($compressionType === 'medium') {
            $compressionType = '/ebook';
        } elseif ($compressionType === 'low') {
            $compressionType = '/printer';
        } else {
            $compressionType = '/screen';
        }
        $filePath = reset(array_keys($files));

        $outputFileName = str_replace(".pdf", "_compressed.pdf", $filePath);

        $returnCode = shell_exec(sprintf("gs -sDEVICE=pdfwrite -dPDFSETTINGS=%s -dPassThroughJPEGImages=false -dPassThroughJPXImages=false -dAutoFilterGrayImages=false -dAutoFilterColorImages=false -dDetectDuplicateImages=true -dQUIET -dBATCH -o %s %s", $compressionType, $outputFileName, $filePath));

        if ($returnCode === false) {
            http_response_code("500");
            header('Content-Type: text/plain');
            echo _("PDF compression failed");
            return;
        } elseif (filesize($filePath) <= filesize($outputFileName)) {
            http_response_code("500");
            header('Content-Type: text/plain');
            echo _("Your pdf is already optimized");
            return;
        } else {
            header('Content-Type: application/pdf');
            header("Content-Disposition: attachment; filename=".basename($outputFileName));
            readfile($outputFileName);
        }

        unlink($outputFileName);
        unlink($filePath);
    }
);

$f3->route('GET /api/file/get', function($f3) {
    $localRootFolder = $f3->get('PDF_LOCAL_PATH');
    if (!$localRootFolder) {
        $f3->error(403);
    }
    $pdf_path = $localRootFolder . '/' . $f3->get('GET.path');
    $pdf_filename = basename($pdf_path);
    if (!preg_match('/.pdf$/', $pdf_path)) {
        $f3->error(403);
    }
    if (!file_exists($pdf_path)) {
        $f3->error(403);
    }
    header('Content-type: application/pdf');
    header("Content-Disposition: attachment; filename=$pdf_filename");
    echo file_get_contents($pdf_path);
});

$f3->route('PUT /api/file/save', function($f3) {
    $localRootFolder = $f3->get('PDF_LOCAL_PATH');
    if (!$localRootFolder) {
        $f3->error(403);
    }
    $pdf_path = $localRootFolder . '/' . $f3->get('GET.path');
    $pdf_filename = basename($pdf_path);
    if (!preg_match('/.pdf$/', $pdf_path)) {
        $f3->error(403);
    }
    if (!file_exists($pdf_path)) {
        $f3->error(403);
    }
    file_put_contents($pdf_path, $f3->get('BODY'));

});

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
