<?php

namespace UBOS\Shape\Domain\FormRuntime;

use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

class FormRuntimeBuilder
{
	public static function buildFromRequest(
		RequestInterface $request,
		array $settings = [
			'pluginUid' => 0,
			'uploadFolder' => '1:/user_upload/',
		]
	): FormRuntime
	{
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
		$uploadStorage = GeneralUtility::makeInstance(Core\Resource\StorageRepository::class)->findByCombinedIdentifier($settings['uploadFolder']);
		$cleanedPostValues = [];
		$parsedBodyKey = 'tx_shape_form';
		if (
			($request->getParsedBody()[$parsedBodyKey] ?? false)
			&& $request->getArguments()['pluginUid'] == $plugin->getUid()
		) {
			$sessionData = (array)json_decode($request->getParsedBody()[$parsedBodyKey]['__session'] ?? '[]', true);
			try {
				$session = new SessionData(...$sessionData);
				$session->hasErrors = false;
			} catch (\Exception $e) {
				$session = new SessionData();
			}

			$session->id = $session->id ?: GeneralUtility::makeInstance(Core\Crypto\Random::class)->generateRandomHexString(40);
			// manually create values form parsed body and uploaded files because get arguments uses array_merge_recursive to merge parsed body and uploaded files
			$postValues = $request->getParsedBody()[$parsedBodyKey][$form->get('name')] ?? [];
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
			$session->values = array_merge(
				$session->values,
				$cleanedPostValues
			);
		} else {
			$session = new SessionData();
			$isStepBack = false;
		}
		//Core\Session\UserSessionManager::create('FE')->collectGarbage(10);
		$frontendUserAuth = $request->getAttribute('frontend.user');
		$key = "tx_shape_c{$plugin->getUid()}_f{$form->getUid()}";

		try {
			DebugUtility::debug(json_decode($frontendUserAuth->getKey('ses', $key), true));
		} catch (\Exception $e) {
			DebugUtility::debug($e);
		}
		return new FormRuntime(
			$request,
			$settings,
			$plugin,
			$form,
			$session,
			$cleanedPostValues,
			$uploadStorage,
			$parsedBodyKey,
			$isStepBack,
		);
	}

	protected static function getContentData(
		RequestInterface $request,
		array $settings
	): ?array
	{
		$uid = $settings['pluginUid'];
		if (!$uid) {
			if ($request->getAttribute('currentContentObject')?->data['CType']) {
				return $request->getAttribute('currentContentObject')?->data;
			}
			$uid = $request->getArguments()['pluginUid'] ?? 0;
		}
		$queryBuilder = GeneralUtility::makeInstance(Core\Database\ConnectionPool::class)->getQueryBuilderForTable('tt_content');
		return $queryBuilder
			->select('*')
			->from('tt_content')
			->where(
				$queryBuilder->expr()->eq('uid', $uid)
			)
			->executeQuery()->fetchAllAssociative()[0] ?? null;
	}
}