<?php

namespace stekycz\Cronner\Console;

use stekycz\Cronner\Cronner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class CronnerListCommand extends Command
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
		$this->setName('cronner:list')
			->setDescription('List all registered tasks');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$tasks = $this->cronner->getTasks();

		$table = new \Symfony\Component\Console\Helper\Table($output);

		$table->setHeaders(['Task', 'Id', 'Last run', 'Next run']);

		$now = new \DateTime();

		if (count($tasks) == 0) {
			$output->writeln('<info>No task found</info>');
			return 0;
		}

		foreach ($tasks as $key => $task) {

			$lastRun = $task->getLastRun();
			$nextRun = $task->getNextRun($now);

			$table->addRow(
				[
					$key,
					md5($key),
					$lastRun === null ? 'N/A' : $task->getLastRun()->format('d.m.Y H:i:s'),
					$nextRun->format('d.m.Y H:i:s') . ($now == $nextRun ? ' (now)' : '')
				]
			);
		}

		$table->render();

		return 0;
	}
}
