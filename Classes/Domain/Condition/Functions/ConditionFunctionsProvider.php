<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Condition\Functions;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class ConditionFunctionsProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            $this->getFormValueFunction(),
			$this->getFormValueFunction('value'),
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

}
