<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UBOS\Shape\Utility\TemplateVariableParser;

class SendEmailFinisher extends AbstractFinisher
{
	public function execute(): ?ResponseInterface
	{
		$this->settings = array_merge([
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
		], $this->settings);

		$recipients = $this->getAddresses($this->settings['recipientAddresses']);
		if (!$recipients || !$this->settings['subject']) {
			return null;
		}

		$email = new Core\Mail\FluidEmail();
		$senderAddress = new Address(
			$this->settings['senderAddress'] ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'],
			$this->settings['senderName'] ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']
		);
		$subject = $this->parseWithValues($this->settings['subject']);
		$template = $this->settings['template'] ?: 'Finisher/SendEmail/Default';
		$templateConfig = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['shape']['finishers']['sendEmail']['templates'][$template] ?? [];
		$format = $templateConfig['format'] ?? Core\Mail\FluidEmail::FORMAT_BOTH;

		$variables = [
			'formValues' => $this->context->session->values,
			'form' => $this->context->form,
			'settings' => $this->settings,
			'parsed' => [
				'body' => $this->parseWithValues($this->settings['body'])
			]
		];
		foreach ($templateConfig['fields'] ?? [] as $key => $config) {
			if ($config['templateVariableParser']) {
				$variables['parsed'][$key] = $this->parseWithValues($this->settings[$key] ?? '');
			}
		}

		$email
			->from($senderAddress)
			->to(...$recipients)
			->subject($subject)
			->setRequest($this->context->request)
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
			foreach ($this->context->form->get('pages') as $page) {
				foreach ($page->get('fields') as $field) {
					if ($field->get('type') === 'file' && isset($this->context->session->values[$field->get('name')])) {
						foreach ($this->context->session->values[$field->get('name')] as $fileIdentifier) {
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
		return null;
	}

	protected function getAddresses(string $addressList): array
	{
		$addressList = TemplateVariableParser::parse($addressList, $this->context->session->values);
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
		return TemplateVariableParser::parse($string, $this->context->session->values);
	}
}