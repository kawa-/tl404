<?php

function get_number_of_subscribers() {
	if (!isset($_POST[PARAM_SOURCE_ID]) or
		!is_numeric($_POST[PARAM_SOURCE_ID]) or
		(int) $_POST[PARAM_SOURCE_ID] < 0
	) {
		Bye::ifInvalidParams();
	}

	$sid = (int) $_POST[PARAM_SOURCE_ID];

	try {
		$redis = new Redis();
		$redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);

		$res = DBH::getNumberOfSubscribers($redis, $sid);

		Bye::ifSuccess($res);
	} catch (RedisException $rexc) {
		Bye::ifRedisDown($rexc->getMessage());
	}
}

