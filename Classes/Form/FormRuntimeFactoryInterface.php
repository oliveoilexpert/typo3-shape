<?php

namespace UBOS\Shape\Form;

use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

interface FormRuntimeFactoryInterface
{
	public function createFromRequest(
		RequestInterface $request,
		ViewInterface $view,
		array $settings
	): FormRuntime;
}
