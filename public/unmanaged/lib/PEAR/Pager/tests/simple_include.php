<?php
// $Id: simple_include.php,v 1.1.2.2 2009/10/29 08:34:13 cvsup Exp $
//
// This testsuite requires SimpleTest.
// You can find it here:
// http://www.lastcraft.com/simple_test.php
//
if (!defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', '../simpletest/');
}

require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
require_once(SIMPLE_TEST . 'mock_objects.php');
require_once(SIMPLE_TEST . 'web_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
?>