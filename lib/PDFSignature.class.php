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

    public function getPDF() {
        $sharingFolder = $this->gpg->decrypt();
        if ($sharingFolder == false) {
            throw new Exception( "PDF file could not be decrypted. Cookie encryption key might be missing.");
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
            shell_exec(sprintf("pdftk %s multistamp %s output %s", $finalFile, $layerFile, $bufferFile));
            rename($bufferFile, $finalFile);
        }

        if ($this->pathHash == $sharingFolder && !$this->gpg->isEncrypted()) {
            $this->toClean[] = $finalFile;
        }

        return [$finalFile, $filename];
    }

    public function clean() {
        foreach($this->toClean as $path) {
            GPGCryptography::hardUnlink($path);
        }
    }

}
