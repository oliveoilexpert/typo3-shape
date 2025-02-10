<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Validator;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Validation\Exception\InvalidValidationOptionsException;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

final class FileExistsInStorageValidator extends AbstractValidator
{
	protected $supportedOptions = [
		'storage' => [null, 'Storage to look for file in', 'object', true],
	];

	public function isValid(mixed $value): void
	{
		$storage = $this->options['storage'];
		if (!$value || !($storage instanceof \TYPO3\CMS\Core\Resource\ResourceStorageInterface)) {
			$message = sprintf('Option "storage" must be of type %s', \TYPO3\CMS\Core\Resource\ResourceStorageInterface::class);
			throw new InvalidValidationOptionsException($message, 1739105228);
		}
		$file = $storage->getFile((string)$value);
		if (!$file || $file->isMissing()) {
			$this->addError(
				$this->translateErrorMessage(
					'validation.error.file_exists_in_storage',
					'shape',
				),
				1739105229
			);
		}

	}
}
