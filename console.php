<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = new \Dotenv\Dotenv(__DIR__);
$dotenv->load();

$app = new \Silly\Edition\PhpDi\Application();

require __DIR__ . '/bootstrap/container.php';

$app->command('mep [--project_root=] [--branch] [--interactive]', \App\Command\Mep::class);

$app->run();