<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Validator;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Validation\Exception\InvalidValidationOptionsException;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

final class FileUploadValidator extends AbstractValidator
{
	protected $supportedOptions = [
	];

	public function isValid(mixed $value): void
	{
		if (!$value instanceof \TYPO3\CMS\Core\Http\UploadedFile) {
			$this->addError(
				$this->translateErrorMessage(
					'validation.error.file_upload',
					'shape',
				),
				1739395516,
			);
		}
	}
}
