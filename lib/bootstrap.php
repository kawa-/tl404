<?php

$methods = array(
	'barusu',
	'delete_id',
	'delete_tl',
	'get_all_subscribers',
	'get_all_subscriptions',
	'get_number_of_subscribers',
	'get_number_of_subscriptions',
	'get_subscribers',
	'get_subscriptions',
	'get_timeline',
	'is_my_subscriber',
	'is_my_subscription',
	'publish',
	'put',
	'register_id',
	'register_tlid',
	'subscribe',
	'unregister_tlid',
	'unsubscribe',
);

define('IS_DEBUG', TRUE); /* TRUE => DEBUG, FALSE => PRODUCTION. if TRUE, BARUSU command is available */
define('LIMIT_COUNT', 5); /* The limit to take ids in get_subscribers/get_subscriptions */
define('LIMIT_TL_ELMS', 5); /* The limit of elements of a timeline */
define('MAX_ELEMENT_SIZE', 256); /* The restriction of the element size */
define('MAX_SUBSCRIPTIONS', 10); /* The number you can follow */
define('INTERVAL_SECONDS', 3); /* The interval second of trimer.php */
define('PARAM_SOURCE_ID', 'sid');
define('PARAM_TARGET_ID', 'tid');
define('PARAM_LIMIT', 'lim');
define('PARAM_OFFSET', 'ofs');
define('PARAM_COUNT', 'cnt');
define('PARAM_ELEMENT', 'elm');
define('PARAM_BARUSU', 'barusu');
define('PARAM_TLID', 'tlid');

define('SERVER_HOST', 'localhost');
define('SERVER_PORT', '9080');
define('REDIS_HOST', 'localhost');
define('REDIS_PORT', '9736');
define('REDIS_TIMEOUT', 2.0);

require dirname(__FILE__) . '/Bye.php';
require dirname(__FILE__) . '/DBH.php';
require dirname(__FILE__) . '/../method/barusu.php';
require dirname(__FILE__) . '/../method/delete_id.php';
require dirname(__FILE__) . '/../method/delete_tl.php';
require dirname(__FILE__) . '/../method/get_all_subscribers.php';
require dirname(__FILE__) . '/../method/get_all_subscriptions.php';
require dirname(__FILE__) . '/../method/get_number_of_subscribers.php';
require dirname(__FILE__) . '/../method/get_number_of_subscriptions.php';
require dirname(__FILE__) . '/../method/get_subscribers.php';
require dirname(__FILE__) . '/../method/get_subscriptions.php';
require dirname(__FILE__) . '/../method/get_timeline.php';
require dirname(__FILE__) . '/../method/is_my_subscriber.php';
require dirname(__FILE__) . '/../method/is_my_subscription.php';
require dirname(__FILE__) . '/../method/publish.php';
require dirname(__FILE__) . '/../method/put.php';
require dirname(__FILE__) . '/../method/register_id.php';
require dirname(__FILE__) . '/../method/register_tlid.php';
require dirname(__FILE__) . '/../method/subscribe.php';
require dirname(__FILE__) . '/../method/unregister_tlid.php';
require dirname(__FILE__) . '/../method/unsubscribe.php';

