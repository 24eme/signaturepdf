<?php

$f3 = require(__DIR__.'/vendor/fatfree/base.php');

if(getenv("DEBUG")) {
    $f3->set('DEBUG', getenv("DEBUG"));
}

$f3->set('XFRAME', null); // Allow use in an iframe
$f3->set('ROOT', __DIR__);
$f3->set('UI', $f3->get('ROOT')."/templates/");
$f3->set('UPLOADS', sys_get_temp_dir()."/");
$f3->set('STORAGE', sys_get_temp_dir()."/pdf/");

function convertPHPSizeToBytes($sSize)
{
    //
    $sSuffix = strtoupper(substr($sSize, -1));
    if (!in_array($sSuffix,array('P','T','G','M','K'))){
        return (int)$sSize;
    }
    $iValue = substr($sSize, 0, -1);
    switch ($sSuffix) {
        case 'P':
            $iValue *= 1024;
            // Fallthrough intended
        case 'T':
            $iValue *= 1024;
            // Fallthrough intended
        case 'G':
            $iValue *= 1024;
            // Fallthrough intended
        case 'M':
            $iValue *= 1024;
            // Fallthrough intended
        case 'K':
            $iValue *= 1024;
            break;
    }
    return (int)$iValue;
}

$f3->route('GET /',
    function($f3) {
        $f3->reroute('/signature');
    }
);
$f3->route('GET /signature',
    function($f3) {
        $f3->set('maxSize',  min(array(convertPHPSizeToBytes(ini_get('post_max_size')), convertPHPSizeToBytes(ini_get('upload_max_filesize')))));
        $f3->set('maxPage',  ini_get('max_file_uploads') - 1);

        echo View::instance()->render('signature.html.php');
    }
);

$f3->route('GET /signature/@hash',
    function($f3, $param) {
        $f3->set('hash', $param['hash']);

        $f3->set('maxSize',  min(array(convertPHPSizeToBytes(ini_get('post_max_size')), convertPHPSizeToBytes(ini_get('upload_max_filesize')))));
        $f3->set('maxPage',  ini_get('max_file_uploads') - 1);

        echo View::instance()->render('signature.html.php');
    }
);

$f3->route('GET /organization',
    function($f3) {
        $f3->set('maxSize',  min(array(convertPHPSizeToBytes(ini_get('post_max_size')), convertPHPSizeToBytes(ini_get('upload_max_filesize')))));

        echo View::instance()->render('organization.html.php');
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
        shell_exec(sprintf("pdftk %s multibackground %s output %s", $tmpfile.'.svg.pdf', $tmpfile.".pdf", $tmpfile.'_signe.pdf'));

        Web::instance()->send($tmpfile.'_signe.pdf', null, 0, TRUE, $filename);

        if($f3->get('DEBUG')) {
            return;
        }
        array_map('unlink', glob($tmpfile."*"));
    }
);

$f3->route('POST /share',
    function($f3) {
        $hash = substr(hash('sha512', uniqid().rand()), 0, 20);
        $sharingFolder = $f3->get('STORAGE').$hash."/";
        $f3->set('UPLOADS', $sharingFolder);
        mkdir($sharingFolder);
        $filename = "original.pdf";

        $files = Web::instance()->receive(function($file,$formFieldName){
            if(strpos(Web::instance()->mime($file['tmp_name'], true), 'application/pdf') !== 0) {
                $f3->error(403);
            }

            return true;
        }, false, function($fileBaseName, $formFieldName) use ($filename) {

            return $filename;
	    });

        if(!count($files)) {
            $f3->error(403);
        }
        $f3->reroute('/signature/'.$hash);
    }

);

$f3->route('POST /organize',
    function($f3) {
        $filename = null;
        $tmpfile = tempnam($f3->get('UPLOADS'), 'pdfsignature_organize');
        unlink($tmpfile);
        $pages = explode(',', $f3->get('POST.pages'));

        $files = Web::instance()->receive(function($file,$formFieldName){
            if($formFieldName == "pdf" && strpos(Web::instance()->mime($file['tmp_name'], true), 'application/pdf') !== 0) {
                $f3->error(403);
            }
            return true;
        }, false, function($fileBaseName, $formFieldName) use ($f3, $tmpfile, &$filename, $pages) {
            if($formFieldName == "pdf") {
                $filename = str_replace(".pdf", "_page_".implode("-", $pages).".pdf", $fileBaseName);
                return basename($tmpfile).".pdf";
            }
	    });

        if(!is_file($tmpfile.".pdf")) {
            $f3->error(403);
        }

        shell_exec(sprintf("pdftk %s cat %s output %s", $tmpfile.".pdf", implode(" ", $pages), $tmpfile.'_organize.pdf'));

        Web::instance()->send($tmpfile."_organize.pdf", null, 0, TRUE, $filename);

        if($f3->get('DEBUG')) {
            return;
        }
        array_map('unlink', glob($tmpfile."*"));
    }
);

$f3->route('GET /signature/@hash/pdf',
    function($f3) {
        $targetDir = $f3->get('STORAGE').$f3->get('PARAMS.hash');
        $files = array_diff(scandir($targetDir), array('..', '.'));
        $original = null;
        $originalFilename = null;
        $layers = [];
        foreach($files as $file) {
            if (strpos($file, '.pdf') === false || strpos($file, 'signe.pdf') !== false) {
                continue;
            }
            if(strpos($file, 'svg.pdf') !== false) {
                $layers[] = $targetDir.'/'.$file;
                continue;
            }
            $original = $targetDir.'/'.$file;
            $originalFilename = $file;
        }
        if (!$original) {
            $f3->error(404);
        }
        if (!$layers) {
            Web::instance()->send($original, null, 0, TRUE, str_replace('.pdf', '_signe.pdf', $originalFilename));
        }
        $newFile =  str_replace('.pdf', '_signe.pdf', $original);
        $newFilename = str_replace('.pdf', '_signe.pdf', $originalFilename);
        shell_exec(sprintf("pdftk %s multibackground %s output %s", $layers[0], $original, $newFile));
        for($i = 1, $max = count($layers); $i < $max; $i++) {
            shell_exec(sprintf("pdftk %1\$s multibackground %2\$s output %3\$s && mv %3\$s %2\$s", $layers[$i], $newFile, str_replace('_signe.pdf', '_tmp_signe.pdf', $newFile)));
        }
        Web::instance()->send($newFile, null, 0, TRUE, $newFilename);

        if($f3->get('DEBUG')) {
            return;
        }
        unlink($newFile);
    }
);

return $f3;
