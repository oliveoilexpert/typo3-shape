<?php

declare(strict_types=1);

namespace UBOS\Shape\Event;

use TYPO3\CMS\Core;
use UBOS\Shape\Domain;

final class FieldConditionResolutionEvent
{
	public function __construct(
		public readonly Domain\FormRuntime\Context       $context,
		public readonly Domain\Record\FieldRecord        $field,
		public readonly Core\ExpressionLanguage\Resolver $resolver,
		public ?bool                                     $result = null,
	) {}
	public function isPropagationStopped(): bool
	{
		return $this->result !== null;
	}
}