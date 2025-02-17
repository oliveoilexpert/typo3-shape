<?php

declare(strict_types=1);

namespace UBOS\Shape\Event;

use TYPO3\CMS\Core;
use UBOS\Shape\Domain;

final class ValueProcessingEvent
{
	public function __construct(
		public readonly Domain\FormRuntime\Context $context,
		public readonly Domain\Record\FieldRecord  $field,
		public readonly mixed                      $value,
		public mixed                               $processedValue = null,
	) {}
	public function isPropagationStopped(): bool
	{
		return $this->processedValue !== null;
	}
}