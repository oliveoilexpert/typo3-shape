<?php

declare(strict_types=1);

namespace UBOS\Shape\Event;

use TYPO3\CMS\Core;
use UBOS\Shape\Form;

final class ValueProcessingEvent
{
	public function __construct(
		public readonly Domain\FormRuntime\FormRuntime $runtime,
		public readonly Domain\Record\FieldRecord      $field,
		public readonly mixed                          $value,
		public mixed                                   $processedValue = null,
	) {}
	public function isPropagationStopped(): bool
	{
		return $this->processedValue !== null;
	}
}