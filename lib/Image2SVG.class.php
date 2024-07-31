<?php

class Image2SVG
{
    public static function isimageMagickInstalled() {
        $output = null;
        $returnCode = null;

        exec('convert --version', $output, $returnCode);

        return $output;
    }

    public static function ispotraceInstalled() {
        $output = null;
        $returnCode = null;

        exec('potrace --version', $output, $returnCode);

        return $output;
    }
}
