<?php

function delete_id() {
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

		/* TL の削除 */
		DBH::deleteAllTLByID($redis, $sid);

		/* subscriber の削除 */
		DBH::deleteIDFromSubscribers($redis, $sid);

		/* subscription の削除 */
		DBH::deleteIDFromSubscriptions($redis, $sid);

		Bye::ifSuccess(TRUE);
	} catch (RedisException $rexc) {
		Bye::ifRedisDown($rexc->getMessage());
	}
}

