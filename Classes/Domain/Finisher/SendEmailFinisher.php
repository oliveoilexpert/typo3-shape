<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UBOS\Shape\Utility\TemplateVariableParser;

class SendEmailFinisher extends AbstractFinisher
{
	protected array $settings = [
		'subject' => '',
		'body' => '',
		'attachUploads' => false,
		'template' => '',
		'senderAddress' => '',
		'senderName' => '',
		'recipientAddresses' => '',
		'ccRecipientAddresses' => '',
		'bccRecipientAddresses' => '',
		'replyToAddresses' => '',
	];

	public function execute(): void
	{
		$recipients = $this->getAddresses($this->settings['recipientAddresses']);
		if (!$recipients || !$this->settings['subject']) {
			return;
		}

		$email = new Core\Mail\FluidEmail($this->getView()->getRenderingContext()->getTemplatePaths());
		$senderAddress = new Address(
			$this->settings['senderAddress'] ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'],
			$this->settings['senderName'] ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']
		);
		$subject = $this->parseWithValues($this->settings['subject']);
		$template = $this->settings['template'] ?: 'Finisher/SendEmail/Default';
		$templateConfig = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['shape']['finishers']['sendEmail']['templates'][$template] ?? [];
		$format = $templateConfig['format'] ?? Core\Mail\FluidEmail::FORMAT_BOTH;

		$formValues = $this->getFormValues();
		$variables = [
			'formValues' => $formValues,
			'settings' => $this->settings,
			'runtime' => $this->getRuntime(),
			'parsed' => [
				'body' => $this->parseWithValues($this->settings['body'])
			]
		];
		foreach ($templateConfig['fields'] ?? [] as $key => $config) {
			$variables['parsed'][$key] = $this->parseWithValues($this->settings[$key] ?? '');
		}

		$email
			->from($senderAddress)
			->to(...$recipients)
			->subject($subject)
			->setRequest($this->getRequest())
			->format($format)
			->setTemplate($template)
			->assignMultiple($variables);
		if ($this->settings['ccRecipientAddresses']) {
			$email->cc(...$this->getAddresses($this->settings['ccRecipientAddresses']));
		}
		if ($this->settings['bccRecipientAddresses']) {
			$email->bcc(...$this->getAddresses($this->settings['bccRecipientAddresses']));
		}
		if ($this->settings['replyToAddresses']) {
			$email->replyTo(...$this->getAddresses($this->settings['replyToAddresses']));
		}
		if ($this->settings['attachUploads']) {
			$resourceFactory = GeneralUtility::makeInstance(Core\Resource\ResourceFactory::class);
			foreach ($this->getForm()->get('pages') as $page) {
				foreach ($page->get('fields') as $field) {
					if ($field->get('type') === 'file' && isset($formValues[$field->get('name')])) {
						foreach ($formValues[$field->get('name')] as $fileIdentifier) {
							$file = $resourceFactory->getFileObjectFromCombinedIdentifier($fileIdentifier);
							if ($file) {
								$email->attach($file->getContents(), $file->getName(), $file->getMimeType());
							}
						}
					}
				}
			}
		}
		GeneralUtility::makeInstance(Core\Mail\MailerInterface::class)->send($email);
	}

	protected function getAddresses(string $addressList): array
	{
		$addressList = TemplateVariableParser::parse($addressList, $this->getFormValues());
		$addresses = [];
		foreach (GeneralUtility::trimExplode(',', $addressList, true) as $address) {
			if (str_starts_with($address, '{{')) {
				continue;
			}
			$addresses[] = $address;
		}
		return $addresses;
	}

	protected function parseWithValues(string $string): string
	{
		return TemplateVariableParser::parse($string, $this->getFormValues());
	}
}