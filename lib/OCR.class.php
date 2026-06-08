<?php

class OCR
{
    public static function isInstalled() {
        return false; // not ready

        $output = null;
        $returnCode = null;

        exec('ocrmypdf --version', $output, $returnCode);

        if (!$output) {
            return false;
        }
        return $output;
    }
}
