<?php

if (!$loader = include __DIR__ . '/../vendor/autoload.php') {
    die('You must set up the project dependencies.');
}

defined('ROOT_DIR') or define('ROOT_DIR', realpath(__DIR__ . '/../..'));

// PHPUnit future-compatibility, just in case
if (!class_exists('\PHPUnit_Framework_TestCase') &&
    class_exists('\PHPUnit\Framework\TestCase')
) {
    class PHPUnit_Framework_TestCase extends \PHPUnit\Framework\TestCase
    {
    }
}
