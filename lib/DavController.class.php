<?php

use Sabre\DAV;

class DavController
{
    function index(Base $f3) {
        // Normalize the request URI with a trailing slash so requests with query parameters stay in the base URI.
        if (isset($_SERVER['REQUEST_URI']) && preg_match('#^/dav\?(.*)$#', $_SERVER['REQUEST_URI'], $matches)) {
            $_SERVER['REQUEST_URI'] = '/dav/?'.$matches[1];
        }

        $rootDirectoryPath = rtrim($f3->get('ROOT'), '/').'/dav';
        $lockFilePath = rtrim($f3->get('ROOT'), '/').'/dav-data/locks';
        $lockDirectoryPath = dirname($lockFilePath);

        if (!is_dir($rootDirectoryPath)) {
            mkdir($rootDirectoryPath, 0775, true);
        }
        if (!is_dir($lockDirectoryPath)) {
            mkdir($lockDirectoryPath, 0775, true);
        }

        $rootDirectory = new DAV\FS\Directory($rootDirectoryPath);

        $server = new DAV\Server($rootDirectory);
        $server->setBaseUri('/dav');

        $lockBackend = new DAV\Locks\Backend\File($lockFilePath);
        $lockPlugin = new DAV\Locks\Plugin($lockBackend);
        $server->addPlugin($lockPlugin);

        $server->addPlugin(new DAV\Browser\Plugin());

        $server->start();
    }
}