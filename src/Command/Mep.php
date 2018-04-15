<?php
namespace App\Command;

use App\Bash;
use App\Channels;
use App\Interactive;
use Github\Api\Repo;
use Github\Client;
use Psr\Container\ContainerInterface;
use RestCord\DiscordClient;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * App Mep command
 */
class Mep
{
	use Interactive;

	/**
	 * @var string
	 */
	private $branch;

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
	 * @var string[]
	 */
	private $config = [];

	/**
	 * @var string[][]
	 */
	private $lastCommit = [];

	/**
	 * Mep constructor
	 *
	 * @param DiscordClient      $discord
	 * @param Client             $github
	 * @param ContainerInterface $container
	 */
	public function __construct(
		DiscordClient $discord,
		Client $github,
		ContainerInterface $container
	) {
		$this->discord   = $discord;
		$this->github    = $github;
		$this->container = $container;
		$this->config    = $this->container->get('config');
	}

	private function addFirstCommit(string $commitName, string $sha)
	{
		if (!array_key_exists($commitName, $this->lastCommit)) {

		}
	}

	/**
	 * @param string          $project_root
	 * @param bool            $branch
	 * @param OutputInterface $output
	 */
	public function __invoke(string $project_root, bool $branch, bool $interactive, OutputInterface $output): void
	{
		$this->pullMainProject($project_root, $branch, $interactive, $output);
		chdir($project_root);
		$this->composerUpdate();

		/** @var $repo Repo */
		$repo        = $this->github->api('repo');
		$partsBranch = explode('/', $this->branch);
		$git_branch  = end($partsBranch);
		$commits     = $repo
			->commits()
			->all($this->config['github_username'], $this->config['repo_name'], ['sha' => $git_branch]);


		$lastCommit = current($commits);
		$this->addFirstCommit($lastCommit['commit']['message'], $lastCommit['sha']);

		$content = $this->createReport($lastCommit);

		$this->discord->channel->createMessage([
			'channel.id' => ($this->config['app_debug'] === true) ? Channels::TEST_BOT : Channels::MEP,
			'content'    => $content
		]);

		$output->writeln("\033[1;33] Mep done. \033[0m\n");
	}

	/**
	 * Composer update project
	 */
	private function composerUpdate(): void
	{
		if (file_exists('composer.phar')) {
			exec('php composer.phar update --no-dev');
		}
	}

	/**
	 * Pull main project and report it
	 *
	 * @param string          $project_root
	 * @param bool            $branch
	 * @param bool            $interactive
	 * @param OutputInterface $output
	 */
	private function pullMainProject(
		string $project_root, bool $branch, bool $interactive, OutputInterface $output
	) {
		chdir($project_root);
		Bash::run('git fetch --all --prune');

		if ($branch) {
			$result = Bash::runByArray('git branch -r');
			array_walk($result, function (&$message) { $message = trim($message); });
			$result = array_filter($result);
			$output->writeln('Git : Remote branch(es) are');
			$branch = $this->choose('Git', array_reverse($result), $output, $interactive);
		} else {
			$branch = 'origin/master';
		}

		$output->writeln('Git : Starting MEP with branch/commit ' . $branch);
		// Detect current revision (before update)
		$previousRevisionHash = Bash::run('git rev-parse HEAD');
		$output->writeln('Git : pre MEP ' . $previousRevisionHash);
		Bash::run('git checkout ' . $branch);

		// Detect current revision (after update)
		$commitIdAfter = Bash::run('git rev-parse HEAD');
	}

	/**
	 * TODO Better render for the table
	 *
	 * @param array $lastCommit
	 * @return string
	 */
	private function createReport(array $lastCommit): string
	{
		$report = ' MEP [360-dev]('. $this->config['github_url'] .') prod' . "\n";
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