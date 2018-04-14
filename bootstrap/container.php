<?php

use Psr\Container\ContainerInterface;
use RestCord\DiscordClient;

/** @var $container ContainerInterface|\DI\Container */
$container = $app->getContainer();

$container->set('config', function () {
	return require __DIR__ . '/app.php';
});

$container->set(DiscordClient::class, function (ContainerInterface $container) {
	return new DiscordClient(['token' => $container->get('config')['discord_token']]);
});

