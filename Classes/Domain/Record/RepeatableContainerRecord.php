<?php

namespace UBOS\Shape\Domain\Record;

class RepeatableContainerRecord extends FieldRecord
{
	protected ?array $createdFieldsets = null;

	public function setSessionValue(mixed $value): void
	{
		$this->sessionValue = $value;
		$this->createdFieldsets = null;
	}

	public function getCreatedFieldsets(): array
	{
		if ($this->createdFieldsets !== null) {
			return $this->createdFieldsets;
		}
		$index = 0;
		if (!$this->getSessionValue()) {
			return [];
		}
		foreach ($this->getSessionValue() as $values) {
			foreach($this->get('fields') as $childField) {
				$newField = clone $childField;
				if (isset($values[$newField->getName()])) {
					$newField->setSessionValue($values[$newField->getName()]);
				}
//				$newField->validationResult = $this->validationResult->getSubResults()[$index]->getSubResults()[$newField->getName()] ?? null;
				$this->createdFieldsets[$index][] = $newField;
			}
			$index++;
		}
		return $this->createdFieldsets;
	}
}
