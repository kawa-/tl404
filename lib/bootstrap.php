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
	'subscribe',
	'unsubscribe',
);

define('IS_DEBUG', TRUE); /* TRUE => DEBUG, FALSE => PRODUCTION */
define('LIMIT', 3); /* # of contents that you can get in one query like "get_random_subscribers" */
define('LIMIT_COUNT', 5);
define('MAX_ELEMENT_SIZE', 256); /* The restriction of the element size */

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
require dirname(__FILE__) . '/../method/subscribe.php';
require dirname(__FILE__) . '/../method/unsubscribe.php';

