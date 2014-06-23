<?php

/**
 * 全ての RPC を正常系のみテスト (異常系は個別のテストを参照)。
 *
 * これを実行する条件:
 * - mutecity/pub のディレクトリに移動して、localhost:9080 で立ち上げ
 * - Redis をlocalhost:9736 で立ち上げ
 * - lib/bootstrap.php の IS_DEBUG を TRUE にしておくこと。FALSEだとテストが実行されない。
 *
 * 注意点:
 * - データはすべて消える (IS_DEBUG が TRUE の場合)
 * - define('LIMIT', 3); で get_random 系の取ってくる数が決定されるが、この値が小さすぎる場合はテストで不具合が生じることがある
 * (制限数 > testSubscribeメソッドでのsubscribe数 となる必要がある)
 *
 * テスト項目:
 * - barusu
 * - subscribe
 * - unsubscribe
 * - get_all_subscriptions
 * - get_all_subscribers
 * - get_random_subscriptions
 * - get_random_subscribers
 * - is_my_subscriber
 * - is_my_subscription
 * - put (+ get_timeline)
 * - distribute (+ get_timeline)
 */
require dirname(__FILE__) . '/../lib/bootstrap4test.php';

class generalTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * 一旦すべてのデータを削除
	 */
	public function testBarusu() {
		if (IS_DEBUG !== TRUE) {
			die('Test is available under the debug mode.');
		}
		
		$res = httpPostWithDecode('barusu', array('barusu' => 'BARUSU!!!'));
		$this->assertEquals(20000, $res['code']);
	}

	public function testSubscribe() {
		/* id:2,3,4,5,6 が id:1 を subscribe */
		$res2to1 = httpPostWithDecode('subscribe', array('sid' => 2, 'tid' => 1));
		$res3to1 = httpPostWithDecode('subscribe', array('sid' => 3, 'tid' => 1));
		$res4to1 = httpPostWithDecode('subscribe', array('sid' => 4, 'tid' => 1));
		$res5to1 = httpPostWithDecode('subscribe', array('sid' => 5, 'tid' => 1));
		$res6to1 = httpPostWithDecode('subscribe', array('sid' => 6, 'tid' => 1));

		$this->assertEquals(20000, $res2to1['code']);
		$this->assertEquals(20000, $res3to1['code']);
		$this->assertEquals(20000, $res4to1['code']);
		$this->assertEquals(20000, $res5to1['code']);
		$this->assertEquals(20000, $res6to1['code']);
	}

	public function testUnSubscribe() {
		/* id:5,6 が id:1 を unsubscribe */
		$res5to1 = httpPostWithDecode('unsubscribe', array('sid' => 5, 'tid' => 1));
		$res6to1 = httpPostWithDecode('unsubscribe', array('sid' => 6, 'tid' => 1));

		$this->assertEquals(20000, $res5to1['code']);
		$this->assertEquals(20000, $res6to1['code']);
	}

	public function testGetAllSubscriptions() {
		$res = httpPostWithDecode('get_all_subscriptions', array('sid' => 2));
		$subscriptions = $res['result'];
		$this->assertTrue(array(1) === $subscriptions);
	}

	public function testGetAllSubscribers() {
		$res = httpPostWithDecode('get_all_subscribers', array('sid' => 1));
		$subscriptions = $res['result'];
		$this->assertTrue(array(2, 3, 4) === $subscriptions);
	}

	public function testGetRandomSubscriptions() {
		$res = httpPostWithDecode('get_random_subscriptions', array('sid' => 2));
		$subscriptions = $res['result'];
		$this->assertTrue(in_array("1", $subscriptions));
	}

	public function testGetRandomSubscribers() {
		$res = httpPostWithDecode('get_random_subscribers', array('sid' => 1));
		$subscribers = $res['result'];
		$this->assertTrue(in_array("2", $subscribers));
	}

	public function testIsMySubscriber() {
		/* id:1 の購読者のうちの一人は id:2 かどうか */
		$res = httpPostWithDecode('is_my_subscriber', array('sid' => 1, 'tid' => 2));
		$this->assertTrue($res['result']);
	}

	public function testIsMySubscription() {
		/* id:2 が購読しているものの中に、id:1が含まれるか */
		$res = httpPostWithDecode('is_my_subscription', array('sid' => 2, 'tid' => 1));
		$this->assertTrue($res['result']);
	}

	public function testPut() {
		$text = 'id:123456789';
		$res1 = httpPostWithDecode('put', array('sid' => 1, 'tlid' => 0, 'elm' => $text));
		$this->assertTrue($res1['result']);
		$res2 = httpPostWithDecode('get_timeline', array('sid' => 1, 'tlid' => 0));
		$this->assertTrue($res2['result'][0] === $text);
	}

	public function testDistribute() {
		$text = 'id:abcdefghijk';
		$res1 = httpPostWithDecode('distribute', array('sid' => 1, 'tlid' => 0, 'elm' => $text));
		$this->assertTrue($res1['result']);
		$res2 = httpPostWithDecode('get_timeline', array('sid' => 2, 'tlid' => 0));
		$this->assertTrue($res2['result'][0] === $text);
		$res3 = httpPostWithDecode('get_timeline', array('sid' => 3, 'tlid' => 0));
		$this->assertTrue($res3['result'][0] === $text);
		$res4 = httpPostWithDecode('get_timeline', array('sid' => 4, 'tlid' => 0));
		$this->assertTrue($res4['result'][0] === $text);
	}

}

