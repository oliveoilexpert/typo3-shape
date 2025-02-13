<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Validator;

use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractCompositeValidator;

/**
 * Validator to chain many validators in a conjunction (logical and), and apply them to each value in a given array.
 */
final class ArrayValuesConjunctionValidator extends AbstractCompositeValidator
{
    public function __construct()
    {
        $this->validators = new \SplObjectStorage();
        $this->validatedInstancesContainer = new \SplObjectStorage();
    }

    /**
     * Checks if all values in the given array are valid according to the validators of the conjunction.
     * Every validator has to be valid for every value to make the whole conjunction valid.
     *
     * @param mixed $value The value that should be validated
     */
    public function validate(mixed $value): Result
    {
		if (!is_array($value)) {
			throw new \InvalidArgumentException('The value must be an array.', 1622450733);
		}
        $result = new Result();
        /** @var AbstractValidator $validator */
		foreach ($value as $item) {
			foreach ($this->getValidators() as $validator) {
				$result->merge($validator->validate($item));
			}
		}
        return $result;
    }
}
