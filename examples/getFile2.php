<?php

// When headers and content need to be flushed at different times

require_once __DIR__."/../vendor/autoload.php";

$file = '/path/to/file.mp3';

$response = new \HttpStream\Response($file, $_SERVER);

// Overwrite response-code
$responseCode = $response->getResponseCode();
header('X-PHP-Response-Code: '.$responseCode, true, $responseCode);

// Send all headers
$response->getHeaders()->flush();

// Send data to client
echo $response->getContent();
