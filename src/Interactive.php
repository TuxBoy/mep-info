<?php
namespace App;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Trait Interactive
 */
trait Interactive
{

	/**
	 * Gives choice to user from a list
	 *
	 * @param string          $step
	 * @param array           $choices
	 * @param OutputInterface $output
	 * @param bool            $interactive
	 * @return null|string
	 */
	public function choose(
		string $step, array $choices, OutputInterface $output, bool $interactive = true): ?string
	{
		if ($interactive) {
			foreach ($choices as $key => $value) {
				$output->writeln($step . ' : ' . $key . ' ' . trim($value));
			}
			$choice = -1;

			while ((intval($choice) < 0) || (intval($choice) > count($choices))) {
				$choice = trim(
					readline('[' . $step . '] Enter an option (between 0 and ' . (count($choices) -1) . ') : ')
				);
			}
			return trim($choices[(int) $choice]);
		}
		return null;
	}

}