<?php

function distribute() {
	if (!isset($_POST[PARAM_SOURCE_ID]) or
		!isset($_POST[PARAM_TLID]) or
		!isset($_POST[PARAM_ELEMENT]) or
		!is_numeric($_POST[PARAM_SOURCE_ID]) or
		!is_numeric($_POST[PARAM_TLID]) or
		(int) $_POST[PARAM_SOURCE_ID] < 0 or
		(int) $_POST[PARAM_TLID] < 0 or
		strlen($_POST[PARAM_ELEMENT]) > MAX_ELEMENT_SIZE
	) {
		Bye::ifInvalidParams();
	}

	$sid = (int) $_POST[PARAM_SOURCE_ID];
	$tlid = (int) $_POST[PARAM_TLID];
	$elm = $_POST[PARAM_ELEMENT];

	try {
		$redis = new Redis();
		$redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);
		$subscribers = $redis->sMembers('subscriber:' . $sid);

		$pipe = $redis->multi(Redis::PIPELINE);
		foreach ($subscribers as $subscriber_id) {
			$pipe->rPush($subscriber_id . ":" . $tlid, $elm);
		}
		$pipe->exec();
		Bye::ifSuccess(TRUE);
	} catch (RedisException $rexc) {
		Bye::ifRedisDown($rexc->getMessage());
	}
}

