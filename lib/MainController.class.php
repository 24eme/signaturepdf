<?php

class MainController
{
    function index(Base $f3) {
        if ($f3->get('disableOrganization')) {
            return $f3->reroute($f3->get('REVERSE_PROXY_URL').'/signature');
        }

        $f3->set('activeTab', 'index');

        echo View::instance()->render('index.html.php');
    }

    function signature(Base $f3) {
        $f3->set('maxSize',  min(array(Config::convertPHPSizeToBytes(ini_get('post_max_size')), Config::convertPHPSizeToBytes(ini_get('upload_max_filesize')))));
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

    function signatureHash(Base $f3) {
        $f3->set('hash', Web::instance()->slug($f3->get('PARAMS.hash')));
        $f3->set('maxSize',  min(array(Config::convertPHPSizeToBytes(ini_get('post_max_size')), Config::convertPHPSizeToBytes(ini_get('upload_max_filesize')))));
        $f3->set('maxPage',  ini_get('max_file_uploads') - 1);

        if(!is_dir($f3->get('PDF_STORAGE_PATH').$f3->get('hash'))) {
            $f3->error(404);
        }
        $f3->set('isPdfEncrypted', GPGCryptography::isPathEncrypted($f3->get('PDF_STORAGE_PATH').$f3->get('hash')));
        $f3->set('activeTab', 'sign');
        echo View::instance()->render('signature.html.php');
    }

    function image2svg(Base $f3) {
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

        shell_exec(sprintf("convert -background white -flatten %s %s", escapeshellarg($imageFile), escapeshellarg($imageFile.".bmp")));
        shell_exec(sprintf("mkbitmap -x -f 8 %s -o %s", escapeshellarg($imageFile.".bmp"), escapeshellarg($imageFile.".bpm")));
        shell_exec(sprintf("potrace --flat --svg %s -o %s", escapeshellarg($imageFile.".bpm"), escapeshellarg($imageFile.".svg")));

        header('Content-Type: image/svg+xml');
        echo file_get_contents($imageFile.".svg");

        if($f3->get('DEBUG')) {
            return;
        }

        array_map('unlink', glob($imageFile."*"));
    }

    function sign(Base $f3) {
        $filename = null;
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
                $svgFile = $tmpfile."_".md5($fileBaseName).".svg";
                $svgFiles[] = $svgFile;
                return basename($svgFile);
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
        if ($f3->get('POST.flatten')) {
            PDFSignature::flatten($tmpfile);
        }

        Web::instance()->send($tmpfile.'_signe.pdf', null, 0, TRUE, $filename);

        if($f3->get('DEBUG')) {
            return;
        }
        array_map('unlink', glob($tmpfile."*"));
    }

    function share(Base $f3) {
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
                    $svgFile = $tmpfile."_".md5($fileBaseName).".svg";
                    $svgFiles[] = $svgFile;
                    return basename($svgFile);
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
        \Flash::instance()->setKey("adminKey", $pdfSignature->createAdminKey());

        $f3->reroute($f3->get('REVERSE_PROXY_URL').'/signature/'.$hash.(($symmetricKey) ? '#'.$symmetricKey : null));
    }

    function signatureDeletion(Base $f3) {
        $sharingFolder = $f3->get('PDF_STORAGE_PATH');
        $baseHash = $sharingFolder.Web::instance()->slug($f3->get('PARAMS.hash'));

        if (is_dir($baseHash) === false) {
            $f3->error(403);
        }

        if (is_file($baseHash.'.admin') === false || is_readable($baseHash.'.admin') === false) {
            $f3->error(403);
        }

        if (file_get_contents($baseHash.'.admin') !== $f3->get('PARAMS.key')) {
            $f3->error(403);
        }

        GPGCryptography::hardUnlink($baseHash.'/.lock');
        GPGCryptography::hardUnlink($baseHash);
        unlink($baseHash.'.admin');
        unlink($baseHash.'.expire');

        $f3->reroute($f3->get('REVERSE_PROXY_URL').'/signature');
    }

    function signaturePdf(Base $f3) {
        $f3->set('activeTab', 'sign');
        $hash = Web::instance()->slug($f3->get('PARAMS.hash'));
        $symmetricKey = (isset($_COOKIE[$hash])) ? GPGCryptography::protectSymmetricKey($_COOKIE[$hash]) : null;
        $pdfSignature = new PDFSignature($f3->get('PDF_STORAGE_PATH').$hash, $symmetricKey);
        if(!$pdfSignature->verifyEncryption()) {
            $f3->error(403, 'Unable to decrypt pdf because of wrong symmetric key');
        }

        Web::instance()->send($pdfSignature->getPDF(), null, 0, TRUE, urlencode($pdfSignature->getPublicFilename()));

        if($f3->get('DEBUG')) {
            return;
        }

        $pdfSignature->clean();
    }

    function signatureSave(Base $f3) {
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
                $svgFile = $tmpfile."_".md5($fileBaseName).".svg";
                $svgFiles[] = $svgFile;
                return basename($svgFile);
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

    function signatureNblayers(Base $f3) {
        $f3->set('activeTab', 'sign');
        $hash = Web::instance()->slug($f3->get('PARAMS.hash'));
        $symmetricKey = (isset($_COOKIE[$hash])) ? GPGCryptography::protectSymmetricKey($_COOKIE[$hash]) : null;
        $pdfSignature = new PDFSignature($f3->get('PDF_STORAGE_PATH').$hash, $symmetricKey);
        echo count($pdfSignature->getLayers());
    }

    function cron(Base $f3) {
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
    }

    function organization(Base $f3) {
        $f3->set('activeTab', 'organize');
        echo View::instance()->render('organization.html.php');
    }

    function metadata(Base $f3) {
        $f3->set('activeTab','metadata');
        echo View::instance()->render('metadata.html.php');
    }

    function ocr(Base $f3) {
        $originalFilename = null;
        $files = Web::instance()->receive(function($file,$formFieldName) {
            if ($formFieldName == "pdf" && strpos(Web::instance()->mime($file['tmp_name'], true), 'application/pdf') !== 0) {
                $f3->error(403);
            }
        }, false, function($fileBaseName, $formFieldName) use(&$originalFilename) {
            $originalFilename = $fileBaseName;
            return date("YmdHis")."_".uniqid()."_".md5($fileBaseName).'.pdf';
        });

        if(!count($files)) {
            http_response_code("500");
            header('Content-Type: text/plain');
            echo _("PDF OCR failed");
            return;
        }

        $filePath = reset(array_keys($files));
        $outputFileName = str_replace(".pdf", "_ocr.pdf", $filePath);

        $returnCode = shell_exec(sprintf("ocrmypdf --output-type pdf --force-ocr %s %s", escapeshellarg($filePath), escapeshellarg($outputFileName)));

        if ($returnCode === false || !file_exists($outputFileName)) {
            unlink($outputFileName);
            unlink($filePath);
            http_response_code("500");
            header('Content-Type: text/plain');
            echo _("PDF OCR failed");
            return;
        }

        header('Content-Type: application/pdf');
        header("Content-Disposition: attachment; filename=".urlencode(basename(str_replace(".pdf", "_ocr.pdf", $originalFilename))));
        readfile($outputFileName);

        unlink($outputFileName);
        unlink($filePath);
    }

    function administration(Base $f3) {
        if (! $f3->get('IS_ADMIN')) {
            die('You ('.$_SERVER['REMOTE_ADDR'].') are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
        }
        $f3->set('activeTab','admin');
        echo View::instance()->render('admin_setup.html.php');
    }

    function compression(Base $f3) {
        $f3->set('maxSize',  min(array(Config::convertPHPSizeToBytes(ini_get('post_max_size')), Config::convertPHPSizeToBytes(ini_get('upload_max_filesize')))));
        $f3->set('activeTab', 'compress');
        echo View::instance()->render('compress.html.php');
    }

    function compress(Base $f3) {
        $originalFilename = null;
        $files = Web::instance()->receive(function($file,$formFieldName) {
            if ($formFieldName == "pdf" && strpos(Web::instance()->mime($file['tmp_name'], true), 'application/pdf') !== 0) {
                $f3->error(403);
            }
        }, false, function($fileBaseName, $formFieldName) use(&$originalFilename) {
            $originalFilename = $fileBaseName;
            return date("YmdHis")."_".uniqid()."_".md5($fileBaseName).'.pdf';
        });

        if(!count($files)) {
            http_response_code("500");
            header('Content-Type: text/plain');
            echo _("PDF compression failed");
            return;
        }

        $filePath = reset(array_keys($files));

        $compression = new Compression($filePath);

        try {
            $output = $compression->compress($f3->get('POST.compressionType'));
        } catch(Exception $e ){
            $compression->clean();
            http_response_code("500");
            header('Content-Type: text/plain');
            echo _("PDF compression failed");
            return;
        }

        if (filesize($filePath) <= filesize($output)) {
            $compression->clean();
            http_response_code("204");
            return;
        }

        header('Content-Type: application/pdf');
        header("Content-Disposition: attachment; filename=".urlencode(basename(str_replace(".pdf", "_compressed.pdf", $originalFilename))));
        readfile($output);

        $compression->clean();
    }

}
