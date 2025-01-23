<?php

declare(strict_types=1);

namespace UBOS\Shape\Validation;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

final class FileExistsInStorageValidator extends AbstractValidator
{
	protected $supportedOptions = [
		'storage' => [null, 'Storage to look for file in', 'object', true],
	];

	public function isValid(mixed $value): void
	{
		$storage = $this->options['storage'];
		// todo: throw exception if storage is not set correctly
		if (!$value || !($storage instanceof \TYPO3\CMS\Core\Resource\ResourceStorageInterface)) {
			$this->addError(
				'LLL:EXT:shape/Resources/Private/Language/locallang.xlf:validator.uploadedfile.missing',
				// todo: find a better error code
				1221565130
			);
		}
		$file = $storage->getFile((string)$value);
		if (!$file || $file->isMissing()) {
			$this->addError(
				'LLL:EXT:shape/Resources/Private/Language/locallang.xlf:validator.uploadedfile.missing',
				// todo: find a better error code
				1221565130
			);
		}

	}
}
