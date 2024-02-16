<?php

setlocale(LC_ALL, "");

$f3 = require(__DIR__.'/vendor/fatfree/base.php');

$f3->set('FALLBACK', null);
$f3->language(isset($f3->get('HEADERS')['Accept-Language']) ? $f3->get('HEADERS')['Accept-Language'] : '');

session_start();

if(getenv("DEBUG")) {
    $f3->set('DEBUG', getenv("DEBUG"));
}

$f3->set('SUPPORTED_LANGUAGES',
    ['fr' => 'Français',
        'en' => 'English',
        'ar' => 'العربية',
        'kab' => 'Taqbaylit',
        'oc' => 'Occitan',
        'it' => 'Italiano']);

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

if ($f3->get('GET.lang')) {
    selectLanguage($f3->get('GET.lang'), $f3, true);
} elseif (isset($_COOKIE['LANGUAGE'])) {
    selectLanguage($_COOKIE['LANGUAGE'], $f3, true);
} else {
    selectLanguage($f3->get('LANGUAGE'), $f3);
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
        $f3->reroute($f3->get('REVERSE_PROXY_URL').'/signature');
    }
);
$f3->route('GET /signature',
    function($f3) {
        $f3->set('maxSize',  min(array(convertPHPSizeToBytes(ini_get('post_max_size')), convertPHPSizeToBytes(ini_get('upload_max_filesize')))));
        $f3->set('maxPage',  ini_get('max_file_uploads') - 1);

        if(!$f3->get('PDF_STORAGE_PATH')) {
            $f3->set('noSharingMode', true);
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
        $tmpfile = tempnam($f3->get('UPLOADS'), 'pdfsignature_sign');
        unlink($tmpfile);
        $svgFiles = "";

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
                $svgFiles .= " ".$tmpfile."_".$fileBaseName;

                return basename($tmpfile."_".$fileBaseName);
            }
	    });

        if(!is_file($tmpfile.".pdf")) {
            $f3->error(403);
        }

        if(!$svgFiles) {
            $f3->error(403);
        }

        shell_exec(sprintf("rsvg-convert -f pdf -o %s %s", $tmpfile.'.svg.pdf', $svgFiles));
        shell_exec(sprintf("pdftk %s multistamp %s output %s", $tmpfile.".pdf", $tmpfile.'.svg.pdf', $tmpfile.'_signe.pdf'));
        Web::instance()->send($tmpfile.'_signe.pdf', null, 0, TRUE, $filename);

        if($f3->get('DEBUG')) {
            return;
        }
        array_map('unlink', glob($tmpfile."*"));
    }
);

require_once 'lib/cryptography.class.php';

$f3->route('POST /share',
    function($f3) {
        $hash = Web::instance()->slug($_POST['hash']);
        $sharingFolder = $f3->get('PDF_STORAGE_PATH').$hash;
        $f3->set('UPLOADS', $sharingFolder."/");
        if (!is_dir($f3->get('PDF_STORAGE_PATH'))) {
            $f3->error(500, 'Sharing folder doesn\'t exist');
        }
        if (!is_writable($f3->get('PDF_STORAGE_PATH'))) {
            $f3->error(500, 'Sharing folder is not writable');
        }
        mkdir($sharingFolder);
        $expireFile = $sharingFolder.".expire";
        file_put_contents($expireFile, $f3->get('POST.duration'));
        touch($expireFile, date_format(date_modify(date_create(), file_get_contents($expireFile)), 'U'));
        $filename = "original.pdf";
        $tmpfile = tempnam($sharingFolder, date('YmdHis'));
        $svgFiles = "";
        $files = Web::instance()->receive(function($file,$formFieldName){
            if($formFieldName == "pdf" && strpos(Web::instance()->mime($file['tmp_name'], true), 'application/pdf') !== 0) {
                $f3->error(403);
            }
            if($formFieldName == "svg" && strpos(Web::instance()->mime($file['tmp_name'], true), 'image/svg+xml') !== 0) {
                $f3->error(403);
            }

            return true;
        }, false, function($fileBaseName, $formFieldName) use ($tmpfile, $filename, $sharingFolder, &$svgFiles) {
                if($formFieldName == "pdf") {
                    file_put_contents($sharingFolder."/filename.txt", $fileBaseName);
                    return $filename;
                }
                if($formFieldName == "svg") {
                    $svgFiles .= " ".$tmpfile."_".$fileBaseName;
                    return basename($tmpfile."_".$fileBaseName);
                }
	    });

        if(!count($files)) {
            $f3->error(403);
        }
        if($svgFiles) {
            shell_exec(sprintf("rsvg-convert -f pdf -o %s %s", $tmpfile.'.svg.pdf', $svgFiles));
        }
        if(!$f3->get('DEBUG')) {
            array_map('cryptographyClass::hardUnlink', glob($tmpfile."*.svg"));
        }

        $symmetricKey = $_COOKIE[$hash];
        $encryptor = new CryptographyClass($_COOKIE[$hash], $f3->get('PDF_STORAGE_PATH').$hash);
        $encryptor->encrypt();


        $f3->reroute($f3->get('REVERSE_PROXY_URL').'/signature/'.$hash."#sk:".$symmetricKey);
    }

);

$f3->route('GET /signature/@hash/pdf',
    function($f3) {
        $f3->set('activeTab', 'sign');
        $hash = Web::instance()->slug($f3->get('PARAMS.hash'));
        $sharingFolder = $f3->get('PDF_STORAGE_PATH').$hash;

        $cryptor = new CryptographyClass(CryptographyClass::protectSymmetricKey($_COOKIE[$hash]), $f3->get('PDF_STORAGE_PATH').$hash);
        if ($cryptor->decrypt() == false) {
            $f3->error(403);
        }

        $files = scandir($sharingFolder);
        $originalFile = $sharingFolder.'/original.pdf';
        $finalFile = $sharingFolder.'/'.$f3->get('PARAMS.hash').uniqid().'.pdf';
        $filename = $f3->get('PARAMS.hash').'.pdf';
        if(file_exists($sharingFolder."/filename.txt")) {
            $filename = file_get_contents($sharingFolder."/filename.txt");
        }
        $layers = [];
        foreach($files as $file) {
            if(strpos($file, 'svg.pdf') !== false) {
                $layers[] = $sharingFolder.'/'.$file;
            }
        }
        if (!$layers) {
            Web::instance()->send($originalFile, null, 0, TRUE, $filename);
        }
        $filename = str_replace('.pdf', '_signe-'.count($layers).'x.pdf', $filename);
        copy($originalFile, $finalFile);
        $bufferFile =  $finalFile.".tmp";
        foreach($layers as $layerFile) {
            shell_exec(sprintf("pdftk %s multistamp %s output %s", $finalFile, $layerFile, $bufferFile));
            rename($bufferFile, $finalFile);
        }
        Web::instance()->send($finalFile, null, 0, TRUE, $filename);

        $cryptor->encrypt($hash);

        if($f3->get('DEBUG')) {
            return;
        }
        array_map('unlink', glob($finalFile."*"));
    }
);

$f3->route('POST /signature/@hash/save',
    function($f3) {
        $hash = Web::instance()->slug($f3->get('PARAMS.hash'));
        $sharingFolder = $f3->get('PDF_STORAGE_PATH').$hash;
        $f3->set('UPLOADS', $sharingFolder.'/');
        $tmpfile = tempnam($sharingFolder, date('YmdHis'));
        unlink($tmpfile);
        $svgFiles = "";

        $expireFile = $sharingFolder.".expire";
        touch($expireFile, date_format(date_modify(date_create(), file_get_contents($expireFile)), 'U'));

        $files = Web::instance()->receive(function($file,$formFieldName){
            if($formFieldName == "svg" && strpos(Web::instance()->mime($file['tmp_name'], true), 'image/svg+xml') !== 0) {
                $f3->error(403);
            }
            return true;
        }, false, function($fileBaseName, $formFieldName) use ($f3, $tmpfile, &$svgFiles) {
            if($formFieldName == "svg") {
                $svgFiles .= " ".$tmpfile."_".$fileBaseName;
                return basename($tmpfile."_".$fileBaseName);
            }
	    });

        if(!$svgFiles) {
            $f3->error(403);
        }

        shell_exec(sprintf("rsvg-convert -f pdf -o %s %s", $tmpfile.'.svg.pdf', $svgFiles));

        if(!$f3->get('DEBUG')) {
            array_map('unlink', explode(' ', trim($svgFiles)));
        }

        $f3->reroute($f3->get('REVERSE_PROXY_URL').'/signature/'.$f3->get('PARAMS.hash')."#signed");
    }
);

$f3->route('GET /signature/@hash/nblayers',
    function($f3) {
        $f3->set('activeTab', 'sign');
        $hash = Web::instance()->slug($f3->get('PARAMS.hash'));
        $files = scandir($f3->get('PDF_STORAGE_PATH').$hash);
        $nbLayers = 0;
        foreach($files as $file) {
            if(strpos($file, 'svg.pdf') !== false) {
                $nbLayers++;
            }
        }
        echo $nbLayers;
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
        rmdir($expiredFolder);
        unlink($expireFile);
    }
});

if (!$f3->get('disableOrganization')) {
$f3->route('GET /organization',
    function($f3) {
        $f3->set('maxSize',  min(array(convertPHPSizeToBytes(ini_get('post_max_size')), convertPHPSizeToBytes(ini_get('upload_max_filesize')))));

        $f3->set('activeTab', 'organize');
        echo View::instance()->render('organization.html.php');
    }
);

$f3->route('POST /organize',
    function($f3) {
        $filenames = array();
        $tmpfile = tempnam($f3->get('UPLOADS'), 'pdfsignature_organize');
        unlink($tmpfile);
        $pages = explode(',', preg_replace('/[^A-Z0-9a-z,]+/', '', $f3->get('POST.pages')));

        $files = Web::instance()->receive(function($file,$formFieldName){
            if(strpos(Web::instance()->mime($file['tmp_name'], true), 'application/pdf') !== 0) {
                $f3->error(403);
            }

            return true;
        }, false, function($fileBaseName, $formFieldName) use ($tmpfile, &$filenames) {
            $filenames[] = str_replace('.pdf', '', $fileBaseName);

            return basename($tmpfile).uniqid().".pdf";
        });

        if(!count($files)) {
            $f3->error(403);
        }

        $pdfs = array();
        foreach(array_keys($files) as $i => $file) {
            $pdfs[] = chr(65 + $i)."=".$file;
        }

        shell_exec(sprintf("pdftk %s cat %s output %s", implode(" ", $pdfs), implode(" ", $pages), $tmpfile.'_final.pdf'));

        Web::instance()->send($tmpfile."_final.pdf", null, 0, TRUE, implode('_', $filenames));

        if($f3->get('DEBUG')) {
            return;
        }

        array_map('unlink', glob($tmpfile."*"));
    }
);
}

$f3->route('GET /metadata',
    function($f3) {
        $f3->set('activeTab','metadata');
        echo View::instance()->render('metadata.html.php');
    }
);

$f3->route('GET /compress',
    function($f3) {
        $f3->set('error_message', "none");
        $f3->set('maxSize',  min(array(convertPHPSizeToBytes(ini_get('post_max_size')), convertPHPSizeToBytes(ini_get('upload_max_filesize')))));
        $f3->set('activeTab', 'compress');
        if (isset($_GET['err'])) {
            $f3->set('error_message', "PDF optimized");
        }
        echo View::instance()->render('compress.html.php');
    }
);

$f3->route ('POST /compress',
    function($f3) {
        $filename = null;
        $tmpfile = tempnam($f3->get('UPLOADS'), 'pdfsignature_sign');
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
        } elseif ($compressionType === 'high') {
            $compressionType = '/screen';
        } else {
            $compressionType = '/screen';
        }

        $arrayPath = array_keys($files);
        $filePath = reset($arrayPath);

        $outputFileName = str_replace(".pdf", "_compressed.pdf", $filePath);

        $returnCode = shell_exec(sprintf("gs -sDEVICE=pdfwrite -dPDFSETTINGS=%s -dQUIET -o %s %s", $compressionType, $outputFileName, $filePath));

        if ($returnCode !== false) {
            if (filesize($filePath) <= filesize($outputFileName)) {
                $error = "pdfalreadyoptimized";
                header('location: /compress?err=' . $error);
            } else {
                header('Content-Type: application/pdf');
                header("Content-Disposition: attachment; filename=$outputFileName");
                readfile($outputFileName);
                unlink($outputFileName);
            }
        } else {
            echo "PDF compression failed.";
        }
    }
);

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
        case 'P': $iValue *= 1024;
        case 'T': $iValue *= 1024;
        case 'G': $iValue *= 1024;
        case 'M': $iValue *= 1024;
        case 'K': $iValue *= 1024; break;
    }
    return (int)$iValue;
}

return $f3;
