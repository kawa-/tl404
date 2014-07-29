<?php

function register_tlid() {
	if (
		!isset($_POST[PARAM_TLID]) or
		!is_numeric($_POST[PARAM_TLID]) or
		(int) $_POST[PARAM_TLID] < 0
	) {
		Bye::ifInvalidParams();
	}

	$tlid = (int) $_POST[PARAM_TLID];

	try {
		$redis = new Redis();
		$redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);

		$res = DBH::regisiterTLID($redis, $tlid);
		if ($res === FALSE) {
			Bye::ifTLIDAlreadyRegistered($tlid);
		}

		Bye::ifSuccess(TRUE);
	} catch (RedisException $rexc) {
		Bye::ifRedisDown($rexc->getMessage());
	}
}

