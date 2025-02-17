<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use UBOS\Shape\Domain;

abstract class AbstractFinisher
{

	public function __construct(
		protected readonly FinisherRunner $runner,
		protected array $settings = []
	)
	{
	}
	abstract public function execute(): void;

	protected function getContext(): Domain\FormRuntime\Context
	{
		return $this->runner->context;
	}

	protected function getRequest(): \TYPO3\CMS\Extbase\Mvc\RequestInterface
	{
		return $this->runner->context->request;
	}
	protected function getPlugin(): Core\Domain\Record
	{
		return $this->runner->context->plugin;
	}
	protected function getForm(): Core\Domain\Record
	{
		return $this->runner->context->form;
	}
	protected function getFormValues(): array
	{
		return $this->runner->context->session->values;
	}

}