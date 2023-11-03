<?php

class CryptographyClass
{
    const KEY_SIZE = 4;

    public function encrypt($hash) {
        foreach (glob("/tmp/".$hash.'/*.pdf') as $file) {
            $outputFile = $file.".gpg";
            $keyPath = $this->getKeyPath();
            $command = "gpg --batch --passphrase-file $keyPath --symmetric --cipher-algo AES256 -o $outputFile $file";
            $result = shell_exec($command);
            $this->freeKeyFile($keyPath);
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
            $keyPath = $this->getKeyPath();
            $command = "gpg --batch --passphrase-file $keyPath --decrypt -o $outputFile $file";
            $result = shell_exec($command);
            $this->freeKeyFile($keyPath);
            if ($result === false) {
                echo "Decypher failure";
                exit;
            }
            unlink($file);
        }
    }

    private function getKeyPath() {
        $path = "../key.txt";
        if (file_put_contents($path, 'test') === false)
        {
            echo "passphrase generation failure";
            exit;
        }
        return $path;
    }

    private function freeKeyFile($keyPath) {
        $passphrase_overwrite = str_repeat("0", self::KEY_SIZE);
        if (file_put_contents($keyPath, $passphrase_overwrite) === false)
        {
            echo "passphrase generation failure";
            exit;
        }
    }



}
?>
