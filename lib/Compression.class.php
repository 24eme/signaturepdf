<?php

class Compression
{
    protected $pdfFile = null;
    protected $toClean = [];

    public function __construct($pdfFile) {
        $this->pdfFile = $pdfFile;
        $this->toClean[] = $pdfFile;
    }

    public function compress($compressionType) {
        $outputFile = str_replace(".pdf", "_compressed.pdf", $this->pdfFile);

        if ($compressionType === 'medium') {
            $compressionType = '/ebook';
        } elseif ($compressionType === 'low') {
            $compressionType = '/printer';
        } else {
            $compressionType = '/screen';
        }

        $returnCode = shell_exec(sprintf("gs -sDEVICE=pdfwrite -dPDFSETTINGS=%s -dPassThroughJPEGImages=false -dPassThroughJPXImages=false -dAutoFilterGrayImages=false -dAutoFilterColorImages=false -dDetectDuplicateImages=true -dAutoRotatePages=/None -dQUIET -dBATCH -o %s %s", escapeshellarg($compressionType), escapeshellarg($outputFile), escapeshellarg($this->pdfFile)));

        if(!file_exists($outputFile)) {
            throw new Exception("Compression failed");
        }

        $this->toClean[] = $outputFile;

        if ($returnCode === false) {
            throw new Exception("Compression failed");
        }

        return $outputFile;
    }

    public function clean() {
        foreach($this->toClean as $file) {
            GPGCryptography::hardUnlink($file);
        }
    }

    public static function isgsInstalled() {
        $output = null;
        $returnCode = null;

        exec('gs --version', $output, $returnCode);

        if (!$output) {
            return array(false);
        }
        return $output;
    }
}
