<?php

namespace PhantomInstaller;

class PhantomBinary
{
    const BIN = '/usr/share/nginx/html/data_analysis/bin/phantomjs';
    const DIR = '/usr/share/nginx/html/data_analysis/bin';

    public static function getBin() {
        return self::BIN;
    }

    public static function getDir() {
        return self::DIR;
    }
}
