<?php
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->register();

$kernel = \AspectMock\Kernel::getInstance();
$kernel->init([
    'debug' => true,
    'vendor' => __DIR__ . '/../vendor/',
    'includePaths' => [__DIR__ . '/../vendor/magento/magento', __DIR__ . '/../app'],
    'excludePaths' => [__DIR__]
]);

Mage::app();