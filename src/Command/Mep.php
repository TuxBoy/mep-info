<?php
namespace App\Command;

use App\Channels;
use Github\Client;
use Psr\Container\ContainerInterface;
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
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * Mep constructor
	 *
	 * @param DiscordClient      $discord
	 * @param Client             $github
	 * @param ContainerInterface $container
	 */
	public function __construct(DiscordClient $discord, Client $github, ContainerInterface $container)
	{
		$this->discord   = $discord;
		$this->github    = $github;
		$this->container = $container;
	}

	/**
	 * @param string          $branch
	 * @param OutputInterface $output
	 */
	public function __invoke(string $branch, OutputInterface $output): void
	{
		$config  = $this->container->get('config');
		$commits = $this->github->api('repo')
			->commits()
			->all($config['github_username'], $config['repo_name'], ['sha' => $branch]);
		$lastCommit = current($commits);
		$content    = $this->createReport($lastCommit);
		$this->discord->channel->createMessage([
			'channel.id' => $config['app_debug'] ? Channels::TEST_BOT : Channels::MEP,
			'content'    => $content
		]);

		$output->writeln("Mep ok sur la branche $branch");
	}

	/**
	 * TODO Better render for the table
	 *
	 * @param array $lastCommit
	 * @return string
	 */
	private function createReport(array $lastCommit): string
	{
		$report = ' MEP [360-dev](https://github.com/Oipnet/360-dev/) prod' . "\n";
		$report .= "\n";
		$report .= "| ----------------------------------------------------------- |\n";
		$report .= "| **Username**    |  **Commit**          | **Commit url**     |\n";
		$report .= $this->line(
			$lastCommit['commit']['committer']['name'], $lastCommit['commit']['message'],
			$lastCommit['html_url']
		);
		$report .= "| ----------------------------------------------------------- |\n";

		return $report . "\n";
	}

	/**
	 * @param string $username
	 * @param string $message
	 * @param string $commit_url
	 * @return string
	 */
	private function line(string $username, string $message, string $commit_url): string
	{
		$line = "| $username |";

		$line .= " $message |";
		$line .= '| ('. $commit_url .') |';
		$line .= "\n";

		return $line;
	}

}