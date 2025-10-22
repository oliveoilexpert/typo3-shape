<?php

declare(strict_types=1);

namespace UBOS\Shape\Form\Processing;

use UBOS\Shape\Form;

final class ValueProcessingEvent
{
	public function __construct(
		public readonly Form\FormRuntime        $runtime,
		public readonly Form\Record\FieldRecord $field,
		public readonly mixed                   $value,
		public mixed                            $processedValue = null,
	)
	{
	}

	public function isPropagationStopped(): bool
	{
		return $this->processedValue !== null;
	}
}