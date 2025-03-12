<?php

declare(strict_types=1);

namespace UBOS\Shape\Event;

use UBOS\Shape\Domain;

final class BeforeFinisherExecutionEvent
{
	public function __construct(
		public readonly Domain\FormRuntime\FinisherContext $context,
		public Domain\Finisher\AbstractFinisher $finisher,
		public array $settings = [],
		public bool $cancelled = false,
	) {}
}