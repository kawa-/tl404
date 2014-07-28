<?php

function delete_tl() {
	if (!isset($_POST[PARAM_SOURCE_ID]) or
		!isset($_POST[PARAM_TLID]) or
		!is_numeric($_POST[PARAM_SOURCE_ID]) or
		!is_numeric($_POST[PARAM_TLID]) or
		(int) $_POST[PARAM_SOURCE_ID] < 0 or
		(int) $_POST[PARAM_TLID] < 0
	) {
		Bye::ifInvalidParams();
	}

	$sid = (int) $_POST[PARAM_SOURCE_ID];
	$tlid = (int) $_POST[PARAM_TLID];

	try {
		$redis = new Redis();
		$redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);
		$res = DBH::deleteTL($redis, $sid, $tlid);
		if ($res === FALSE) {
			Bye::ifTimelineNotExist();
		}
		Bye::ifSuccess(TRUE);
	} catch (RedisException $rexc) {
		Bye::ifRedisDown($rexc->getMessage());
	}
}

