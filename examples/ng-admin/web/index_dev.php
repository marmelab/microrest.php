<?php

if (preg_match('#^/admin#', $_SERVER["REQUEST_URI"])) {
    if ('/admin' === $_SERVER["REQUEST_URI"]) {
        header("Location: /admin/");

        exit;
    }
    return false; // serve the requested resource as-is.
}

use Symfony\Component\Debug\Debug;

require_once __DIR__.'/../vendor/autoload.php';

Debug::enable();

$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../config/dev.php';

$app->run();
