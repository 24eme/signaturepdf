<?php

class PDFSignature
{
    protected $symmetricKey = null;
    protected $pathHash = null;
    protected $hash = null;
    protected $gpg = null;
    protected $toClean = [];

    public function __construct($pathHash, $symmetricKey = null) {
        $this->symmetricKey = $symmetricKey;
        $this->pathHash = $pathHash;
        $this->hash = basename($this->pathHash);
        $this->gpg = new GPGCryptography($symmetricKey, $pathHash);
    }


    public function createShare($duration) {
        mkdir($this->pathHash);
        $expireFile = $this->pathHash.".expire";
        file_put_contents($expireFile, $duration);
        touch($expireFile, date_format(date_modify(date_create(), file_get_contents($expireFile)), 'U'));
    }

    public function saveShare() {
        if($this->symmetricKey) {
            $this->gpg->encrypt();
        }
    }

    public function getPDF() {
        $sharingFolder = $this->gpg->decrypt();
        if ($sharingFolder == false) {
            throw new Exception("PDF file could not be decrypted. Cookie encryption key might be missing.");
        }
        if ($this->pathHash != $sharingFolder && $this->gpg->isEncrypted()) {
            $this->toClean[] = $sharingFolder;
        }
        $files = scandir($sharingFolder);
        $originalFile = $sharingFolder.'/original.pdf';
        $finalFile = $sharingFolder.'/'.$this->hash.uniqid().'.pdf';
        $filename = $this->hash.'.pdf';
        if(file_exists($sharingFolder."/filename.txt")) {
            $filename = file_get_contents($sharingFolder."/filename.txt");
        }
        $layers = [];
        foreach($files as $file) {
            if(strpos($file, 'svg.pdf') !== false) {
                $layers[] = $sharingFolder.'/'.$file;
            }
        }
        if(!count($layers)) {
            return [$originalFile, $filename];
        }

        $filename = str_replace('.pdf', '_signe-'.count($layers).'x.pdf', $filename);
        copy($originalFile, $finalFile);
        $bufferFile =  $finalFile.".tmp";
        foreach($layers as $layerFile) {
            self::addSvgToPDF($finalFile, $layerFile, $bufferFile, false);
            rename($bufferFile, $finalFile);
        }

        if ($this->pathHash == $sharingFolder && !$this->gpg->isEncrypted()) {
            $this->toClean[] = $finalFile;
        }

        return [$finalFile, $filename];
    }

    public function addSignature(array $svgFiles, $outputPdfFile) {
        $expireFile = $this->pathHash.".expire";
        touch($expireFile, date_format(date_modify(date_create(), file_get_contents($expireFile)), 'U'));

        self::createPDFFromSvg($svgFiles, $outputPdfFile);

        if($this->gpg->isEncrypted()) {
            $this->gpg->encrypt();
        }
        $this->toClean = array_merge($this->toClean, $svgFiles);
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
