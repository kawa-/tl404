<?php

function barusu() {

	if (IS_DEBUG !== TRUE) {
		Bye::ifBarusuDisabledUnderDebugMode();
	}

	if (!isset($_POST[PARAM_BARUSU]) or $_POST[PARAM_BARUSU] !== 'BARUSU!!!') {
		Bye::ifInvalidParams();
	}

	try {
		$redis = new Redis();
		$redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);
		DBH::deleteAll($redis);
		Bye::ifSuccess('BARUSU!!!');
	} catch (RedisException $rexc) {
		Bye::ifRedisDown($rexc->getMessage());
	}
}

