<?php
class Request {
	public static $queryCount = 0;

	public static function query($url, $method = "GET", $data = array(), $jsonDecodeResponse = true, $curlOptions = array()) {
		$handle = curl_init();

		$finalCurlOptions = array(
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HEADER => true,
			CURLOPT_MAXREDIRS => 5,
			CURLOPT_NOBODY => $method === "HEAD",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_URL => $url,
			CURLOPT_USERAGENT => USERAGENT
		);

		foreach ($curlOptions as $key => $value) {
			$finalCurlOptions[$key] = $value;
		}

		if ($method === "POST" || $method === "PUT") {
			$finalCurlOptions[CURLOPT_POSTFIELDS] = $data;
		}

		curl_setopt_array($handle, $finalCurlOptions);

		$rawResponse = curl_exec($handle);
		self::$queryCount++;

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

		// $rawResponse now contains only data as we have removed all headers
		$response = trim($rawResponse);

		if ($jsonDecodeResponse) {
			$response = json_decode($response);
		}

		return array($info["http_code"], $headers, $response, $info);
	}
}
?>