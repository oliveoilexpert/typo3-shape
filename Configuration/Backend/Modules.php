<?php

use UBOS\Shape\Controller;

return [
	'web_examples' => [
		'parent' => 'web',
		'position' => ['after' => 'web_list'],
		'access' => 'user',
		'workspaces' => 'live',
		'path' => '/module/web/yform',
		'labels' => 'LLL:EXT:shape/Resources/Private/Language/locallang_mod.xlf',
		'extensionName' => 'Shape',
		'iconIdentifier' => 'mimetypes-x-content-form',
		'controllerActions' => [
			Controller\ModuleController::class => [
				'flash', 'tree', 'clipboard', 'links', 'fileReference', 'fileReferenceCreate', 'count',
			],
		],
	],
];