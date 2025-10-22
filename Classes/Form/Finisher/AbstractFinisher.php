<?php

namespace UBOS\Shape\Form\Finisher;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use UBOS\Shape\Form\Runtime;
use UBOS\Shape\Utility\TemplateVariableParser;

#[Autoconfigure(public: true, shared: false)]
abstract class AbstractFinisher implements LoggerAwareInterface
{
	use LoggerAwareTrait;

	protected array $settings = [];
	protected FinisherContext $context;

	final public function execute(FinisherContext $context, array $settings): void
	{
		if ($context->cancelled) {
			return;
		}
		$this->context = $context;
		Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($this->settings, $settings);
		$this->executeInternal();
	}

	abstract public function executeInternal(): void;

	protected function getRuntime(): Runtime\FormRuntime
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

	protected function parseWithValues(string $string): string
	{
		return TemplateVariableParser::parse($string, $this->getFormValues());
	}

	/**
	 * Get minimal log context - only include form UID for correlation
	 */
	protected function getLogContext(array $additionalContext = []): array
	{
		return array_merge([
			'formUid' => $this->getForm()->getUid(),
		], $additionalContext);
	}
}