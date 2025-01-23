<?php

namespace UBOS\Shape\Domain\Record;


class MultiSelectOptionFieldRecord extends GenericFieldRecord
{
	protected ?array $selectedOptions = null;

	public function initialize(): void
	{
		$value = [];
		foreach ($this->get('field_options') as $option) {
			if ($option->get('selected')) {
				$value[] = $option->get('value');
			}
		}
		$this->properties['default_value'] = $value;
	}

	public function setSessionValue(mixed $value): void
	{
		$this->sessionValue = $value;
		$this->selectedOptions = null;
	}

	public function getSelectedOptions(): ?array
	{
		if ($this->selectedOptions !== null) {
			return $this->selectedOptions;
		}
		$selectedOptions = [];
		$value = $this->getSessionValue();
		if (is_array($value)) {
			foreach ($value as $val) {
				$selectedOptions[$val] = $val;
			}
		} else {
			$selectedOptions[$value] = $value;
		}
		return $selectedOptions;
	}
}
