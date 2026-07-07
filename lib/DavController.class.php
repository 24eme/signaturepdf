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

    function saveToRemote(Base $f3) {
        $settings = [
            'baseUri' => $f3->get('DAV_REMOTE_BASE_URI'),
            'userName' => $f3->get('DAV_REMOTE_USERNAME'),
            'password' => $f3->get('DAV_REMOTE_PASSWORD'),
        ];

        $fileName = basename($f3->get('POST.file'));
        $fileContent = file_get_contents($f3->get('ROOT') . DIRECTORY_SEPARATOR . 'dav' . DIRECTORY_SEPARATOR . $fileName);

        $destinationPath = $f3->get('DAV_REMOTE_DESTINATION_PATH') . '/' . $fileName;

        $client = new DAV\Client($settings);
        $response = $client->request('PUT', $destinationPath, $fileContent);

        if ($response['statusCode'] >= 200 && $response['statusCode'] < 300) {
            echo json_encode(['message' => 'File saved to remote successfully', 'statusCode' => $response['statusCode']]);
        } else {
            echo json_encode(['message' => 'Failed to save file to remote', 'statusCode' => $response['statusCode'], 'responseBody' => $response['body']]);
        }
    }
}