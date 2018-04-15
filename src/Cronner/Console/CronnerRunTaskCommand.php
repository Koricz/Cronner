<?php

namespace stekycz\Cronner\Console;

use stekycz\Cronner\Cronner;
use stekycz\Cronner\Tasks\Task;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CronnerRunTaskCommand extends Command
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
		$this->setName('cronner:task')
			->setDescription('Run task')
			->addArgument('id', InputArgument::REQUIRED, 'Task id')
			->addOption('force', null, InputOption::VALUE_NONE, 'Force run');
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

		$now = new \DateTime();

		$tasks = $this->cronner->getTasks();

		$found = false;

		foreach ($tasks as $key => $task) {

			if (md5($key) == $input->getArgument('id')) {
				if ($input->getOption('force') === false && !$task->shouldBeRun($now)) {
					$output->writeln('<error>Task "' . $task->getName() . '" could not be run now. Next planned run: ' . $task->getNextRun()->format('d.m.Y H:i:s') . '</error>');
					$output->writeln('<info>Use <comment>--force</comment> option to run Task immediately</info>');
				} else {
					$this->cronner->runTask($task, $input->getOption('force'));
				}

				$found = true;
				break;
			}
		}

		if (!$found) {
			$output->writeln('<info>No task found</info>');
		}

		return 0;
	}
}
