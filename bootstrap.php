<?php

define( "ROOT_PATH", __DIR__ );

require __DIR__ . "/vendor/autoload.php";

use BasePoster\Poster;

$poster = new Poster();

echo $poster->sendMessage();