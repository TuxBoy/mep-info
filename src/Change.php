<?php
namespace App;

/**
 * A change during MEP
 */
class Change
{

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $sha;

	/**
	 * @var Change
	 */
	public $post;

	/**
	 * @var Change
	 */
	public $pre;

	/**
	 * Change constructor
	 *
	 * @param string $name
	 * @param string $sha
	 * @param Change $post
	 * @param Change $pre
	 */
	public function __construct(string $name, string $sha, ?Change $post = null, ?Change $pre = null)
	{
		$this->name = $name;
		$this->sha  = $sha;
		$this->post = $post;
		$this->pre  = $pre;
	}

	/**
	 * @return string
	 */
	public function getSha(): string
	{
		return $this->sha;
	}

}