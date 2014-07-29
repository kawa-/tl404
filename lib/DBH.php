<?php

class DBH {

	static function deleteAll(Redis $redis) {
		$redis->flushAll();
		return TRUE;
	}

	static function subscribe(Redis $redis, $sid, $tid) {
		$num = $redis->zCard('subscription:' . $sid);
		if ($num >= MAX_SUBSCRIPTIONS) {
			return -1;
		}

		$res = $redis->zAdd('subscription:' . $sid, time(), $tid);
		if ($res === 0) {
			return 0;
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
		$redis->lPush($sid . ":" . $tlid, $elm);
	}

	static function distribute(Redis $redis, $subscribers, $sid, $tlid, $elm) {
		$pipe = $redis->multi(Redis::PIPELINE);
		foreach ($subscribers as $subscriber_id) {
			$pipe->lPush($subscriber_id . ":" . $tlid, $elm);
		}
		$pipe->lPush($sid . ":" . $tlid, $elm);
		$pipe->exec();
	}

	static function trimTLs(Redis $redis, Array $tlids, $sid) {
		$pipe = $redis->multi(Redis::PIPELINE);
		foreach ($tlids as $tlid) {
			$pipe->lTrim($sid . ":" . $tlid, 0, LIMIT_TL_ELMS - 1);
		}
		$pipe->exec();
	}

	static function registerID(Redis $redis, $sid) {
		$res = $redis->zAdd('registered_ids', time(), $sid);
		if ($res === 0) {
			return FALSE;
		}
		return TRUE;
	}

	static function unregisterID(Redis $redis, $sid) {
		$res = $redis->zRem('registered_ids', time(), $sid);
		if ($res === 0) {
			return FALSE;
		}
		return TRUE;
	}

	static function regisiterTLID(Redis $redis, $tlid) {
		$res = $redis->sAdd('registered_tlids', $tlid);
		if ($res === 0) {
			return FALSE;
		}
		return TRUE;
	}

	static function unregisterTLID(Redis $redis, $tlid) {
		$res = $redis->sRem('registered_tlids', $tlid);
		if ($res === 0) {
			return FALSE;
		}
		return TRUE;
	}

	static function getAllRegisteredID(Redis $redis) {
		return $redis->zRange('registered_ids', 0, -1);
	}

	static function getAllRegisteredTLID(Redis $redis) {
		return $redis->sMembers('registered_tlids');
	}

	static function deleteTL(Redis $redis, $sid, $tlid) {
		$res = $redis->del($sid . ":" . $tlid);
		if (empty($res)) {
			return FALSE;
		}
		return TRUE;
	}

	static function deleteAllTLByID(Redis $redis, $sid) {
		$tlids = $redis->sMembers('registered_tlids');

		$pipe = $redis->multi(Redis::PIPELINE);
			foreach ($tlids as $tlid) {
				$pipe->del($sid . ":" . $tlid);
		}
		$pipe->exec();
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

