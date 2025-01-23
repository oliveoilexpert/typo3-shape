<?php

namespace UBOS\Shape\Domain\Record;


class SingleSelectOptionFieldRecord extends GenericFieldRecord
{
	public function initialize(): void
	{
		$value = null;
		foreach ($this->get('field_options') as $option) {
			if ($option->get('selected')) {
				$value = $option->get('value');
				break;
			}
		}
		$this->properties['default_value'] = $value;
	}
}
