<?php

require dirname(__FILE__) . '/bootstrap.php';

define('TEST_SERVER_HOST', 'localhost');
define('TEST_SERVER_PORT', 9080);

function httpPostWithDecode($method, array $params) {
	$data = http_build_query(array_merge(array('method' => $method), $params), "", "&");

	$header = array(
		"Content-Type: application/x-www-form-urlencoded",
		"Content-Length: " . strlen($data)
	);

	$context = array(
		"http" => array(
			"method" => "POST",
			"header" => implode("\r\n", $header),
			"content" => $data
		)
	);
	return json_decode(file_get_contents('http://' . TEST_SERVER_HOST . ':' . TEST_SERVER_PORT . '/', false, stream_context_create($context)), TRUE);
}
