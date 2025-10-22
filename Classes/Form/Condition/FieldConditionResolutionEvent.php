<?php

declare(strict_types=1);

namespace UBOS\Shape\Form\Condition;

use TYPO3\CMS\Core;
use UBOS\Shape\Form;

final class FieldConditionResolutionEvent
{
	public function __construct(
		public readonly Form\Runtime\FormRuntime         $runtime,
		public readonly Form\Record\FieldRecord          $field,
		public readonly Core\ExpressionLanguage\Resolver $resolver,
		public ?bool                                     $result = null,
	) {}
	public function isPropagationStopped(): bool
	{
		return $this->result !== null;
	}
}