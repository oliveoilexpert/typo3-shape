<?php

declare(strict_types=1);

namespace UBOS\Shape\Form\Condition;

use TYPO3\CMS\Core\ExpressionLanguage\Resolver;
use UBOS\Shape\Form;

final class FinisherConditionResolutionEvent
{
	public function __construct(
		public readonly Form\FormRuntime          $runtime,
		public readonly Form\Model\FinisherConfigurationInterface $finisherConfiguration,
		public readonly Resolver                  $resolver,
		public ?bool                              $result = null,
	) {}
	public function isPropagationStopped(): bool
	{
		return $this->result !== null;
	}
}