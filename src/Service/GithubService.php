<?php
namespace App\Service;

use Github\Api\Repo;
use Github\Client;

class GithubService
{

	/**
	 * @var Client
	 */
	private $client;

	/**
	 * GithubService constructor
	 *
	 * @param Client $client
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * @return Repo
	 */
	public function getRepo(): Repo
	{
		return $this->client->api('repo');
	}

}