<?php

declare(strict_types=1);

namespace UBOS\Shape\Form\Serialization;

use UBOS\Shape\Form;

final class ValueSerializationEvent
{
	public function __construct(
		public readonly Form\FormRuntime        $runtime,
		public readonly Form\Record\FieldRecord $field,
		public readonly mixed                   $value,
		public mixed                            $serializedValue = null,
	)
	{
	}

	public function isPropagationStopped(): bool
	{
		return $this->serializedValue !== null;
	}
}