<?php

declare(strict_types=1);


namespace UBOS\Shape\Domain\Condition;

use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;
use UBOS\Shape\Domain\Condition\Functions\ConditionFunctionsProvider;

class ConditionProvider extends AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageProviders = [
            ConditionFunctionsProvider::class,
        ];
    }
}
