<?php

/**
 * This programs add a task to the MQ. Please use it for a test of workers.
 * Usage:
 * $ php new_task.php Hi!
 */
require_once __DIR__ . '/../lib/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();


$channel->queue_declare('task_queue', false, true, false, false);

$data = implode(' ', array_slice($argv, 1));
if (empty($data)) {
	$data = "Hello World!";
}
$msg = new AMQPMessage($data, array('delivery_mode' => 2));

$channel->basic_publish($msg, '', 'task_queue');

echo " [x] Sent ", $data, "\n";

$channel->close();
$connection->close();
