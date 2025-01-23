<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Condition\Functions;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class FormConditionFunctionsProvider implements ExpressionFunctionProviderInterface
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


//    protected function getRootFormPropertyFunction(): ExpressionFunction
//    {
//        return new ExpressionFunction(
//            'getRootFormProperty',
//            static fn() => null, // Not implemented, we only use the evaluator
//            static function ($arguments, $property) {
//                $formDefinition = $arguments['formRuntime']->getFormDefinition();
//                try {
//                    $value = ObjectAccess::getPropertyPath($formDefinition, $property);
//                } catch (\Exception) {
//                    $value = null;
//                }
//                return $value;
//            }
//        );
//    }
}
