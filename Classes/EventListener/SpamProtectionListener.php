<?php

namespace UBOS\Shape\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\DebugUtility;
use UBOS\Shape\Domain;
use UBOS\Shape\Event\SpamProtectionEvent;

final class SpamProtectionListener
{
	#[AsEventListener]
	public function __invoke(SpamProtectionEvent $event): void
	{
		// honeypot
		if ($event->context->request->getParsedBody()['tx_shape_form']['__email'] ?? '') {
			$event->isSpam = true;
		}
	}
}
