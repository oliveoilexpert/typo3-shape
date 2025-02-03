<?php

declare(strict_types=1);

namespace UBOS\Shape\Event;

use TYPO3\CMS\Extbase\Mvc\RequestInterface;

final class FormRenderEvent
{
	public function __construct(
		protected readonly RequestInterface $request,
		protected array $variables = [],
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

	public function getRequest(): RequestInterface
	{
		return $this->request;
	}
}