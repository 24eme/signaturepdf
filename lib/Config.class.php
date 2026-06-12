<?php

setlocale(LC_ALL, "");

class Config
{
    private static $_instance = null;
    protected $f3 = null;

    public static function createInstance($f3) {
        self::$_instance = new Config($f3);

        return self::getInstance();
    }

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            throw new Exception("Instance isn't create");
        }
        return self::$_instance;
    }

    public function __construct($f3) {

        $this->f3 = $f3;

        $this->f3->set('FALLBACK', null);
        $this->f3->language(isset($this->f3->get('HEADERS')['Accept-Language']) ? $this->f3->get('HEADERS')['Accept-Language'] : '');

        session_start();

        if(getenv("DEBUG")) {
            $this->f3->set('DEBUG', getenv("DEBUG"));
        }

        $this->f3->set('SUPPORTED_LANGUAGES',
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
                'nl'  => 'Nederlands',
                'oc' => 'Occitan',
                'pl' => 'Polski',
                'ro' => 'Română',
                'ta' => 'தமிழ்',
                'tr' => 'Türkçe'
            ]);

        $this->f3->set('XFRAME', null); // Allow use in an iframe
        $this->f3->set('ROOT', __DIR__."/..");
        $this->f3->set('UI', $this->f3->get('ROOT')."/templates/");
        $this->f3->set('UPLOADS', sys_get_temp_dir()."/");
        $this->f3->set('COMMIT', $this->getCommit());

        $this->f3->config($this->f3->get('ROOT').'/config/config.ini');
        if (!$this->f3->exists('REVERSE_PROXY_URL')) {
            $this->f3->set('REVERSE_PROXY_URL', '');
        }

        if($this->f3->get('PDF_STORAGE_PATH') && !preg_match('|/$|', $this->f3->get('PDF_STORAGE_PATH'))) {
            $this->f3->set('PDF_STORAGE_PATH', $this->f3->get('PDF_STORAGE_PATH').'/');
        }

        $this->f3->set('disableOrganization', false);
        if($this->f3->get('DISABLE_ORGANIZATION')) {
            $this->f3->set('disableOrganization', $this->f3->get('DISABLE_ORGANIZATION'));
        }

        $this->f3->set('ADMIN_AUTHORIZED_IP', array_merge(["localhost", "127.0.0.1", "::1"], explode(' ', $this->f3->get('ADMIN_AUTHORIZED_IP') . '')));
        $this->f3->set('IS_ADMIN', in_array(@$_SERVER["REMOTE_ADDR"], $this->f3->get('ADMIN_AUTHORIZED_IP')));

        if ($this->f3->get('GET.lang')) {
            $this->selectLanguage($this->f3->get('GET.lang'), true);
        } elseif (isset($_COOKIE['LANGUAGE'])) {
            $this->selectLanguage($_COOKIE['LANGUAGE'], true);
        } else {
            $this->selectLanguage($this->f3->get('LANGUAGE'));
        }

        if (!$this->f3->exists('PDF_STORAGE_ENCRYPTION')) {
            $this->f3->set('PDF_STORAGE_ENCRYPTION', false);
        }

        if($this->f3->get('PDF_STORAGE_ENCRYPTION') && !GPGCryptography::isGpgInstalled()) {
            $this->f3->set('PDF_STORAGE_ENCRYPTION', false);
        }

        if ($this->f3->exists('NSS3_DIRECTORY') && $this->f3->exists('NSS3_PASSWORD') && $this->f3->exists('NSS3_NICK')) {
            NSSCryptography::getInstance($this->f3->get('NSS3_DIRECTORY'), $this->f3->get('NSS3_PASSWORD'), $this->f3->get('NSS3_NICK'));
        }


        $domain = basename(glob($this->f3->get('ROOT')."/locale/application_*.pot")[0], '.pot');

        bindtextdomain($domain, $this->f3->get('ROOT')."/locale");
        textdomain($domain);

        $this->f3->set('TRANSLATION_LANGUAGE', _("en"));
        $this->f3->set('DIRECTION_LANGUAGE', 'ltr');
        if($this->f3->get('TRANSLATION_LANGUAGE') == "ar") {
            $this->f3->set('DIRECTION_LANGUAGE', 'rtl');
        }

        if($this->f3->get('PDF_DEMO_LINK') === null || $this->f3->get('PDF_DEMO_LINK') === true) {
            if ($this->f3->get('TRANSLATION_LANGUAGE') == "ar") {
                $this->f3->set('PDF_DEMO_LINK', 'https://raw.githubusercontent.com/24eme/signaturepdf/master/tests/files/document_ar.pdf');
            } else {
                $this->f3->set('PDF_DEMO_LINK', 'https://raw.githubusercontent.com/24eme/signaturepdf/master/tests/files/document.pdf');
            }
        }
    }

    public function getApiLocalFilePath() {
        $localRootFolder = $this->f3->get('PDF_LOCAL_PATH');
        if (!$localRootFolder) {
            $this->f3->error(403);
            return false;
        }
        $pdf_path = $localRootFolder . '/' . $this->f3->get('GET.path');
        if (strpos($pdf_path, '..') !== false) {
            $this->f3->error(403);
            return false;
        }
        if (strpos(realpath($pdf_path), realpath($localRootFolder)) === false) {
            $this->f3->error(403);
            return false;
        }
        if (!file_exists($pdf_path)) {
            $this->f3->error(403);
            return false;
        }
        return $pdf_path;
    }

    public function getCommit() {
        $gitDirectory = __DIR__.'/../.git';

        if(!file_exists($gitDirectory.'/HEAD')) {

            return null;
        }

        $head = str_replace(["ref: ", "\n"], "", file_get_contents($gitDirectory.'/HEAD'));
        $commit = null;

        if(strpos($head, "refs/") !== 0) {
            $commit = $head;
        }

        if(file_exists($gitDirectory.'/'.$head)) {
            $commit = str_replace("\n", "", file_get_contents($gitDirectory.'/'.$head));
        }

        return substr($commit, 0, 7);
    }

    public function selectLanguage($lang, $putCookie = false) {
        $langSupported = null;
        foreach(explode(',', $lang) as $l) {
            if(array_key_exists($l, $this->f3->get('SUPPORTED_LANGUAGES'))) {
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

    public static function convertPHPSizeToBytes($sSize)
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


}
