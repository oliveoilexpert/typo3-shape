<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Validator;

use TYPO3\CMS\Core;
use UBOS\Shape\Domain;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

final class UniqueInTableValidator extends AbstractValidator
{
	protected $supportedOptions = [
		'table' => ['', 'Name of the table', 'string', true],
		'column' => ['', 'Name of the column to look for value in', 'string', true],
	];

	public function isValid(mixed $value): void
	{
		// todo: inject?
		/** @var Domain\Repository\GenericRepository $genericRepository */
		$genericRepository = Core\Utility\GeneralUtility::makeInstance(Domain\Repository\GenericRepository::class);
		$genericRepository->forTable($this->options['table']);

		$count = $genericRepository->countBy($this->options['column'], $value, excludeHidden: false);
		if ($count) {
			$this->addError(
				$this->translateErrorMessage(
					'validation.error.unique_in_table',
					'shape',
				),
				1739105516
			);
		}
	}
}
