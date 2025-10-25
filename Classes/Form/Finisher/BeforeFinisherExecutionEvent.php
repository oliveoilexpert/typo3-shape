<?php

declare(strict_types=1);

namespace UBOS\Shape\Form\Finisher;

final class BeforeFinisherExecutionEvent
{
	public function __construct(
		public readonly FinisherExecutionContext $context,
		public FinisherInterface               	 $finisher,
		public bool                              $cancelled = false,
	)
	{
	}
}