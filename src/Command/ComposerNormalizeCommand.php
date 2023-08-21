<?php

namespace LiquidLight\ComposerNormalize\Command;

use App\Command\Base;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComposerNormalizeCommand extends Base
{
	protected static $defaultName = 'composer:normalize';

	protected function configure(): void
	{
		parent::configure();

		$this
			// the short description shown while running "php bin/console list"
			->setDescription('Composer Normalize')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		parent::execute($input, $output);

		$failures = [];

		$finder = new Finder();
		$finder->files()
			->in(getcwd() . '/app')->name('composer.json')
		;

		$files = iterator_to_array($finder);
		$files[] = getcwd() . '/composer.json';

		foreach($files as $composerFile) {
			$command = [
				'composer',
				'--working-dir', $this->path,
				'normalize',
				$composerFile,
				'--indent-size', '4',
				'--indent-style', 'space',
			];

			if ($output->isVerbose()) {
				$command[] = '-v';
			}

			if ($this->input->getOption('fix') === false) {
				$command[] = '--dry-run';
			}

			if ($this->input->getOption('whisper')) {
				$command[] = '--quiet';
			}

			$process = $this->cmd($command);
			$this->io->newLine();
			$this->outputResult($process);

			if(!$process->isSuccessful()) {
				$failures[] = $composerFile;
			}
		}


		return count($failures) ? Command::FAILURE : Command::SUCCESS;
	}
}
