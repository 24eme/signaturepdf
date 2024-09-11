<?php

class PDFSignature
{
    protected $symmetricKey = null;
    protected $pathHash = null;
    protected $hash = null;
    protected $gpg = null;
    protected $toClean = [];
    protected $lockFile = null;
    protected $cacheDecryptFiles = [];

    public function __construct($pathHash, $symmetricKey = null) {
        $this->symmetricKey = $symmetricKey;
        $this->pathHash = $pathHash;
        $this->hash = basename($this->pathHash);
        $this->gpg = new GPGCryptography($symmetricKey, $pathHash);
        $this->lockFile = $this->pathHash.'/.lock';
}

    public function createShare($originalFile, $originFileBaseName, $duration) {
        mkdir($this->pathHash);
        $expireFile = $this->pathHash.".expire";
        file_put_contents($expireFile, $duration);
        touch($expireFile, date_format(date_modify(date_create(), file_get_contents($expireFile)), 'U'));
        rename($originalFile, $this->pathHash.'/original.pdf');
        file_put_contents($this->pathHash.'/filename.txt', $originFileBaseName);
        if($this->symmetricKey) {
            $this->gpg->encrypt();
        }
    }

    public function verifyEncryption() {
        if(!$this->isEncrypted()) {

            return true;
        }

        return file_exists($this->getDecryptFile($this->pathHash."/filename.txt"));
    }

    public function isEncrypted() {
        return $this->gpg->isEncrypted();
    }

    public function getDecryptFile($file) {
        if(!$this->isEncrypted()) {
            return $file;
        }

        if(array_key_exists($file, $this->cacheDecryptFiles)) {
            return $this->cacheDecryptFiles[$file];
        }

        $decryptFile = $this->gpg->decryptFile($file);
        $this->toClean[] = $decryptFile;
        $this->cacheDecryptFiles[$file] = $decryptFile;

        return $decryptFile;
    }

    public function getPDF() {
        $this->compile();
        return $this->getDecryptFile($this->pathHash.'/final.pdf');
    }

    public function needToCompile() {
        $needToCompile = false;
        foreach($this->getLayers() as $layerFile) {
            if(!file_exists(str_replace('.svg.pdf', '.sign.pdf', $layerFile))) {
                $needToCompile = true;
            }
        }
        if(!$this->isEncrypted() && !file_exists($this->pathHash.'/final.pdf')) {
            $needToCompile = true;
        }
        if($this->isEncrypted() && !file_exists($this->pathHash.'/final.pdf.gpg')) {
            $needToCompile = true;
        }

        return $needToCompile;
    }

    protected function isCompileLock() {
        if(file_exists(touch($this->lockFile)) && filemtime($this->lockFile) > time() + 30) {
            unlink($this->lockFile);
        }

        return file_exists(touch($this->lockFile));
    }

    protected function lockCompile() {
        touch($this->lockFile);
    }

    protected function unlockCompile() {
        unlink($this->lockFile);
    }

    public function compile() {
        if(!$this->needToCompile()) {
            return;
        }

        if($this->isCompileLock()) {
            sleep(1);
            return $this->compile();
        }

        $this->lockCompile();

        $pathHashDecrypted = $this->gpg->decrypt();

        if ($pathHashDecrypted == false) {
            throw new Exception("PDF file could not be decrypted. Cookie encryption key might be missing.");
        }
        if ($this->pathHash != $pathHashDecrypted && $this->isEncrypted()) {
            $this->toClean[] = $pathHashDecrypted;
        }

        $layers = $this->getLayers($pathHashDecrypted);
        $currentSignedFile = $pathHashDecrypted.'/original.pdf';
        $signedFileToCopy = [];
        foreach($layers as $layerFile) {
            $signedFile = str_replace('.svg.pdf', '.sign.pdf', $layerFile);
            if(!file_exists($signedFile)) {
                self::addSvgToPDF($currentSignedFile, $layerFile, $signedFile, false);
                if ($this->pathHash != $pathHashDecrypted && $this->isEncrypted()) {
                    copy($signedFile, str_replace($pathHashDecrypted ,$this->pathHash, $signedFile));
                }
            }
            $currentSignedFile = $signedFile;
        }

        copy($currentSignedFile, $this->pathHash.'/final.pdf');

        if($this->isEncrypted()) {
            $this->gpg->encrypt();
        }

        $this->unlockCompile();
    }

    public function getPublicFilename() {
        $filename = $this->hash.'.pdf';

        $file = $this->getDecryptFile($this->pathHash."/filename.txt");

        if(file_exists($file)) {
            $filename = file_get_contents($file);
        }

        $filename = str_replace('.pdf', '_signe-'.count($this->getLayers()).'x.pdf', $filename);

        return $filename;
    }

    public function getLayers($pathHash = null) {
        if(is_null($pathHash)) {
            $pathHash = $this->pathHash;
        }
        $files = scandir($pathHash);
        $layers = [];
        foreach($files as $file) {
            if(strpos($file, '.svg.pdf') !== false) {
                $layers[] = $pathHash.'/'.$file;
            }
        }
        return $layers;
    }

    public function addSignature(array $svgFiles) {
        $expireFile = $this->pathHash.".expire";
        touch($expireFile, date_format(date_modify(date_create(), file_get_contents($expireFile)), 'U'));

        do {
            if(isset($svgPDFFile)) { usleep(1); }
            $svgPDFFile = $this->pathHash."/".(new DateTime())->format('YmdHisu').'.svg.pdf';
        } while (file_exists($svgPDFFile));

        self::createPDFFromSvg($svgFiles, $svgPDFFile);

        if($this->isEncrypted()) {
            $this->gpg->encrypt();
        }
        $this->toClean = array_merge($this->toClean, $svgFiles);
        $this->compile();
    }

    public static function createPDFFromSvg(array $svgFiles, $outputPdfFile) {
        shell_exec(sprintf("rsvg-convert -f pdf -o %s %s", $outputPdfFile, implode(" ", $svgFiles)));
    }

    public static function addSvgToPDF($pdfOrigin, $pdfSvg, $pdfOutput, $digitalSignature = true) {
        shell_exec(sprintf("pdftk %s multistamp %s output %s", $pdfOrigin, $pdfSvg, $pdfOutput));
        if (NSSCryptography::getInstance()->isEnabled() && $digitalSignature) {
            NSSCryptography::getInstance()->addSignature($pdfOutput, 'Signed with SignaturePDF');
        }
    }

    public function clean() {
        foreach($this->toClean as $path) {
            if(strpos($path, $this->pathHash) !== false) {
                continue;
            }
            GPGCryptography::hardUnlink($path);
        }
    }

    public static function isrsvgConvertInstalled() {
        $output = null;
        $returnCode = null;

        exec('rsvg-convert --version', $output, $returnCode);

        if (!$output) {
            return array(false);
        }
        return $output;
    }

    public static function ispdftkInstalled() {
        $output = null;
        $returnCode = null;

        exec('pdftk --version', $output, $returnCode);

        if (!$output) {
            return array(false);
        }
        return $output;
    }

}
