<?php

class CryptographyClass
{
    private $symmetric_key = null;

    function __construct($key) {
            $this->setSymmetricKey($key);
    }

    public function encrypt($hash) {
        foreach (glob("/tmp/".$hash.'/*.pdf') as $file) {
            $outputFile = $file.".gpg";
            $key = $this->getSymmetricKey();
            $command = "gpg --batch --passphrase $key --symmetric --cipher-algo AES256 -o $outputFile $file";
            $result = shell_exec($command);
            if ($result === false) {
                echo "Cypher failure";
                exit;
            }
            unlink($file);
        }
    }

    public function decrypt($hash) {
        foreach (glob("/tmp/".$hash.'/*.gpg') as $file) {
            $outputFile = str_replace(".gpg", "", $file);
            $key = $this->getSymmetricKey();
            $command = "gpg --batch --passphrase $key --decrypt -o $outputFile $file";
            $result = shell_exec($command);
            if ($result === false) {
                echo "Decypher failure";
                exit;
            }
            unlink($file);
        }
        return true;
    }

    private function getSymmetricKey() {
        return $this->symmetric_key;
    }

    private function setSymmetricKey($key) {
        $this->symmetric_key = $key;
    }

}
?>
