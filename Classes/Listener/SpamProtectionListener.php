<?php

namespace UBOS\Shape\Listener;

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
		$arguments = $event->context->request->getParsedBody()['tx_shape_form'] ?? [];
		// honeypot
		if ($event->context->settings['spamProtection']['honeypot'] && $arguments['__email'] ?? '') {
			$event->spamReasons['honeypot'] = [
				'message' => 'Honeypot field was filled.',
			];
		}
		// focus field must be set
		if ($event->context->settings['spamProtection']['focusPass'] && ($arguments['__focus_pass'] ?? '') !== 'human') {
			$event->spamReasons['focusPass'] = [
				'message' => 'Focus Pass field that fills via JavaScript on focusin event was not filled correctly.',
			];
		}
	}
}
