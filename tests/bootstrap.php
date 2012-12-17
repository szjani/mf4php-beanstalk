<?php
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    include_once __DIR__ . '/../vendor/autoload.php';
}

\lf4php\LoggerFactory::setILoggerFactory(new lf4php\nop\NOPLoggerFactory());
