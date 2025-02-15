<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core;
use TYPO3\CMS\Fluid;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase;

class SendEmailFinisher extends AbstractFinisher
{
	public string $templateName = 'SendEmail';
	public string $mailFormat = Core\Mail\FluidEmail::FORMAT_BOTH;

	public function execute(): ?ResponseInterface
	{
		$this->settings = array_merge([
			'mailSubject' => '',
			'mailTitle' => '',
			'mailBody' => '',
			'mailAttachUploads' => false,
			'mailTemplate' => '',
			'senderAddress' => '',
			'senderName' => '',
			'recipientAddresses' => '',
			'ccRecipientAddresses' => '',
			'bccRecipientAddresses' => '',
			'replyToAddresses' => '',
		], $this->settings);

		$recipients = $this->getAddresses($this->settings['recipientAddresses']);
		if (!$recipients) {
			return null;
		}
		$email = new Core\Mail\FluidEmail();
		$email
			->from($this->resolveSenderAddress())
			->to(...$recipients)
			->subject($this->settings['mailSubject'] ?: ($GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']) . ' message')
			->setRequest($this->context->request)
			->format($this->mailFormat)
			->setTemplate($this->settings['mailTemplate'] ?: $this->templateName)
			->assignMultiple([
				'formValues' => $this->context->session->values,
				'form' => $this->context->form,
				'settings' => $this->settings,
				'interpolatedMailBody' => $this->interpolateStringWithFormValues($this->settings['mailBody']),
			]);
		if ($this->settings['ccRecipientAddresses']) {
			$email->cc(...$this->getAddresses($this->settings['ccRecipientAddresses']));
		}
		if ($this->settings['bccRecipientAddresses']) {
			$email->bcc(...$this->getAddresses($this->settings['bccRecipientAddresses']));
		}
		if ($this->settings['replyToAddresses']) {
			$email->replyTo(...$this->getAddresses($this->settings['replyToAddresses']));
		}
		if ($this->settings['mailAttachUploads']) {
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

	protected function resolveSenderAddress(): Address
	{
		return new Address(
			$this->settings['senderAddress'] ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'],
			$this->settings['senderName'] ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']
		);
	}

	protected function getAddresses(string $addressList): array
	{
		$addressList = $this->interpolateStringWithFormValues($addressList);
		$addresses = [];
		foreach (GeneralUtility::trimExplode(',', $addressList, true) as $address) {
			$addresses[] = $address;
		}
		return $addresses;
	}

	protected function interpolateStringWithFormValues(string $string): string
	{
		foreach ($this->context->session->values as $key => $value) {
			if (is_array($value)) {
				$value = implode(', ', $value);
			}
			$string = str_replace('{' . $key . '}', $value ?? '', $string);
		}
		return $string;
	}
}