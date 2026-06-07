<?php

require(__DIR__.'/lib/Config.class.php');
require(__DIR__.'/lib/GPGCryptography.class.php');
require(__DIR__.'/lib/NSSCryptography.class.php');
require(__DIR__.'/lib/PDFSignature.class.php');
require(__DIR__.'/lib/Image2SVG.class.php');
require(__DIR__.'/lib/Compression.class.php');
require(__DIR__.'/lib/OCR.class.php');
require(__DIR__.'/lib/MainController.class.php');
require(__DIR__.'/lib/ApiController.class.php');

$f3 = require(__DIR__.'/vendor/fatfree/base.php');

Config::createInstance();

$f3->route('GET|HEAD @index: /', 'MainController->index');
$f3->route('GET @signature: /signature', 'MainController->signature');
$f3->route('GET @signature_hash: /signature/@hash', 'MainController->signatureHash');
$f3->route('POST @image2svg: /image2svg', 'MainController->image2svg');
$f3->route('POST @sign: /sign', 'MainController->sign');
$f3->route('POST @share: /share', 'MainController->share');
$f3->route('GET @signature_deletion: /signature/@hash/delete/@key', 'MainController->signatureDeletion');
$f3->route('GET @signature_pdf: /signature/@hash/pdf', 'MainController->signaturePdf');
$f3->route('POST @signature_save: /signature/@hash/save', 'MainController->signatureSave');
$f3->route('GET @signature_nblayers: /signature/@hash/nblayers', 'MainController->signatureNblayers');
$f3->route('GET @cron: /cron', 'MainController->cron');
$f3->route('GET @organization: /organization', 'MainController->organization');
$f3->route('GET @metadata: /metadata', 'MainController->metadata');
$f3->route('POST @ocr: /ocr', 'MainController->ocr');
$f3->route('GET @administration: /administration', 'MainController->administration');
$f3->route('GET @compression: /compress', 'MainController->compression');
$f3->route('POST @compress: /compress', 'MainController->compress');

$f3->route('GET @api_file_get: /api/file/get', 'ApiController->fileGet');
$f3->route('GET @api_file_save: /api/file/get', 'ApiController->fileSave');
$f3->route('POST @api_share_new: /api/share/new', 'ApiController->shareNew');
$f3->route('GET @api_share_get: /api/share/get/@hash/@symmkey', 'ApiController->shareGet');
$f3->route('GET @api_share_delete: /api/share/delete/@hash/@adminkey', 'ApiController->shareDelete');

return $f3;
