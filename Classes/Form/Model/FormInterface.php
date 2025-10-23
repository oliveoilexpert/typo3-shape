<?php

namespace UBOS\Shape\Form\Model;

interface FormInterface
{
	public function getUid(): int;

	public function getName(): string;

	/** @return FormPageInterface[] */
	public function getPages(): Iterable;

	/** @return FinisherConfigurationInterface[] */
	public function getFinisherConfigurations(): array;
}