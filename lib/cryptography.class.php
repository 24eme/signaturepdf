<?php

class CryptographyClass
{
    private $symmetricKey = null;
    private $pathHash = null;

    function __construct($key, $pathHash) {
            $this->symmetricKey = $key;
            $this->pathHash = $pathHash;
    }

    private function getFiles($isGpg) {
        $suffix = "";
        if ($isGpg) {
            $suffix = ".gpg";
        }
        $filesTab = glob($this->pathHash.'/*.pdf'.$suffix);
        $filesTab[] = $this->pathHash."/filename.txt".$suffix;

        return $filesTab;
    }

    public function encrypt() {

        foreach ($this->getFiles(false) as $file) {
            $outputFile = $file.".gpg";
            $command = "gpg --batch --passphrase $this->symmetricKey --symmetric --cipher-algo AES256 -o $outputFile $file";
            $result = shell_exec($command);
            if ($result === false) {
                echo "Cypher failure";
                exit;
            }
            $this->hardUnlink($file);
        }
    }

    public function decrypt() {
        foreach ($this->getFiles(true) as $file) {
            $outputFile = str_replace(".gpg", "", $file);
            $command = "gpg --batch --passphrase $this->symmetricKey --decrypt -o $outputFile $file";
            $result = shell_exec($command);
            if ($result === false) {
                echo "Decypher failure";
                exit;
            }
            $this->hardUnlink($file);
        }
        return true;
    }

    public static function hardUnlink($element) {
        if (!$element) {
            return;
        }
        print_r(['hu', $element]);
        $eraser = str_repeat(0, strlen(file_get_contents($element)));
        file_put_contents($element, $eraser);
        unlink($element);
    }

    public static function isSymmetricKeyValid($key) {
        return (bool)preg_match('/^[0-9a-zA-Z]{15}$/', $key);
    }

    public static function createSymmetricKey() {
            $length = 15;
            $keySpace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $pieces = [];
            $max = mb_strlen($keySpace, '8bit') - 1;
            for ($i = 0; $i < $length; ++$i) {
                $pieces []= $keySpace[random_int(0, $max)];
            }

            return implode('', $pieces);
        }
}
?>
