<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Validator;

use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use UBOS\Shape\Domain;

final class UniqueInSubmissionsValidator extends AbstractValidator
{
	protected $supportedOptions = [
		'fieldName' => ['', 'Name of the field to look for value in', 'string', true],
		'pluginUid' => [0, 'Plugin parent of submissions to look for value in', 'integer', false],
		'formUid' => [0, 'Form parent of submissions to look for value in', 'integer', false],
	];

	public function isValid(mixed $value): void
	{
		/** @var Domain\Repository\FormSubmissionRepository $submissionRepository */
		$submissionRepository = Core\Utility\GeneralUtility::makeInstance(Domain\Repository\FormSubmissionRepository::class);
		$isUnique = $submissionRepository->isUniqueValue(
			$this->options['fieldName'],
			$value,
			(int)$this->options['pluginUid'],
			(int)$this->options['formUid']
		);
		if (!$isUnique) {
			$this->addError(
				$this->translateErrorMessage(
					'validation.error.unique_in_submissions',
					'shape',
				),
				1739105515
			);
		}
	}
}
