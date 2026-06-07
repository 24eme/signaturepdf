<?php

class ApiController
{
    function fileGet(Base $f3) {
        if (!($pdf_path = Config::getInstance()->getApiLocalFilePath())) {
            $f3->error(403);
        }
        $pdf_filename = basename($pdf_path);
        $extension = 'pdf';
        if (!preg_match('/.(pdf|png|jpg|jpeg)$/', $pdf_path, $m)) {
            $f3->error(403);
        }
        $extension = $m[1];
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                header('Content-type: image/jpg');
                break;
            case 'png':
                header('Content-type: image/png');
                break;
            default:
                header('Content-type: application/pdf');
                break;
        }
        header("Content-Disposition: attachment; filename=$pdf_filename");
        echo file_get_contents($pdf_path);
    }

    function fileSave(Base $f3) {
        if (!($pdf_path = Config::getInstance()->getApiLocalFilePath())) {
            $f3->error(403);
        }
        $pdf_filename = basename($pdf_path);
        if (!preg_match('/(.*).(pdf|png|jpg|jpeg)$/', $pdf_path, $m)) {
            $f3->error(403);
        }
        $basefile = $m[1];
        $extension = $m[2];
        file_put_contents($basefile.'.pdf', $f3->get('BODY'));
    }

    function shareNew(Base $f3) {
        if (! is_dir($f3->get('PDF_STORAGE_PATH'))) {
            echo json_encode(['message' => 'Sharing folder doesn\'t exist']);
            return http_response_code(500);
        }

        if (! is_writable($f3->get('PDF_STORAGE_PATH'))) {
            echo json_encode(['message' => 'Sharing folder is not writable']);
            return http_response_code(500);
        }

        if (! $f3->get('POST.duration')) {
            echo json_encode(['message' => 'Missing parameter `duration`']);
            return http_response_code(400);
        }

        $symmetricKey = GPGCryptography::createSymmetricKey();
        $hashPath = strtolower(GPGCryptography::createSymmetricKey());

        $tmpfile = tempnam($f3->get('UPLOADS'), 'pdfsignature_share_'.uniqid($symmetricKey, true));
        unlink($tmpfile);

        $originalFile = $tmpfile."_original.pdf";
        $originalFileBaseName = null;

        $files = Web::instance()->receive(function($file, $formFieldName) {
            if ($formFieldName !== "pdf") {
                return false;
            }

            if (strpos(Web::instance()->mime($file['tmp_name'], true), 'application/pdf') !== 0) {
                return false;
            }

            return true;
        }, false, function($fileBaseName, $formFieldName) use ($originalFile, &$originalFileBaseName) {
            if($formFieldName == "pdf") {
                $originalFileBaseName = $fileBaseName;
                return basename($originalFile);
            }
        });

        if(! count($files)) {
            echo json_encode(['message' => 'Invalid file uploaded']);
            return http_response_code(400);
        }

        $pdfSignature = new PDFSignature($f3->get('PDF_STORAGE_PATH').$hashPath, $symmetricKey);
        $pdfSignature->createShare($originalFile, $originalFileBaseName, $f3->get('POST.duration'));

        if (! $f3->get('DEBUG')) {
            $pdfSignature->clean();
        }

        $adminKey = $pdfSignature->createAdminKey();

        echo json_encode([
            'adminkey' => $adminKey,
            'hash' => $hashPath,
            'symmetrickey' => $symmetricKey,
            'url' => $f3->get('SCHEME').'://'.$_SERVER['SERVER_NAME'].(!in_array($f3->get('PORT'),[80,443])?(':'.$f3->get('PORT')):'').$f3->get('BASE').'/signature/'.$hashPath.'#'.$symmetricKey
        ]);

        return http_response_code(201);
    }

    function shareDelete(Base $f3) {
        $sharingFolder = $f3->get('PDF_STORAGE_PATH');
        $baseHash = $sharingFolder.$f3->get('PARAMS.hash');

        if (is_dir($baseHash) === false) {
            echo json_encode(['message' => 'File not found']);
            return http_response_code(404);
        }

        if (is_file($baseHash.'.admin') === false || is_readable($baseHash.'.admin') === false) {
            echo json_encode(['message' => 'Admin file not found']);
            return http_response_code(403);
        }

        if (file_get_contents($baseHash.'.admin') !== $f3->get('PARAMS.adminkey')) {
            echo json_encode(['message' => 'Unauthorized access']);
            return http_response_code(403);
        }

        GPGCryptography::hardUnlink($baseHash.'/.lock');
        GPGCryptography::hardUnlink($baseHash);
        unlink($baseHash.'.admin');
        unlink($baseHash.'.expire');

        echo json_encode(['message' => 'Shared PDF successfully deleted']);

        return http_response_code(200);
    }

    function shareGet(Base $f3) {
        $path = Web::instance()->slug($f3->get('PARAMS.hash'));
        $symmetricKey = $f3->get('PARAMS.symmkey');

        $pdfSignature = new PDFSignature($f3->get('PDF_STORAGE_PATH').$path, $symmetricKey);

        if (! $pdfSignature->verifyEncryption()) {
            echo json_encode(['message' => 'Unauthorized access']);
            return http_response_code(403);
        }

        Web::instance()->send($pdfSignature->getPDF(), null, 0, TRUE, urlencode($pdfSignature->getPublicFilename()));

        if ($f3->get('DEBUG')) {
            return;
        }

        $pdfSignature->clean();
    }
}
