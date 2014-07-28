<?php

function get_subscriptions() {
	if (!isset($_POST[PARAM_SOURCE_ID]) or
		!is_numeric($_POST[PARAM_SOURCE_ID]) or
		(int) $_POST[PARAM_SOURCE_ID] < 0
	) {
		Bye::ifInvalidParams();
	}

	/* optional parameters */
	if (isset($_POST[PARAM_OFFSET])) {
		if (!is_numeric($_POST[PARAM_OFFSET]) or
			(int) $_POST[PARAM_OFFSET] < 0
		) {
			Bye::ifInvalidOffset();
		}
	}
	if (isset($_POST[PARAM_COUNT])) {
		if (!is_numeric($_POST[PARAM_COUNT]) or
			(int) $_POST[PARAM_COUNT] < 0 or
			(int) $_POST[PARAM_COUNT] > LIMIT_COUNT
		) {
			Bye::ifInvalidCount();
		}
	}

	$sid = (int) $_POST[PARAM_SOURCE_ID];
	$offset = isset($_POST[PARAM_OFFSET]) ? (int) $_POST[PARAM_OFFSET] : 0;
	$count = isset($_POST[PARAM_COUNT]) ? (int) $_POST[PARAM_COUNT] : LIMIT_COUNT;

	try {
		$redis = new Redis();
		$redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);
		$res = DBH::getSubscriptions($redis, $sid, $offset, $count);
		Bye::ifSuccess(array_map('intval', $res));
	} catch (RedisException $rexc) {
		Bye::ifRedisDown($rexc->getMessage());
	}
}

