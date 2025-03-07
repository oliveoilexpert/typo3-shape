<?php

declare(strict_types=1);

namespace UBOS\Shape\Event;

use UBOS\Shape\Domain;

final class BeforeFinisherExecutionEvent
{
	public function __construct(
		public Domain\Finisher\AbstractFinisher $finisher,
		public bool $cancelled = false,
	) {}
}