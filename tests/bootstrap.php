<?php
/**
 * Created by PhpStorm.
 * User: bao
 * Date: 2015-08-01
 * Time: 10:49 PM
 */

function includeIfExists($file)
{
    if (file_exists($file)) {
        return include $file;
    }
}

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = new \Dotenv\Dotenv(__DIR__ . '/../');
    $dotenv->load();
}

if ((!$loader = includeIfExists(__DIR__ . '/../vendor/autoload.php')) && (!$loader = includeIfExists(__DIR__ . '/../../../.composer/autoload.php'))) {
    die('You must set up the project dependencies, run the following commands:' . PHP_EOL .
        'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
        'php composer.phar install' . PHP_EOL);
}

return $loader;
