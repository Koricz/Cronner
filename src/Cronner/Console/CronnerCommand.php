<?php

namespace stekycz\Cronner\Console;

use stekycz\Cronner\Cronner;
use stekycz\Cronner\Tasks\Task;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class CronnerCommand extends Command
{

	/**
	 * @var Cronner
	 */
	private $cronner;

	/**
	 * CronnerTaskCommand constructor.
	 * @param Cronner $cronner
	 */
	public function __construct(Cronner $cronner)
	{
		parent::__construct();

		$this->cronner = $cronner;
	}

	protected function configure()
	{
		$this->setName('cronner:run')
			->setDescription('Run all tasks');
	}


	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->cronner->onTaskFinished[] = function (Cronner $cronner, Task $task) use ($output) {
			$output->writeln('Task ' . $task->getName() . ' has been finished.');
		};

		$this->cronner->onTaskError[] = function (Cronner $cronner, \Exception $exception, Task $task) use ($output) {
			$output->writeln('<error>Task "' . $task->getName() . '" has been stoped by an error: ' . $exception->getMessage() . '</error>');
		};

		$this->cronner->run();

		$output->writeln('<info>Cronner finished</info>');

		return 0;
	}
}
