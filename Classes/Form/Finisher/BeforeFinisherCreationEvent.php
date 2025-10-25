<?php

declare(strict_types=1);

namespace UBOS\Shape\Form\Finisher;

final class BeforeFinisherExecutionEvent
{
	public function __construct(
		public readonly FinisherContext $context,
		public AbstractFinisher $finisher,
		public array $settings = [],
		public bool $cancelled = false,
	) {}
}