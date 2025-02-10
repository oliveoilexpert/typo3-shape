<?php

declare(strict_types=1);

namespace UBOS\Shape\Event;

use TYPO3\CMS\Core;
use UBOS\Shape\Domain;

final class FieldResolveConditionEvent
{
	public function __construct(
		public readonly Domain\FormContext $context,
		public readonly Domain\Record\FieldRecord $field,
		public readonly Core\ExpressionLanguage\Resolver $resolver,
		public ?bool $result = null,
	) {}

	public function isPropagationStopped(): bool
	{
		return $this->result !== null;
	}
}