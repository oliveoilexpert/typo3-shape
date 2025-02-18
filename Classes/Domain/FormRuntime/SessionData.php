<?php

namespace UBOS\Shape\Domain\FormRuntime;
class SessionData
{
	public function __construct(
		public string $id = '',
		public array $values = [],
		public int $previousPageIndex = 1,
	)
	{}
}