<?php

function register_id() {

	if (!isset($_POST[PARAM_SOURCE_ID]) or
		!is_numeric($_POST[PARAM_SOURCE_ID]) or
		(int) $_POST[PARAM_SOURCE_ID] < 0
	) {
		Bye::ifInvalidParams();
	}

	$sid = (int) $_POST[PARAM_SOURCE_ID];
	$bucket_id = (int) floor($sid / BUCKET_SIZE);

	try {
		$redis = new Redis();
		$redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);
		$res = $redis->sAdd('bucket:' . $bucket_id, $sid);

		if ($res === 0) {
			Bye::ifIDAlreadyRegistered($sid);
		}

		Bye::ifSuccess(array('registered.'));
	} catch (RedisException $rexc) {
		Bye::ifRedisDown($rexc->getMessage());
	}
}