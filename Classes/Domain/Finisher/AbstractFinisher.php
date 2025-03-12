<?php

namespace UBOS\Shape\Domain\Finisher;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use UBOS\Shape\Domain\FormRuntime;

#[Autoconfigure(public: true, shared: false)]
abstract class AbstractFinisher
{
	protected array $settings = [];
	protected FormRuntime\FinisherContext $context;

	final public function execute(FormRuntime\FinisherContext $context, array $settings): void
	{
		if ($context->cancelled) {
			return;
		}
		$this->context = $context;
		Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($this->settings, $settings);
		$this->executeInternal();
	}

	abstract public function executeInternal(): void;

	protected function getRuntime(): FormRuntime\FormRuntime
	{
		return $this->context->runtime;
	}
	protected function getRequest(): RequestInterface
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
	protected function getPluginSettings(): array
	{
		return $this->context->runtime->settings;
	}
	protected function getView(): Core\View\ViewInterface
	{
		return $this->context->runtime->view;
	}
}