<?php

//require_once __DIR__ . '/../lib/vendor/autoload.php';
require dirname(__FILE__) . '/../lib/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

function publish() {
	if (!isset($_POST[PARAM_SOURCE_ID]) or
		!isset($_POST[PARAM_TLID]) or
		!isset($_POST[PARAM_ELEMENT]) or
		!is_numeric($_POST[PARAM_SOURCE_ID]) or
		!is_numeric($_POST[PARAM_TLID]) or
		(int) $_POST[PARAM_SOURCE_ID] < 0 or
		(int) $_POST[PARAM_TLID] < 0 or
		strlen($_POST[PARAM_ELEMENT]) > MAX_ELEMENT_SIZE
	) {
		Bye::ifInvalidParams();
	}

	$sid = (int) $_POST[PARAM_SOURCE_ID];
	$tlid = (int) $_POST[PARAM_TLID];
	$elm = $_POST[PARAM_ELEMENT];

	$data = serialize(array('type' => 'publish', 'sid' => $sid, 'tlid' => $tlid, 'elm' => $elm));

	try {
		$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
		$channel = $connection->channel();
		$channel->queue_declare('task_queue', false, true, false, false);
		$msg = new AMQPMessage($data, array('delivery_mode' => 2));
		$channel->basic_publish($msg, '', 'task_queue');

		Bye::ifSuccess(TRUE);
	} catch (Exception $e) {
		Bye::ifGeneralError($e->getMessage());
	}
}

