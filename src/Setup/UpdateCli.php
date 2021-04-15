<?php

/**
 * Class that registers WPCLI command for Setup.
 *
 * @package EightshiftLibs\Setup
 */

declare(strict_types=1);

namespace EightshiftLibs\Setup;

use EightshiftLibs\Cli\AbstractCli;
use WP_CLI\ExitException;

/**
 * Class UpdateCli
 */
class UpdateCli extends AbstractCli
{
	public const COMMAND_NAME = 'run_update';

	/**
	 * Get WPCLI command name
	 *
	 * @return string
	 */
	public function getCommandName(): string
	{
		return self::COMMAND_NAME;
	}

	/**
	 * Get WPCLI command doc.
	 *
	 * @return array
	 */
	public function getDoc(): array
	{
		return [
			'shortdesc' => 'Run project update with details stored in setup.json file.',
			'synopsis' => [
				[
					'type' => 'assoc',
					'name' => 'setup_file_path',
					'description' => 'Specify the path of the setup.json file (optional)',
					'optional' => true,
				],
			],
		];
	}

	public function __invoke(array $args, array $assocArgs) // phpcs:ignore
	{
		require_once $this->getLibsPath('src/Setup/Setup.php');

		$setupFilename = 'setup.json';

		if (getenv('TEST') !== false) {
			$setupFilename = $this->getProjectConfigRootPath() . '/cliOutput/setup.json';
		}

		try {
			setup(
				$this->getProjectConfigRootPath(),
				[
					'skip_core' => $assocArgs['skip_core'] ?? false,
					'skip_plugins' => $assocArgs['skip_plugins'] ?? false,
					'skip_plugins_core' => $assocArgs['skip_plugins_core'] ?? false,
					'skip_plugins_github' => $assocArgs['skip_plugins_github'] ?? false,
					'skip_themes' => $assocArgs['skip_themes'] ?? false,
				],
				$setupFilename
			);
		} catch (ExitException $e) {
			exit("{$e->getCode()}: {$e->getMessage()}");
		}
	}
}
