<?php

/**
 * 全ての RPC を正常系のみテスト (異常系は個別のテストを参照)。
 *
 * これを実行する条件:
 * - mutecity/pub のディレクトリに移動して、localhost:9080 で立ち上げ
 * - Redis をlocalhost:9736 で立ち上げ
 * - RabbitMQと./worker/distributer.phpを走らせる
 * - lib/bootstrap.php の IS_DEBUG を TRUE にしておくこと。FALSEだとテストが実行されない。
 *
 * 注意点:
 * - データはすべて消える
 * - trimmer.phpのテストは、現在のところ省略。というのも一般に時間が掛かるため(一定周期で削除する都合上、、、)
 *
 * テスト項目:
 * - barusu
 * - subscribe
 * - unsubscribe
 * - get_all_subscriptions
 * - get_all_subscribers
 * - get_number_of_subscriptions
 * - get_number_of_subscribers
 * - get_subscriptions
 * - get_subscribers
 * - is_my_subscriber
 * - is_my_subscription
 * - put (+ get_timeline)
 * - publish (+ get_timeline)
 * - delete_id
 * - delete_tl
 * - register_id
 * - register_tlid
 * - unregister_tlid
 *
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

	public function testGetNumberOfSubscriptions() {
		$res = httpPostWithDecode('get_number_of_subscriptions', array('sid' => 2));
		$this->assertEquals($res['result'], 1);
	}

	public function testGetNumberOfSubscribers() {
		$res = httpPostWithDecode('get_number_of_subscribers', array('sid' => 1));
		$this->assertEquals($res['result'], 3);
	}

	public function testGetSubscriptions() {
		$res = httpPostWithDecode('get_subscriptions', array('sid' => 2, 'ofs' => 0, 'cnt' => 1));
		$subscriptions = $res['result'];
		$this->assertTrue(array(1) === $subscriptions);
	}

	/**
	 * 最新のものから2件だけ取得 (testGetAllSubscribersの方は、全件、つまり3件取得している。そこが相違点)。
	 * arrayの順番に注目、新しいものがindex=0に入ってくるので、testGetAllSubscribersとは逆順になっている。
	 */
	public function testGetSubscribers() {
		$res = httpPostWithDecode('get_subscribers', array('sid' => 1, 'ofs' => 0, 'cnt' => 2));
		$subscriptions = $res['result'];
		$this->assertTrue(array(4, 3) === $subscriptions);
	}

	/*
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
	 */

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

	public function testPublish() {
		$text = 'id:' . uniqid() . '|sid:1';
		$res1 = httpPostWithDecode('publish', array('sid' => 1, 'tlid' => 0, 'elm' => $text));
		$this->assertTrue($res1['result']);
		sleep(1);
		$res2 = httpPostWithDecode('get_timeline', array('sid' => 2, 'tlid' => 0));
		$this->assertTrue($res2['result'][0] === $text);
		$res3 = httpPostWithDecode('get_timeline', array('sid' => 3, 'tlid' => 0));
		$this->assertTrue($res3['result'][0] === $text);
		$res4 = httpPostWithDecode('get_timeline', array('sid' => 4, 'tlid' => 0));
		$this->assertTrue($res4['result'][0] === $text);
	}

	public function testDeleteID() {
		httpPostWithDecode('subscribe', array('sid' => 100, 'tid' => 201));
		httpPostWithDecode('subscribe', array('sid' => 100, 'tid' => 202));
		httpPostWithDecode('subscribe', array('sid' => 100, 'tid' => 203));
		httpPostWithDecode('subscribe', array('sid' => 201, 'tid' => 100));
		httpPostWithDecode('subscribe', array('sid' => 202, 'tid' => 100));
		httpPostWithDecode('subscribe', array('sid' => 203, 'tid' => 100));
		$res = httpPostWithDecode('delete_id', array('sid' => 100));
		$this->assertEquals(20000, $res['code']);
		$res2 = httpPostWithDecode('is_my_subscriber', array('sid' => 100, 'tid' => 1001));
		$this->assertFalse($res2['result']);
	}

	public function testDeleteTL() {
		/* generate TL */
		httpPostWithDecode('put', array('sid' => 100, 'tlid' => 1001, 'elm' => 'foobar1001'));
		httpPostWithDecode('put', array('sid' => 100, 'tlid' => 1002, 'elm' => 'foobar1002'));
		httpPostWithDecode('put', array('sid' => 100, 'tlid' => 1003, 'elm' => 'foobar1003'));

		/* delete TL */
		$res1001 = httpPostWithDecode('delete_tl', array('sid' => 100, 'tlid' => 1001));
		$res1002 = httpPostWithDecode('delete_tl', array('sid' => 100, 'tlid' => 1002));
		$res1003 = httpPostWithDecode('delete_tl', array('sid' => 100, 'tlid' => 1003));
		$this->assertTrue($res1001['result']);
		$this->assertTrue($res1002['result']);
		$this->assertTrue($res1003['result']);
	}

	public function testRegisterID() {
		$res1 = httpPostWithDecode('register_id', array('sid' => 1));
		$res2 = httpPostWithDecode('register_id', array('sid' => 2));
		$res3 = httpPostWithDecode('register_id', array('sid' => 3));
		$res4 = httpPostWithDecode('register_id', array('sid' => 4));
		$this->assertTrue($res1['result']);
		$this->assertTrue($res2['result']);
		$this->assertTrue($res3['result']);
		$this->assertTrue($res4['result']);

		/* 適当にユーザーを登録して、削除出来るか確かめる */
		httpPostWithDecode('register_id', array('sid' => 999));
		$res999 = httpPostWithDecode('delete_id', array('sid' => 999));
		$this->assertTrue($res999['result']);
	}

	public function testRegisterTLID() {
		$res1 = httpPostWithDecode('register_tlid', array('tlid' => 0));
		$res2 = httpPostWithDecode('register_tlid', array('tlid' => 1));
		$res3 = httpPostWithDecode('register_tlid', array('tlid' => 2));
		$res4 = httpPostWithDecode('register_tlid', array('tlid' => 3));
		$this->assertTrue($res1['result']);
		$this->assertTrue($res2['result']);
		$this->assertTrue($res3['result']);
		$this->assertTrue($res4['result']);
	}

	public function testUnRegisterTLID() {
		$res1 = httpPostWithDecode('unregister_tlid', array('tlid' => 1));
		$res2 = httpPostWithDecode('unregister_tlid', array('tlid' => 2));
		$res3 = httpPostWithDecode('unregister_tlid', array('tlid' => 3));
		$this->assertTrue($res1['result']);
		$this->assertTrue($res2['result']);
		$this->assertTrue($res3['result']);
	}

}


