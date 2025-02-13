<?php

namespace UBOS\Shape\Domain\FormRuntime;

use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Service\ExtensionService;

class FormContextBuilder
{
	public static function buildFromRequest(
		RequestInterface $request,
		array $settings = [
			'pluginUid' => null,
			'uploadFolder' => '1:/user_upload/',
		]
	): FormContext
	{
		$extensionService = GeneralUtility::makeInstance(ExtensionService::class);
		$pluginNamespace = $extensionService->getPluginNamespace($request->getControllerExtensionName(), $request->getControllerName());
		$contentData = self::getContentData($request, $settings);
		if (!$contentData) {
			throw new \Exception('No content data found');
		}
		$plugin = GeneralUtility::makeInstance(Core\Domain\RecordFactory::class)
			->createResolvedRecordFromDatabaseRow('tt_content', $contentData);
		$form = $plugin->get('pi_flexform')->get('settings')['form'][0] ?? null;
		if (!$form) {
			throw new \Exception('No form found');
		}
		$cleanedPostValues = [];
		if (!$request->getAttribute('frontend.cache.instruction')->isCachingAllowed()) {
			$sessionData = (array)json_decode($request->getArguments()['session'] ?? '[]', true);
			try {
				$session = new FormSession(...$sessionData);
				$session->hasErrors = false;
			} catch (\Exception $e) {
				$session = new FormSession();
			}

			$session->id = $session->id ?: GeneralUtility::makeInstance(Core\Crypto\Random::class)->generateRandomHexString(40);
			// manually create values form parsed body and uploaded files because get arguments uses array_merge_recursive to merge parsed body and uploaded files
			$postValues = $request->getParsedBody()[$pluginNamespace][$form->get('name')] ?? [];
			Core\Utility\ArrayUtility::mergeRecursiveWithOverrule(
				$postValues,
				$request->getUploadedFiles()[$form->get('name')] ?? [],
			);

			$pageIndex = $request->getArguments()['pageIndex'] ?? 1;
			$isStepBack = ($session->previousPageIndex ?? 1) > $pageIndex;

			// only keep post values that can be mapped to fields
			// substitute missing values with proxy values, if possible (e.g. for file fields)
			foreach ($form->get('pages') as $page) {
				foreach ($page->get('fields') as $field) {
					if (!$field->has('name')) {
						continue;
					}
					$name = $field->getName();
					if (isset($postValues[$name.'__CONFIRM'])) {
						$cleanedPostValues[$name.'__CONFIRM'] = $postValues[$name.'__CONFIRM'];
					}
					if (isset($postValues[$name])) {
						$cleanedPostValues[$name] = $postValues[$name];
					} else if (isset($postValues[$name.'__PROXY'])) {
						$cleanedPostValues[$name] = $postValues[$name.'__PROXY'];
					}
				}
			}
		} else {
			$session = new FormSession();
			$isStepBack = false;
		}
		$uploadStorage = GeneralUtility::makeInstance(Core\Resource\StorageRepository::class)->findByCombinedIdentifier($settings['uploadFolder']);
		return new FormContext(
			$request,
			$settings,
			$plugin,
			$form,
			$session,
			$cleanedPostValues,
			$uploadStorage,
			$isStepBack
		);
	}

	protected static function getContentData(
		RequestInterface $request,
		array $settings
	): ?array
	{
		$contentData = $request->getAttribute('currentContentObject')?->data;
		if (isset($contentData['CType'])) {
			return $contentData;
		}
		$queryBuilder = GeneralUtility::makeInstance(Core\Database\ConnectionPool::class)->getQueryBuilderForTable('tt_content');
		return $queryBuilder
			->select('*')
			->from('tt_content')
			->where(
				$queryBuilder->expr()->eq('uid', (int)$request->getArgument('pluginUid') ?? $settings['pluginUid'] ?? 0)
			)
			->executeQuery()->fetchAllAssociative()[0] ?? null;
	}
}