<?php

class CryptographyClass
{
    public function encrypt($hash) {
        $key = "test";
        foreach (glob("/tmp/".$hash.'/*.pdf') as $file) {
            $outputFile = $file.".gpg";
            $command = "echo '$key' | gpg --batch --passphrase-fd 0 --symmetric --cipher-algo AES256 -o $outputFile $file";
            $result = shell_exec($command);
            if ($result === false) {
                echo "Cypher failure";
                exit;
            }
            unlink($file);
        }
    }

    public function decrypt($hash) {
        $key = "test";
        foreach (glob("/tmp/".$hash.'/*.gpg') as $file) {
            $outputFile = str_replace(".gpg", "", $file);
            $command = "echo '$key' | gpg --batch --passphrase-fd 0 --decrypt -o $outputFile $file";
            $result = shell_exec($command);
            if ($result === false) {
                echo "Decypher failure";
                exit;
            }
            unlink($file);
        }
    }

}
?>
