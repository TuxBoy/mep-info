<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = new \Dotenv\Dotenv(__DIR__);
$dotenv->load();

$app = new \Silly\Edition\PhpDi\Application();

require __DIR__ . '/bootstrap/container.php';

$app->command('mep [branch]', \App\Command\Mep::class)->defaults(['branch' => 'master']);

$app->run();