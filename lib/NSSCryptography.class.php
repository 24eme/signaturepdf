<?php

class NSSCryptography
{
    private $nss_directory = null;
    private $nss_password = null;
    private $nss_nick = null;

    private function __construct($dir, $pass, $nick) {
        $this->nss_directory = $dir;
        $this->nss_password = $pass;
        $this->nss_nick = $nick;
    }

    private static  $instance = null;

    public static function getInstance($dir = null, $pass = null, $nick = null) {
        if (!self::$instance) {
            self::$instance = new NSSCryptography($dir, $pass, $nick);
        }
        return self::$instance;
    }

    public function addSignature($pdf_path, $reason) {
        putenv('NSSPASS='.$this->nss_password);
        exec('pdfsig '.$pdf_path.' '.$pdf_path.'.signed.pdf -add-signature -nssdir "'.$this->nss_directory.'" -nss-pwd "$NSSPASS" -nick "'.$this->nss_nick.'" -reason "'.$reason.'" 2>&1', $output, $returnCode);
        if ($returnCode) {
            throw new Exception('pdfsign error: '.implode(' ', $output));
        }
        rename($pdf_path.'.signed.pdf', $pdf_path);
    }

    public function verify($pdf_path) {
        $signatures = [];

        putenv('NSSPASS='.$this->nss_password);
        exec('pdfsig -nssdir "'.$this->nss_directory.'" -nss-pwd "$NSSPASS" '.$pdf_path.' 2>&1', $output, $returnCode);

        if ($returnCode && !preg_match('/does not contain any signatures/', $output[0])) {
            throw new Exception('pdfsign error: '.implode(' ', $output));
        }

        $index = null;
        foreach($output as $l) {
            if (preg_match('/^(Signature[^:]*):/', $l, $m)) {
                $index = $m[1];
                $signatures[$index] = [];
                continue;
            }
            if (preg_match('/^  - ([^:]*):(.*)/', $l, $m)) {
                $signatures[$index][$m[1]] = $m[2];
            }elseif (preg_match('/^  - (.*) (document signed)/', $l, $m)) {
                $signatures[$index]["Document signed"] = $m[1];
            }
        }
        return $signatures;
    }

    public function isEnabled() {
        if (!$this->nss_directory || !$this->nss_nick) {
            return false;
        }
        return true;
    }

    public function isPDFSigConfigured() {
        if (!$this->isEnabled()) {
            return false;
        }
        if (!$this->isPDFSigInstalled()) {
            return false;
        }
        if (!$this->isCertUtilInstalled()) {
            return false;
        }

        $file = tempnam('/tmp', 'certutil');
        file_put_contents($file, $this->nss_password);
        exec('certutil -f '.$file.' -d '.$this->nss_directory.' -L -n "'.$this->nss_nick.'" 2>&1', $output, $returnCodeL);
        exec('certutil -f '.$file.' -d '.$this->nss_directory.' -K | grep ":'.$this->nss_nick.'" 2>&1', $output, $returnCodeK);
        unlink($file);

        return ($returnCodeL == 0 && $returnCodeK == 0);
    }

    public static function isCertUtilInstalled() {
        $output = null;
        $returnCode = null;
        exec('certutil -v 2>&1', $output, $returnCode);
        if ($returnCode != 1) {
            return false;
        }
        return "OK";
    }


    public static function isPDFSigInstalled() {
        $output = null;
        $returnCode = null;
        exec('pdfsig -v 2>&1', $output, $returnCode);

        if ($returnCode != 0) {
            return false;
        }

        $version = explode(' ', $output[0])[2];
        if (! $version >= "21") {
            return false;
        }
        return $version;

    }
}
