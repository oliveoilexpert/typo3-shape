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
				if (isset($values[$newField->get('name')])) {
					$newField->setSessionValue($values[$newField->get('name')]);
				}
				$this->createdFieldsets[$index][] = $newField;
			}
			$index++;
		}
		return $this->createdFieldsets;
	}
}
