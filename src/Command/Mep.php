<?php
namespace App\Command;

use App\Channels;
use Github\Client;
use RestCord\DiscordClient;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * App Mep command
 */
class Mep
{

	/**
	 * @var DiscordClient
	 */
	private $discord;

	/**
	 * @var Client
	 */
	private $github;

	/**
	 * Mep constructor
	 *
	 * @param DiscordClient $discord
	 * @param Client        $github
	 */
	public function __construct(DiscordClient $discord, Client $github)
	{
		$this->discord = $discord;
		$this->github  = $github;
	}

	/**
	 * @param string          $branch
	 * @param OutputInterface $output
	 */
	public function __invoke(string $branch, OutputInterface $output): void
	{
		$commits = $this->github->api('repo')
			->commits()
			->all('TuxBoy', 'TuxBoy-Framework', ['sha' => $branch]);
		$lastCommit = current($commits);
		$this->discord->channel->createMessage([
			'channel.id' => Channels::TEST_BOT,
			'content'    => $lastCommit['commit']['message']
		]);

		$output->writeln("Mep ok sur la branche $branch");
	}

}