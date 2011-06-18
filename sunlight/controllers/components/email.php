<?php
namespace Controllers\Components;

use \Exception;

class Email {
	const EXCEPTION_INVALID_EMAIL_ADDRESS = 1;
	const EXCEPTION_MISSING_SENDER = 2;
	const EXCEPTION_MISSING_RECIPIENT = 3;

	protected $from = "";

	protected $to = array();

	protected $cc = array();

	protected $bcc = array();

	protected $subject = "";

	protected $message = "";

	public static function formatAddress($eMailAddress, $displayName = "") {
		$eMailAddress = filter_var($eMailAddress, FILTER_SANITIZE_EMAIL);

		if (strlen($eMailAddress) > 254) {
			throw new Exception("E-mail address is longer than 254 characters.", self::EXCEPTION_INVALID_EMAIL_ADDRESS);
		}

		return $displayName ? "$displayName <$eMailAddress>" : $eMailAddress;
	}

	public function setFrom($eMailAddress, $displayName = "") {
		$this->from = self::formatAddress($eMailAddress, $displayName);
	}

	public function addTo($eMailAddress, $displayName = "") {
		array_push($this->to, self::formatAddress($eMailAddress, $displayName));
	}

	public function addCc($eMailAddress, $displayName = "") {
		array_push($this->cc, self::formatAddress($eMailAddress, $displayName));
	}

	public function addBcc($eMailAddress, $displayName = "") {
		array_push($this->bcc, self::formatAddress($eMailAddress, $displayName));
	}

	public function setSubject($subject) {
		$this->subject = $subject;
	}

	public function setMessage($message) {
		$this->message = $message;
	}

	public function getFrom() {
		return $this->from;
	}

	public function getTo() {
		return $this->to;
	}

	public function getCc() {
		return $this->cc;
	}

	public function getBcc() {
		return $this->bcc;
	}

	public function getSubject() {
		return $this->subject;
	}

	public function getMessage() {
		return $this->message;
	}

	public function send() {
		if (empty($this->from)) {
			throw new Exception("Please provide a sender.", self::EXCEPTION_MISSING_SENDER);
		}

		if (empty($this->to) && empty($this->cc) && empty($this->bcc)) {
			throw new Exception("Please provide a recipient.", self::EXCEPTION_MISSING_RECIPIENT);
		}

		// Set e-mail encoding to UTF-8
		mb_language("uni");

		$to = implode(", ", $this->getTo());

		$additionalHeaders = "From: " . $this->getFrom() . "\n";

		if ($this->getCc()) {
			$additionalHeaders .= "Cc: " . implode(", ", $this->getCc()) . "\n";
		}

		if ($this->getBcc()) {
			$additionalHeaders .= "Bcc: " . implode(", ", $this->getBcc()) . "\n";
		}

		return mb_send_mail(
			$to,
			$this->getSubject(),
			$this->getMessage(),
			$additionalHeaders
		);
	}
}
?>