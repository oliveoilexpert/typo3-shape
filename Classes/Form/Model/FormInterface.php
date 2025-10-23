<?php

namespace UBOS\Shape\Form\Model;

use TYPO3\CMS\Core\Collection\LazyRecordCollection;

interface FormInterface
{
	public function getUid(): int;

	public function getName(): string;

	/**
	 * @return LazyRecordCollection<FormPageInterface>|array<FormPageInterface>
	 */
	public function getPages(): LazyRecordCollection|array;

	/**
	 * @return LazyRecordCollection<FinisherConfigurationInterface>|array<FinisherConfigurationInterface>
	 */
	public function getFinisherConfigurations(): LazyRecordCollection|array;
}