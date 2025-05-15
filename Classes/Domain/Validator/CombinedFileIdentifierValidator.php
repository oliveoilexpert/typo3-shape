<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Validator;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

#[Autoconfigure(public: true, shared: false)]
final class CombinedFileIdentifierValidator extends AbstractValidator
{
	protected $supportedOptions = [
	];

	public function __construct(
		protected \TYPO3\CMS\Core\Resource\ResourceFactory $resourceFactory
	) {}

	public function isValid(mixed $value): void
	{
		try {
			$file = $this->resourceFactory->getFileObjectFromCombinedIdentifier($value);
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
		);
	}
}
