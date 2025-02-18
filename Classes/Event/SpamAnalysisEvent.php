<?php

declare(strict_types=1);

namespace UBOS\Shape\Event;

use TYPO3\CMS\Core;
use UBOS\Shape\Domain;

final class SpamAnalysisEvent
{
	public function __construct(
		public readonly Domain\FormRuntime\FormRuntime $context,
		public array                                   $spamReasons = [],
	) {}
}