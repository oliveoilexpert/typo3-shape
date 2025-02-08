<?php

namespace UBOS\Shape\Command;

use Symfony\Component\Console;
use Symfony\Component\Console\Command\Command;
use UBOS\Shape\Service\DatabaseService;


#[Console\Attribute\AsCommand(
	name: 'shape:mirror-page-field-relations',
	description: 'Mirror page field relation fields based on current tx_shape_form_page.fields config type.',
)]
class MirrorPageFieldRelationsCommand extends Command
{
	public function __construct(
		private readonly DatabaseService $databaseService
	) {
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setHelp('Mirror page field relation fields based on current tx_shape_form_page.fields config type.',);
	}

	protected function execute(
		Console\Input\InputInterface $input,
		Console\Output\OutputInterface $output,
	): int {
		$this->databaseService->mirrorCurrentPageFieldRelations();
		return Command::SUCCESS;
	}
}