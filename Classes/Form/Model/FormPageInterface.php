<?php

namespace UBOS\Shape\Form\Model;

use TYPO3\CMS\Core\Collection\LazyRecordCollection;

interface FormPageInterface
{
	public function getType(): string;

	/**
	 * @return LazyRecordCollection<FieldInterface>|array<FieldInterface>
	 */
	public function getFields(): LazyRecordCollection|array;
}