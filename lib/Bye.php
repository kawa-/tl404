<?php

class Bye {

	static function ifInvalidMethod() {
		self::outputFailure(40000, 'Invalid Method. Visit readme.html if you have no idea.');
	}

	static function ifInvalidParams() {
		self::outputFailure(40001, 'Invalid Params.');
	}

	static function ifInvalidLimit() {
		self::outputFailure(40002, 'Invalid lim.');
	}

	static function ifInvalidOffset() {
		self::outputFailure(40003, 'Invalid ofs (offset).');
	}

	static function ifInvalidCount() {
		self::outputFailure(40004, 'Invalid cnt (count).');
	}

	static function ifAlreadySubscribing($sid, $tid) {
		self::outputFailure(40100, "id:" . $sid . " is already subscribing to id:" . $tid . ".");
	}

	static function ifNotSubscribing($sid, $tid) {
		self::outputFailure(40101, "id:" . $sid . " is NOT subscribing to id:" . $tid . ".");
	}

	static function ifTooManySubscribing($sid) {
		self::outputFailure(40102, "id:" . $sid . " reached the subscription limit (" . MAX_SUBSCRIPTIONS . ").");
	}

	static function ifIDAlreadyRegistered($sid) {
		self::outputFailure(40200, "id:" . $sid . " is already registered.");
	}

	static function ifIDNotRegistered($sid) {
		self::outputFailure(40201, "id:" . $sid . " is NOT registered.");
	}

	static function ifTLIDAlreadyRegistered($tlid) {
		self::outputFailure(40300, "tlid:" . $tlid . " is already registered.");
	}

	static function ifTLIDNotRegistered($tlid) {
		self::outputFailure(40301, "tlid:" . $tlid . " is NOT registered.");
	}

	static function ifRedisDown($msg) {
		self::outputFailure(59000, 'Internal DB Down. ' . $msg);
	}

	static function ifRabbitMQDown($msg) {
		self::outputFailure(59100, 'Internal RabbitMQ Down. ' . $msg);
	}

	static function ifGeneralError($msg) {
		self::outputFailure(59999, 'Unknown Error. Detail: ' . $msg);
	}

	static function ifNoSubscriptions($sid) {
		self::outputFailure(60100, 'id:' . $sid . ' has no subscriptions.');
	}

	static function ifNoSubscribers($sid) {
		self::outputFailure(60101, 'id:' . $sid . ' has no subscribers.');
	}

	static function ifTimelineEmpty() {
		self::outputFailure(60200, 'An empty timeline.');
	}

	static function ifTimelineNotExist() {
		self::outputFailure(60201, 'The timeline does not exist.');
	}

	static function ifBarusuDisabledUnderDebugMode() {
		self::outputFailure(70100, 'barusu is disable under the debug mode.');
	}

	static function ifSuccess($result) {
		self::outputSuccess(20000, 'OK', $result);
	}

	private static function outputFailure($code, $message) {
		//header('Access-control-allow-origin: *');
		@header('Content-Type: application/json; charset=utf-8');
		@header('X-Content-Type-Options: nosniff');
		exit(json_encode(array('code' => $code, 'message' => $message)));
	}

	private static function outputSuccess($code, $message, $result) {
		//header('Access-control-allow-origin: *');
		@header('Content-Type: application/json; charset=utf-8');
		@header('X-Content-Type-Options: nosniff');
		exit(json_encode(array('code' => $code, 'message' => $message, 'result' => $result)));
	}

}
