<?php

// временный хак... надо подумать, как правильнее сделать
if (preg_match('/^\/js\/hm\/locale\/([^\/]+)\/translate\.js(\?.*)?$/', $_SERVER['REQUEST_URI'], $matches)) {

    $eTag = $matches[2];

    header('Expires: Tue, 25 Jan 2050 00:00:00 GMT');
    header('Cache-Control: public, max-age=2592000');

    if (isset($_SERVER["HTTP_IF_NONE_MATCH"]) && $eTag === $_SERVER["HTTP_IF_NONE_MATCH"]) {
        header('HTTP/1.1 304 Not Modified');
        die;
    }

    header('Content-Type: text/javascript');
    header('ETag: '.$eTag);

    $fileName = realpath(dirname(__FILE__).'/../../data/cache/locale/'.$matches[1].'/translate.js');
    readfile($fileName);
    die;
}
