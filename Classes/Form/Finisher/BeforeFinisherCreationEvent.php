<?php

declare(strict_types=1);

namespace UBOS\Shape\Form\Finisher;

use UBOS\Shape\Form;

final class BeforeFinisherCreationEvent
{
	public function __construct(
		public readonly Form\FormRuntime $runtime,
		public readonly Form\Model\FinisherConfigurationInterface $finisherConfiguration,
		public string $finisherClassName,
		public array $settings,
	)
	{
	}
}