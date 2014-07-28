<?php

class DBH {

	static function deleteAll(Redis $redis) {
		$redis->flushAll();
		return TRUE;
	}

	static function subscribe(Redis $redis, $sid, $tid) {
		$res = $redis->zAdd('subscription:' . $sid, time(), $tid);
		if ($res === 0) {
			return FALSE;
		}
		$redis->zAdd('subscriber:' . $tid, time(), $sid);
		return TRUE;
	}

	static function unsubscribe(Redis $redis, $sid, $tid) {
		$res = $redis->zRem('subscription:' . $sid, $tid);
		if ($res === 0) {
			return FALSE;
		}
		$redis->zRem('subscriber:' . $tid, time(), $sid);
		return TRUE;
	}

	static function getAllSubscriptions(Redis $redis, $sid) {
		$res = $redis->zRange('subscription:' . $sid, 0, -1);
		if (empty($res)) {
			return FALSE;
		}
		return $res;
	}

	static function getAllSubscribers(Redis $redis, $sid) {
		$res = $redis->zRange('subscriber:' . $sid, 0, -1);
		if (empty($res)) {
			return FALSE;
		}
		return $res;
	}

	static function getSubscribers(Redis $redis, $sid, $offset, $count) {
		return $redis->zRevRange('subscriber:' . $sid, $offset, $offset + $count - 1);
	}

	static function getSubscriptions(Redis $redis, $sid, $offset, $count) {
		return $redis->zRevRange('subscription:' . $sid, $offset, $offset + $count - 1);
	}

	/*
	 * discon!!!
	 * 
	  static function getRandomSubscribers(Redis $redis, $sid, $lim) {
	  $res = $redis->sRandMember('subscriber:' . $sid, $lim);
	  //$res = $redis->sRandMember('subscriber:' . $sid, $lim);
	  if (empty($res)) {
	  return FALSE;
	  }

	  return $res;
	  }

	  static function getRandomSubscriptions(Redis $redis, $sid, $lim) {
	  $res = $redis->sRandMember('subscription:' . $sid, $lim);
	  if (empty($res)) {
	  return FALSE;
	  }

	  return $res;
	  }
	 */

	static function getNumberOfSubscribers(Redis $redis, $sid) {
		return (int) $redis->zCard('subscriber:' . $sid);
	}

	static function getNumberOfSubscriptions(Redis $redis, $sid) {
		return (int) $redis->zCard('subscription:' . $sid);
	}

	static function getTimeline(Redis $redis, $sid, $tlid) {
		$res = $redis->lRange($sid . ":" . $tlid, 0, -1);
		if (empty($res)) {
			return FALSE;
		}
		return $res;
	}

	static function isMySubscriber(Redis $redis, $sid, $tid) {
		if ($redis->zRank('subscriber:' . $sid, $tid) === FALSE) {
			return FALSE;
		}
		return TRUE;
	}

	static function isMySubscription(Redis $redis, $sid, $tid) {
		if ($redis->zRank('subscription:' . $sid, $tid) === FALSE) {
			return FALSE;
		}
		return TRUE;
	}

	static function putElmOnTL(Redis $redis, $sid, $tlid, $elm) {
		$redis->rPush($sid . ":" . $tlid, $elm);
	}

	static function distribute(Redis $redis, $subscribers, $tlid, $elm) {
		$pipe = $redis->multi(Redis::PIPELINE);
		foreach ($subscribers as $subscriber_id) {
			$pipe->rPush($subscriber_id . ":" . $tlid, $elm);
		}
		$pipe->exec();
	}

	static function deleteTL(Redis $redis, $sid, $tlid) {
		$res = $redis->del($sid . ":" . $tlid);
		if (empty($res)) {
			return FALSE;
		}
		return TRUE;
	}

	static function deleteAllTLByID(Redis $redis, $sid) {
		$tls_to_be_deleted = $redis->keys($sid . ':*');
		if (!empty($tls_to_be_deleted)) {
			$pipe = $redis->multi(Redis::PIPELINE);
			foreach ($tls_to_be_deleted as $tl) {
				$pipe->del($tl);
			}
			$pipe->exec();
		}
	}

	static function deleteIDFromSubscribers(Redis $redis, $sid) {
		$subscribers = $redis->zRange('subscriber:' . $sid, 0, -1);
		$pipe2 = $redis->multi(Redis::PIPELINE);
		if (!empty($subscribers)) {
			foreach ($subscribers as $id) {
				$pipe2->zRem('subscription:' . $id, $sid);
			}
		}
		$pipe2->del('subscriber:' . $sid);
		$pipe2->exec();
	}

	static function deleteIDFromSubscriptions(Redis $redis, $sid) {
		$subscriptions = $redis->zRange('subscription:' . $sid, 0, -1);
		$pipe3 = $redis->multi(Redis::PIPELINE);
		if (!empty($subscriptions)) {
			foreach ($subscriptions as $id) {
				$pipe3->zRem('subscriber:' . $id, $sid);
			}
		}
		$pipe3->del('subscription:' . $sid);
		$pipe3->exec();
	}

}

