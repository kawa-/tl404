<?php

require dirname(__FILE__) . '/../lib/bootstrap.php';

echo '[NOTICE] This trims Timelines in ' . INTERVAL_SECONDS . ' seconds intervals. To exit press CTRL+C', "\n";

try {
	$redis = new Redis();
	$redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);
	while (TRUE) {
		$ids = DBH::getAllRegisteredID($redis); // $redis->zRange('registered_ids', 0, -1);
		$tlids = DBH::getAllRegisteredTLID($redis); // $redis->sMembers('registered_tlids');
		echo '[NOTICE] Registered tlids: ' . json_encode($tlids) . "\n";
		foreach ($ids as $id) {
			DBH::trimTLs($redis, $tlids, $id);
			echo '[NOTICE] TLs by ' . $id . " was trimmed.\n";
			sleep(INTERVAL_SECONDS);
		}
	}
} catch (Exception $e) {
	echo '[ERROR] ';
	Bye::ifGeneralError($e->getMessage());
}