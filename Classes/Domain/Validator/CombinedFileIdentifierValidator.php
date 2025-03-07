<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Validator;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

final class CombinedFileIdentifierValidator extends AbstractValidator
{
	protected $supportedOptions = [
	];

	public function isValid(mixed $value): void
	{
		$resourceFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\ResourceFactory::class);
		try {
			$file = $resourceFactory->getFileObjectFromCombinedIdentifier($value);
			if (!$file) {
				$this->addDefaultError();
			}
		} catch (\Exception $e) {
			$this->addDefaultError();
		}
	}

	public function addDefaultError(): void
	{
		$this->addError(
			$this->translateErrorMessage(
				'validation.error.combined_file_identifier',
				'shape',
			),
			1739105229
		);	}
}
