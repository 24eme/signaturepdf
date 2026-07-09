<?php

use Sabre\DAV;

class DavController
{
    function fileGet(Base $f3) {
        $file = $f3->get('GET.file');

        $client = new DAV\Client([
            'baseUri' => $f3->get('DAV_REMOTE_BASE_URI'),
            'userName' => $f3->get('DAV_REMOTE_USERNAME'),
            'password' => $f3->get('DAV_REMOTE_PASSWORD'),
        ]);

        $response = $client->request('GET', $file);

        if ($response['statusCode'] === 200) {
            $contentType = 'application/octet-stream';
            $headers = isset($response['headers']) && is_array($response['headers']) ? $response['headers'] : [];
            $rawContentType = $headers['content-type'] ?? $headers['Content-Type'] ?? null;

            if (is_array($rawContentType) && isset($rawContentType[0])) {
                $contentType = $rawContentType[0];
            } elseif (is_string($rawContentType) && $rawContentType !== '') {
                $contentType = $rawContentType;
            }

            header('Content-Type: '.$contentType);
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            echo $response['body'];
        } else {
            $f3->error(404, "File not found");
        }
    }
}