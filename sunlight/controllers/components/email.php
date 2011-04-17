<?php
namespace Controllers\Components;

class Email {
	public $from = null;

	public $to = null;

	public $cc = null;

	public $bcc = null;

	public $subject = null;

	public $message = null;

	public function send() {
		$additionalHeaders = "";

		if (isset($this->from)) {
			$additionalHeaders .= "From: " . filter_var($this->from, FILTER_SANITIZE_EMAIL) . "\n";
		}

		if (isset($this->to)) {
			$validates = filter_var($this->to, FILTER_VALIDATE_EMAIL);
		}

		if (isset($this->cc)) {
			$additionalHeaders .= "CC: " . filter_var($this->cc, FILTER_SANITIZE_EMAIL) . "\n";
		}

		if (isset($this->bcc)) {
			$additionalHeaders .= "BCC: " . filter_var($this->bcc, FILTER_SANITIZE_EMAIL) . "\n";
		}

		// Set e-mail encoding to UTF-8
		mb_language("uni");

		return mb_send_mail(
			$this->to,
			$this->subject,
			$this->message,
			$additionalHeaders
		);
	}
}
?>