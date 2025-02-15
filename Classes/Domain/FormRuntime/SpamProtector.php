<?php

namespace UBOS\Shape\Domain\FormRuntime;

use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\DebugUtility;
use UBOS\Shape\Domain;
use UBOS\Shape\Event\ValueProcessingEvent;

class SpamProtector
{
	public function __construct(
		protected Domain\FormRuntime\FormContext $context,
		protected EventDispatcherInterface $eventDispatcher,
	)
	{
	}

	public function evaluate(): mixed
	{
		$event = new SpamProtectionEvent($this->context);
		$this->eventDispatcher->dispatch($event);
		return $event->isSpam;
	}

}