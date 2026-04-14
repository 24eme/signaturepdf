<?php

class OCR
{
    public static function isInstalled() {
        $output = null;
        $returnCode = null;

        exec('ocrmypdf --version', $output, $returnCode);

        if (!$output) {
            return array(false);
        }
        return $output;
    }
}
