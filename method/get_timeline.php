<?php

function get_timeline() {
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
		$res = $redis->lRange($sid . ":" . $tlid, 0, -1);
		if (empty($res)) {
			Bye::ifTimelineEmpty();
		}
		Bye::ifSuccess($res);
	} catch (RedisException $rexc) {
		Bye::ifRedisDown($rexc->getMessage());
	}
}

