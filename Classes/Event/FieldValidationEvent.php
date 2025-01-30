<?php

declare(strict_types=1);

namespace UBOS\Shape\Event;

use TYPO3\CMS\Form;
use TYPO3\CMS\Core;

final class FieldValidationEvent
{
	public function __construct(
		private readonly Core\Domain\RecordInterface $field,
	) {}

}