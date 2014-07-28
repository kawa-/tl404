<?php

function get_random_subscriptions() {
	if (!isset($_POST[PARAM_SOURCE_ID]) or
		!is_numeric($_POST[PARAM_SOURCE_ID]) or
		(int) $_POST[PARAM_SOURCE_ID] < 0
	) {
		Bye::ifInvalidParams();
	}

	/* optional parameter */
	if (isset($_POST[PARAM_LIMIT])) {
		if (!is_numeric($_POST[PARAM_LIMIT]) or
			(int) $_POST[PARAM_LIMIT] < 0 or (int) $_POST[PARAM_LIMIT] > LIMIT
		) {
			Bye::ifInvalidLimit();
		}
	}

	$sid = (int) $_POST[PARAM_SOURCE_ID];
	$lim = isset($_POST[PARAM_LIMIT]) ? (int) $_POST[PARAM_LIMIT] : LIMIT;

	try {
		$redis = new Redis();
		$redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);

		$res = DBH::getRandomSubscriptions($redis, $sid, $lim);
		if ($res === FALSE) {
			Bye::ifNoSubscriptions($sid);
		}

		Bye::ifSuccess(array_map('intval', $res));
	} catch (RedisException $rexc) {
		Bye::ifRedisDown($rexc->getMessage());
	}
}

