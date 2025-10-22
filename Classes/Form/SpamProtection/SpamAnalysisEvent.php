<?php

declare(strict_types=1);

namespace UBOS\Shape\Form\SpamProtection;

use UBOS\Shape\Form;

final class SpamAnalysisEvent
{
	public function __construct(
		public readonly Form\FormRuntime $runtime,
		public array                     $spamReasons = [],
	)
	{
	}
}