<?php

namespace UBOS\Shape\Domain\Record;

class DatetimeFieldRecord extends GenericFieldRecord
{
	const array FORMATS = [
		'date' => 'Y-m-d',
		'time' => 'H:i',
		'datetime' => 'Y-m-d H:i',
		'datetime-local' => 'Y-m-d\TH:i',
		'week' => 'Y-\WW',
		'month' => 'Y-m',
	];

	public array $dateTimeProperties = ['default_value', 'min', 'max'];

	public function initialize(): void
	{
		foreach($this->dateTimeProperties as $key) {
			$value = $this->get($key);
			if ($value instanceof \DateTimeInterface) {
				$this->properties[$key] = $this->properties[$key]->format(self::FORMATS[$this->get('type')]);
			}
		}
	}
}
