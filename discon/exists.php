<?php

function exists() {
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
		$res = $redis->sIsMember('bucket:' . $bucket_id, $sid);

		if ($res === FALSE) {
			Bye::ifIDNotRegistered($sid);
		}

		Bye::ifSuccess(array(TRUE));
	} catch (RedisException $rexc) {
		Bye::ifRedisDown($rexc->getMessage());
	}
}

