<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use UBOS\Shape\Domain\FormRuntime;

abstract class AbstractFinisher
{
	public function __construct(
		protected readonly FormRuntime\FinisherContext $context,
		protected array                                $settings = []
	) {}

	abstract public function execute(): void;

	protected function getContext(): FormRuntime\FormRuntime
	{
		return $this->context->runtime;
	}
	protected function getRequest(): \TYPO3\CMS\Extbase\Mvc\RequestInterface
	{
		return $this->context->runtime->request;
	}
	protected function getPlugin(): Core\Domain\Record
	{
		return $this->context->runtime->plugin;
	}
	protected function getForm(): Core\Domain\Record
	{
		return $this->context->runtime->form;
	}
	protected function getFormValues(): array
	{
		return $this->context->runtime->session->values;
	}
}