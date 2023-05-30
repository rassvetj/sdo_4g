#!/usr/bin/env php
<?php
	$pathInit = __DIR__ . '/../../init/_init.php';

	if(!file_exists($pathInit)){
		echo 'Error: init file not found';
		die(PHP_EOL);
	}	
	require_once $pathInit;
	
	echo PHP_EOL . 'start' . PHP_EOL;
	
	if(!class_exists('HM_User_Import_Student_Manager')){
		die('class HM_User_Import_Student_Manager not found' . PHP_EOL);
	}
	$manager = new HM_User_Import_Student_Manager();
	$manager->import();
	$manager->sendReport();
	
	die(PHP_EOL . 'end' . PHP_EOL);
?>