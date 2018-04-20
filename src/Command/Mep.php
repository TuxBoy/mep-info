<?php
namespace App\Command;

use App\Bash;
use App\Change;
use App\Channels;
use App\Interactive;
use App\Service\GithubService;
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
	 * @var Change[]
	 */
	private $changes = [];

	/**
	 * @var GithubService
	 */
	private $githubService;

	/**
	 * Mep constructor
	 *
	 * @param DiscordClient      $discord
	 * @param Client             $github
	 * @param GithubService      $githubService
	 * @param ContainerInterface $container
	 */
	public function __construct(
		DiscordClient $discord,
		Client $github,
		GithubService $githubService,
		ContainerInterface $container
	) {
		$this->discord       = $discord;
		$this->github        = $github;
		$this->container     = $container;
		$this->config        = $this->container->get('config');
		$this->githubService = $githubService;
	}

	/**
	 * @param string $name
	 * @param string $sha
	 */
	private function addPostCommit(string $name, string $sha)
	{
		if (!array_key_exists($name, $this->changes)) {
			$this->changes[$name] = new Change($name, $sha);
		}
		$this->changes[$name]->post = new Change($name, $sha);
	}

	/**
	 * @param string $name
	 * @param string $sha
	 */
	private function addPreCommit(string $name, string $sha)
	{
		if (!array_key_exists($name, $this->changes)) {
			$this->changes[$name] = new Change($name, $sha);
		}
		$this->changes[$name]->pre = new Change($name, $sha);
	}

	/**
	 * @param string          $project_root
	 * @param bool            $branch
	 * @param bool            $interactive
	 * @param OutputInterface $output
	 */
	public function __invoke(string $project_root, bool $branch, bool $interactive, OutputInterface $output): void
	{
		$this->pullMainProject($project_root, $branch, $interactive, $output);
		chdir($project_root);
		$this->composerUpdate();

		$partsBranch = explode('/', $this->branch);
		$git_branch  = end($partsBranch);
		$commits     = $this->githubService->getRepo()
			->commits()
			->all($this->config['github_username'], $this->config['repo_name'], ['sha' => $git_branch]);


		$lastCommit = current($commits);
		$content    = $this->createReport($lastCommit);

		$this->discord->channel->createMessage([
			'channel.id' => ($this->config['env'] === 'dev') ? Channels::TEST_BOT : Channels::MEP,
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
			Bash::run('php composer.phar update --no-dev');
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
		// TODO See if the chdir here is really useful (Show warning in the server)
		// chdir($project_root);
		Bash::run('git fetch --all --prune');

		if ($branch) {
			$result = Bash::runByArray('git branch -r');
			array_walk($result, function (&$message) { $message = trim($message); });
			$result = array_filter($result);
			$output->writeln('Git : Remote branch(es) are');
			$this->branch = $this->choose('Git', array_reverse($result), $output, $interactive);
		} else {
			$this->branch = 'origin/master';
		}

		$output->writeln('Git : Starting MEP with branch/commit ' . $this->branch);
		// Detect current revision (before update)
		$previousRevisionHash = Bash::run('git rev-parse HEAD');
		$this->addPreCommit('360-dev', $previousRevisionHash);
		$output->writeln('Git : pre MEP ' . $previousRevisionHash);
		Bash::run('git checkout ' . $branch);
		Bash::run('git pull');

		// Detect current revision (after update)
		$commitIdAfter = Bash::run('git rev-parse HEAD');
		$this->addPostCommit('360-dev', $commitIdAfter);
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
		// Get diff between two commits
		$changesToReport = array_filter($this->changes, function (Change $change) {
			return $change->pre->getSha() !== $change->post->getSha();
		});
		if (count($changesToReport) === 0) {
			$report .= "\t\t **No changes.** \n";
		} else {

			$report .= "| **Username**    |  **Commit**          | **Commit url**     |\n";
			$report .= $this->line(
				$lastCommit['commit']['committer']['name'], $lastCommit['commit']['message'],
				$lastCommit['html_url']
			);
		}
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