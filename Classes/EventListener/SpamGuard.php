<?php

namespace UBOS\Shape\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\DebugUtility;
use UBOS\Shape\Domain;
use UBOS\Shape\Event\SpamAnalysisEvent;

final class SpamGuard
{
	#[AsEventListener]
	public function __invoke(SpamAnalysisEvent $event): void
	{
		$parameters = $event->runtime->request->getParsedBody()[$event->runtime->parsedBodyKey] ?? [];
		// honeypot
		if ($event->runtime->settings['spamProtection']['honeypot'] && ($parameters['__email'] ?? '')) {
			$event->spamReasons['honeypot'] = [
				'message' => 'Honeypot field was filled.',
			];
		}
		// focus field must be set
		if ($event->runtime->settings['spamProtection']['focusPass'] && ($parameters['__focus_pass'] ?? '') !== 'human') {
			$event->spamReasons['focusPass'] = [
				'message' => 'Focus Pass field that fills via JavaScript on focusin event was not filled correctly.',
			];
		}
	}
}
