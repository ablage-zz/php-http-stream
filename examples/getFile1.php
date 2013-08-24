<?php

// Simplest way of dumping the data to the client

require_once __DIR__."/../vendor/autoload.php";

$file = '/path/to/file.mp3';

$response = new \HttpStream\Response($file, $_SERVER);
$response->flush();
