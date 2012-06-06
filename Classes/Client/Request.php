<?php
/**
 * Request helper
 */
class Request {

	static public function post($url, $postdata, $files = array()) {
		$data = "";
		$boundary = "---------------------" . substr(md5(rand(0, 32000)), 0, 10);

		//Collect Postdata
		foreach ($postdata as $key => $val) {
			$data .= "--$boundary\n";
			$data .= "Content-Disposition: form-data; name=\"" . $key . "\"\n\n" . $val . "\n";
		}

		$data .= "--$boundary\n";

		//Collect Filedata
		foreach ($files as $key => $file) {
			$fileContents = file_get_contents($file['tmp_name']);

			$data .= "Content-Disposition: form-data; name=\"{$key}\"; filename=\"{$file['name']}\"\n";
			$data .= "Content-Type: image/jpeg\n";
			$data .= "Content-Transfer-Encoding: binary\n\n";
			$data .= $fileContents . "\n";
			$data .= "--$boundary--\n";
		}

		$params = array('http' => array(
			'method' => 'POST',
			'user_agent' => "PHP/" . PHP_VERSION,
			'header' => 'Content-Type: multipart/form-data; boundary=' . $boundary,
			'content' => $data
		));

		$ctx = stream_context_create($params);
		$fp = fopen($url, 'rb', false, $ctx);

		if (!$fp) {
			throw new Exception("Problem with $url, $php_errormsg");
		}

		$response = @stream_get_contents($fp);
		if ($response === false) {
			throw new Exception("Problem reading data from $url, $php_errormsg");
		}
		return $response;
	}

}

?>