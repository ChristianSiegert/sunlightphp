<?php
namespace Libraries;

use \Exception as Exception;

class HttpRequest {
	public $status;

	public $headers;

	public $response;

	protected $url = "";

	protected $method = "GET";

	protected $data = array();

	protected $options = array();

	protected static $count = 0;

	public function send() {
		$handle = curl_init();

		$finalCurlOptions = array(
			CURLOPT_CUSTOMREQUEST => $this->method,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HEADER => true,
			CURLOPT_MAXREDIRS => 5,
			CURLOPT_NOBODY => $this->method === "HEAD",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_URL => $this->url,
			CURLOPT_USERAGENT => USERAGENT
		);

		foreach ($this->options as $option => $value) {
			$finalCurlOptions[$option] = $value;
		}

		if (!empty($this->data) && ($this->method === "POST" || $this->method === "PUT")) {
			$finalCurlOptions[CURLOPT_POSTFIELDS] = $this->data;
		}

		curl_setopt_array($handle, $finalCurlOptions);

		$rawResponse = curl_exec($handle);
		self::$count++;

		if (curl_errno($handle)) {
			throw new Exception("Curl: " . curl_error($handle) . " (Curl error code: " . curl_errno($handle) . ").");
		}

		$info = curl_getinfo($handle);
		curl_close($handle);

		// Convert encoding if necessary
		if (preg_match('#charset=(.+)$#', $info["content_type"], $match)
				&& mb_convert_case($match[1], MB_CASE_UPPER) !== mb_internal_encoding()) {
			$rawResponse = mb_convert_encoding($rawResponse, mb_internal_encoding(), $match[1]);
		}

		$headers = array();

		// Matches header at the beginning of a string
		$pattern = '#^HTTP/1\..*(?=(?:\n|\r\n){2,})#sU';

		while (preg_match($pattern, $rawResponse, $rawHeader)) {
			$header = array();

			foreach (explode("\r\n", $rawHeader[0]) as $i => $line) {
				if ($i === 0) {
					$header["Status"] = $line;
				} else {
					$explodedLine = explode(": ", $line, 2);
					$header[$explodedLine[0]] = isset($explodedLine[1]) ? $explodedLine[1] : "";
				}
			}

			$headers[] = $header;

			// Remove header from $rawResponse
			$rawResponse = trim(preg_replace($pattern, "", $rawResponse, 1));
		}

		$this->status = $info["http_code"];
		$this->headers = $headers;
		$this->response = trim($rawResponse);
		$this->info = $info;
		return $this;
	}

	public function setUrl($url) {
		$this->url = $url;
	}

	public function setMethod($method) {
		$this->method = strtoupper($method);
	}

	public function setData($data) {
		$this->data = $data;
	}

	public function setOption($option, $value) {
		$this->options[$option] = $value;
	}

	public static function getCount() {
		return self::$count;
	}
}
?>