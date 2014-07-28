<?php

function is_my_subscriber() {
	if (!isset($_POST[PARAM_SOURCE_ID]) or
		!isset($_POST[PARAM_TARGET_ID]) or
		!is_numeric($_POST[PARAM_SOURCE_ID]) or
		!is_numeric($_POST[PARAM_TARGET_ID]) or
		(int) $_POST[PARAM_SOURCE_ID] < 0 or
		(int) $_POST[PARAM_TARGET_ID] < 0 or
		(int) $_POST[PARAM_SOURCE_ID] === (int) $_POST[PARAM_TARGET_ID]
	) {
		Bye::ifInvalidParams();
	}

	$sid = (int) $_POST[PARAM_SOURCE_ID];
	$tid = (int) $_POST[PARAM_TARGET_ID];

	try {
		$redis = new Redis();
		$redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);

		$res = DBH::isMySubscriber($redis, $sid, $tid);

		Bye::ifSuccess($res);
	} catch (RedisException $rexc) {
		Bye::ifRedisDown($rexc->getMessage());
	}
}

