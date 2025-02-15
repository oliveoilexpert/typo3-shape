<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Validator;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Validation\Exception\InvalidValidationOptionsException;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

final class HTMLPatternValidator extends AbstractValidator
{
	protected $supportedOptions = [
		'pattern' => ['', 'HTML pattern attribute string to validate against.', 'string'],
	];

	public function isValid(mixed $value): void
	{
		$pattern = $this->options['pattern'];
		if (!$pattern) {
			return;
		}
		$regex = $this->htmlPatternToPhpRegex($pattern);
		DebugUtility::debug($regex);
		$result = preg_match($regex, $value);
		if ($result === 0) {
			$this->addError(
				$this->translateErrorMessage(
					'validation.error.html_pattern',
					'shape',
				),
				1739395515,
			);
		}
		if ($result === false) {
			throw new InvalidValidationOptionsException('PatternValidator regular expression "' . $regex . '" contained an error.', 1739395516);
		}
	}

	public function htmlPatternToPhpRegex(string $htmlPattern): string {
		// Convert Unicode escape sequences
		$htmlPattern = preg_replace_callback('/\\\\u([0-9A-Fa-f]{4})/', function($matches) {
			return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
		}, $htmlPattern);

		// Escape forward slashes since we're using them as delimiters
		$htmlPattern = str_replace('/', '\\/', $htmlPattern);

		// Add delimiters and unicode modifier
		return '/^' . $htmlPattern . '$/u';
	}

}
