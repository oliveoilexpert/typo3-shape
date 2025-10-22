<?php

declare(strict_types=1);

namespace UBOS\Shape\Form\Rendering;

use UBOS\Shape\Form;

final class BeforeFormRenderEvent
{
	public function __construct(
		public readonly Form\FormRuntime $runtime,
		protected array                  $variables = [],
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