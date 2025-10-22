<?php

declare(strict_types=1);

namespace UBOS\Shape\Form\Finisher;

use Psr\Http\Message\ResponseInterface;
use UBOS\Shape\Form;

class FinisherContext
{
	public function __construct(
		public readonly Form\FormRuntime $runtime,
		public ?ResponseInterface        $response = null,
		public array                     $finishedActionArguments = [],
		public bool                      $cancelled = false,
	) {}
}