<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;

abstract class AbstractFinisher
{
	protected array $settings = [];
	public function __construct(
		protected Extbase\Mvc\RequestInterface $request,
		protected Core\View\ViewInterface $view,
		protected array $pluginSettings,
		protected Core\Domain\Record $contentRecord,
		protected Core\Domain\Record $formRecord,
		protected Core\Domain\Record $finisherRecord,
		protected array $formValues,
	)
	{
		$flexFormService = Core\Utility\GeneralUtility::makeInstance(Core\Service\FlexFormService::class);
		$this->settings = $flexFormService->convertFlexFormContentToArray($finisherRecord->getRawRecord()->get('settings'));
	}

	abstract public function execute(): ?ResponseInterface;

}