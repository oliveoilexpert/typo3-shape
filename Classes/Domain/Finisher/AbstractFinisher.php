<?php

namespace UBOS\Shape\Domain\Finisher;

use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use UBOS\Shape\Domain\FormRuntime;

abstract class AbstractFinisher
{
	protected array $settings = [];

	public function __construct(
		public readonly FormRuntime\FinisherContext $context,
		array                                		$settings = [],
		public readonly ?Core\Domain\Record			$record = null,
	) {
		$defaultSettings = $context->runtime->settings['finisherDefaults'][static::class] ?? [];
		$this->settings = array_merge($defaultSettings, $this->settings);
		foreach ($settings as $key => $value) {
			if ($value !== '') {
				$this->settings[$key] = $value;
			}
		}
	}

	abstract public function execute(): void;

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