<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Condition\Functions;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use TYPO3\CMS\Core\Utility\DebugUtility;
use UBOS\Shape\Enum;

class ConditionFunctionsProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            $this->getFormValueFunction(),
			$this->getFormValueFunction('value'),
			$this->getIsConsentApprovedFunction(),
			$this->getIsConsentDeclinedFunction(),
			$this->getIsBeforeConsentFunction(),
        ];
    }

    protected function getFormValueFunction(string $name = 'getFormValue'): ExpressionFunction
    {
        return new ExpressionFunction(
            $name,
            static fn() => null, // Not implemented, we only use the evaluator
            static function ($arguments, $field, $default = null) {
                return $arguments['formValues'][$field] ?? $default;
            }
        );
    }

	protected function getIsConsentApprovedFunction(): ExpressionFunction
	{
		return new ExpressionFunction(
			'isConsentApproved',
			static fn() => null, // Not implemented, we only use the evaluator
			static function ($arguments, $default = '') {
				return ($arguments['consentStatus'] ?? '') === Enum\ConsentStatus::Approved;
			}
		);
	}

	protected function getIsConsentDeclinedFunction(): ExpressionFunction
	{
		return new ExpressionFunction(
			'isConsentDeclined',
			static fn() => null, // Not implemented, we only use the evaluator
			static function ($arguments, $default = '') {
				return ($arguments['consentStatus'] ?? '') === Enum\ConsentStatus::Declined;
			}
		);
	}

	protected function getIsBeforeConsentFunction(): ExpressionFunction
	{
		return new ExpressionFunction(
			'isBeforeConsent',
			static fn() => null, // Not implemented, we only use the evaluator
			static function ($arguments, $default = '') {
				return ($arguments['consentStatus'] ??  Enum\ConsentStatus::Pending) === Enum\ConsentStatus::Pending;
			}
		);
	}

}
