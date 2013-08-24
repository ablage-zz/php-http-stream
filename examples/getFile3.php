<?php

// When you want to do the dumping yourself or use custom header calls like using frameworks

require_once __DIR__."/../vendor/autoload.php";

$file = '/path/to/file.mp3';

$response = new \HttpStream\Response($file, $_SERVER);

// Overwrite response-code
$responseCode = $response->getResponseCode();
header('X-PHP-Response-Code: '.$responseCode, true, $responseCode);

// Send all headers
foreach($response->getHeaders as $header) {
    header($header->getName().': '.$header->getValue());
}

// Send data to client
echo $response->getContent();
