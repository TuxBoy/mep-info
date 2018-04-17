<?php
namespace App;

/**
 * App Bash command exec
 */
abstract class Bash
{

	/**
	 * @param string $command
	 * @return string|null
	 */
	public static function run(string $command): ?string
	{
		return shell_exec($command);
	}

	/**
	 * @param string $command
	 * @return array
	 */
	public static function runByArray(string $command): array
	{
		return explode("\n", static::run($command));
	}

}