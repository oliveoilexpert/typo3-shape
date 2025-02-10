<?php

namespace UBOS\Shape\Domain\FormRuntime;
class FormSession
{
	public function __construct(
		public string $id = '',
		public array $values = [],
		public array $filenames = [],
		public bool $hasErrors = false,
		public int $previousPageIndex = 1,
	)
	{}
}