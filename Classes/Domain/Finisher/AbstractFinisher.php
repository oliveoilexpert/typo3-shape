<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;

abstract class AbstractFinisher
{
	protected array $settings = [];
	public function __construct(
		protected \UBOS\Shape\Domain\FormRuntime\FormContext $context,
		protected Core\Domain\Record $finisher,
		protected Core\View\ViewInterface $view,
	)
	{
		$this->settings = Core\Utility\GeneralUtility::makeInstance(Core\Service\FlexFormService::class)
			->convertFlexFormContentToArray($this->finisher->getRawRecord()->get('settings'));
	}

	abstract public function execute(): ?ResponseInterface;

}