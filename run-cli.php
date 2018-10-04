<?php
declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';
$config = require_once 'config.php';

use App\Application;
use App\Factories\TransactionManagerFactory;
use App\Utils;

try {
    if ($argc < 2) {
        throw new Exception('You must provide a file name as the first argument');
    }
    $fileName = $argv[1]; // get the file name argument

    // initialize objects
    $transactionManager = TransactionManagerFactory::create($config, 'promotion');
    $application = Application::newInstance($fileName, $config);

    // run the application
    $application->run(function (array $row = null) use ($transactionManager, $config) {
        echo Utils::formatOutput($transactionManager->run($row), $row, $config).PHP_EOL;
    });
} catch (\Exception $ex) {
    echo get_class($ex).' with message: ' . $ex->getMessage().PHP_EOL.'In file '.$ex->getFile().':'.$ex->getLine();
}

echo PHP_EOL;
