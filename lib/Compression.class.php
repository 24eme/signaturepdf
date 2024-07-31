<?php

class Compression
{
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
