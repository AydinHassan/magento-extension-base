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

//bootstrap Magento - eugrh
\Mage::app('admin');
foreach (spl_autoload_functions() as $autoloader) {
    if (is_array($autoloader) && $autoloader[0] instanceof Varien_Autoload) {
        spl_autoload_unregister($autoloader);
    }
}
//get rid of magento error handler as it swallows errors
restore_error_handler();
