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
		$protection = $event->runtime->settings['spamProtection'];
		// honeypot
		if ($protection['honeypot']['enabled']
			&& ($parameters[$protection['honeypot']['fieldName']] ?? '')
		) {
			$event->spamReasons['honeypot'] = [
				'message' => 'Honeypot field was filled.',
			];
		}
		// focus field must be set
		if ($protection['focusPass']['enabled']
			&& ($parameters[$protection['focusPass']['fieldName']] ?? '') !== $protection['focusPass']['value']
		) {
			$event->spamReasons['focusPass'] = [
				'message' => 'Focus Pass field that fills via JavaScript on focusin event was not filled correctly.',
			];
		}
	}
}
