<?php

declare(strict_types=1);


namespace UBOS\Shape\Domain\Condition;

use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;
use UBOS\Shape\Domain\Condition\Functions\FormConditionFunctionsProvider;

class ConditionProvider extends AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageProviders = [
            FormConditionFunctionsProvider::class,
        ];
    }
}
