<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\FormRuntime;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use UBOS\Shape\Domain;

class FinisherContext
{
	public function __construct(
		public readonly FormRuntime        $runtime,
		public ?ResponseInterface          $response = null,
		public array                       $finishedActionArguments = [],
		public bool 					   $cancelled = false,
	) {}
}