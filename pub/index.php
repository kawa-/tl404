<?php

require dirname(__FILE__) . '/../lib/bootstrap.php';

if (!isset($_POST['method']) or !in_array($_POST['method'], $methods)) {
	Bye::ifInvalidMethod();
}

call_user_func($_POST['method']);

