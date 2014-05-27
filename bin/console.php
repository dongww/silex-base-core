<?php
require_once __DIR__ . '/../vendor/autoload.php';
//require_once __DIR__ . '/../db/config.php';

use Symfony\Component\Console\Application;
use Dongww\Db\Dbal\Command;

$app = new Application('dbal-wrap', 'v0.0.1');
$app->add(new Command\UpdateCommand());

$app->run();