<?php

namespace UBOS\Shape\Domain\Record;

class RepeatableContainerFieldRecord extends GenericFieldRecord
{
	protected ?array $createdFieldsets = null;

	public function setSessionValue(mixed $value): void
	{
		$this->sessionValue = $value;
		$this->createdFieldsets = null;
	}

	public function getCreatedFields(): array
	{
		if ($this->createdFieldsets !== null) {
			return $this->createdFieldsets;
		}
		$index = 0;
		foreach ($this->getSessionValue() as $values) {
			foreach($this->get('fields') as $childField) {
				$newField = clone $childField;
				if (isset($values[$newField->get('identifier')])) {
					$newField->setSessionValue($values[$newField->get('identifier')]);
				}
				$this->createdFieldsets[$index][] = $newField;
			}
			$index++;
		}
		return $this->createdFieldsets;
	}
}
