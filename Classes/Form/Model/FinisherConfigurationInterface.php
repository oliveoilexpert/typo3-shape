<?php

namespace UBOS\Shape\Form\Model;

interface FinisherConfigurationInterface
{
	public function getFinisherClassName(): string;

	public function getSettings(): array;

	public function getCondition(): ?string;
}