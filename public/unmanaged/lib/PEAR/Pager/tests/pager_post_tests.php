<?php
// $Id: pager_post_tests.php,v 1.1.2.2 2009/10/29 08:34:13 cvsup Exp $

require_once 'simple_include.php';
require_once 'pager_include.php';

$test = &new GroupTest('Pager POST tests');
$test->addTestFile('pager_post_test.php');
exit ($test->run(new HTMLReporter()) ? 0 : 1);

?>