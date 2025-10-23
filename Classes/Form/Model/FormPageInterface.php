<?php

namespace UBOS\Shape\Form\Model;

interface FormPageInterface
{
	public function getType(): string;

	/** @return FieldInterface[] */
	public function getFields(): array;
}