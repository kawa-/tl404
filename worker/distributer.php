<?php

/**
 * This program is a worker for RabbitMQ.
 * It distributes contents for the subscribers.
 *
 * Usage:
 * $ php distributer.php
 */
require dirname(__FILE__) . '/../lib/bootstrap.php';
require dirname(__FILE__) . '/../lib/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;

try {
	$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
	$channel = $connection->channel();

	$channel->queue_declare('task_queue', false, true, false, false);

	echo '[NOTICE] Waiting for messages. To exit press CTRL+C', "\n";

	$callback = function($msg) {
			echo "[NOTICE] Received ", $msg->body, "\n";
			//$data_array = json_decode($msg->body, TRUE);
			$data_array = unserialize($msg->body);
			if ($data_array['type'] === 'publish') {
				try {
					$sid = $data_array['sid'];
					$tlid = $data_array['tlid'];
					$elm = $data_array['elm'];
					$redis = new Redis();
					$redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);
					$subscribers = DBH::getAllSubscribers($redis, $sid);
					if ($subscribers === FALSE) {
						echo "[WARNING] OK, but there is no subscribers.\n";
					} else {
						DBH::distribute($redis, $subscribers, $tlid, $elm);
					}
					echo "[SUCCESS] Done", "\n";
					$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
				} catch (RedisException $rexc) {
					echo '[Error] Redis expection occurred. Detail: ' . $rexc->getMessage();
					die();
				}
			}
		};

	$channel->basic_qos(null, 1, null);
	$channel->basic_consume('task_queue', '', false, false, false, false, $callback);

	while (count($channel->callbacks)) {
		$channel->wait();
	}

	$channel->close();
	$connection->close();
} catch (Exception $e) {
	echo '[ERROR] ';
	Bye::ifGeneralError($e->getMessage());
}