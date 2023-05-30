<?php

	
	$CRON_DIR            = __DIR__.'/../../../';
	$init_path           = $CRON_DIR.'init/_init.php';
	$_dataFilePath       = realpath(__DIR__ . '/data_new.xml');
	$isExcludeEmptyValue = true;                        # если новое значение поля пустое, исключять его из обновления
	$searchField         = 'name';              # поле, по которому искать сущестующую запись.
	$primaryKey          = 'requisite_id';              # Первичный ключ. Удаляем перед обновлением.
	$executeFrom		 = date('2020-12-31 23:59:59'); # выполнить не ранее этой даты
	
	if(strtotime($executeFrom) > time()){
		echo 'ERR: Script can be executed after ' . $executeFrom;
		die;
	}
	
	
	if(!file_exists($init_path)){
		echo 'ERR: init file not found' . "\n";
		exit();
	}
	require_once $init_path;
	
	if(!file_exists($_dataFilePath)){
		echo 'ERR: data file not found' . "\n";
		exit();
	}

	$xml = simplexml_load_file($_dataFilePath);	
	
	if(empty($xml)){
		echo 'ERR: Data is empty' . "\n";
		die;
	}
	
	$serviceRequisite = Zend_Registry::get('serviceContainer')->getService('TicketRequisite');
		
	foreach($xml as $i){
		$data = array(
			'requisite_id'    => trim(strval($i->requisite_id)),
			'name'            => trim(strval($i->name)),
			'recipient'       => trim(strval($i->recipient)),
			'inn'             => trim(strval($i->inn)),
			'kpp'             => trim(strval($i->kpp)),
			'oktmo'           => trim(strval($i->oktmo)),
			'bik'             => trim(strval($i->bik)),
			'cbc'             => trim(strval($i->cbc)),
			'bank_account'    => trim(strval($i->bank_account)),
			'bank_recipient'  => trim(strval($i->bank_recipient)),
			'note'            => trim(strval($i->note)),
			'personalaccount' => trim(strval($i->personalaccount)),
		);
		
		if($isExcludeEmptyValue){
			$data = array_filter($data);
		}
		
		if(empty($data)){
			echo 'ERR: Row data is empty' . "\n";
			continue;
		}
		
		$criteria   = $serviceRequisite->quoteInto( $searchField . ' = ?', $data[$searchField] );
		$existsItem = $serviceRequisite->getOne($serviceRequisite->fetchAll($criteria));
		
		if(!$existsItem){
			echo 'ERR: Exists item not found by criteria: ' . $criteria . "\n";			
			continue;
		}
		
		unset($data[$primaryKey]);
		
		$criteria   = $serviceRequisite->quoteInto( $primaryKey . ' = ?', $existsItem->{$primaryKey} );
		$isUpdated  = $serviceRequisite->updateWhere(
            $data,
            $criteria
        );
		
		if(!$isUpdated){
			echo 'ERR: Cant update item: ' . $criteria . "\n";
			continue;
		}
		
		echo 'OK: Update item: ' . $criteria . "\n";
	}
	
	echo 'Done' . "\n";	
	echo 'Exit' . "\n";	
	die;
?>
