<?php

declare(strict_types=1);

namespace UBOS\Shape\Event;

use UBOS\Shape\Domain;

final class BeforeFormRenderEvent
{
	public function __construct(
		public readonly Domain\FormRuntime\FormRuntime $context,
		protected array                                $variables = [],
	) {}
	public function getVariables(): array
	{
		return $this->variables;
	}
	public function setVariable(string $key, $value): void
	{
		$this->variables[$key] = $value;
	}
	public function setVariables(array $variables): void
	{
		$this->variables = array_merge($this->variables, $variables);
	}
}