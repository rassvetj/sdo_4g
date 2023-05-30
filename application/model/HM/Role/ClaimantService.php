<?php
class HM_Role_ClaimantService extends HM_Service_Abstract
{

    public function accept($id)
    {

        $order = $this->getOne($this->find($id));
        if ($order) {

            $user = $this->getUser($order);

            if ($user) {
                // Назначение роли студент
                $this->getService('User')->assignRole($user->MID, HM_Role_RoleModelAbstract::ROLE_STUDENT);

                $updateArr =  array(
                    'SID' => $id,
                    'status' => HM_Role_ClaimantModel::STATUS_ACCEPTED
                );
                $subjectId = $order->CID;

                // Назначение на курс
                $this->getService('Subject')->assignStudent($subjectId, $user->MID);

//                $this->delete($id);
				$this->update($updateArr);

                // Отправка сообщения
                $messenger = $this->getService('Messenger');

                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_ORDER_ACCEPTED,
                    array(
                        'subject_id' => $order->CID
                    )
                );

                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $order->MID);
            }
        }
    }

    public function getUser($order)
    {
        if ($order->MID) {
            $user = $this->getOne($this->getService('User')->fetchAll($this->quoteInto('MID = ?', $order->MID)));
            if ($user) {
                // Обновление инфы существующего юзера
                //$user->LastName = $order->lastname;
                //$user->FirstName = $order->firstname;
                //$user->Patronymic = $order->patronymic;
                //$this->getService('User')->update($user->getValues());
            }
        } else if (strlen($order->mid_external)) {
            $user = $this->getOne($this->getService('User')->fetchAll($this->quoteInto('mid_external = ?', $order->mid_external)));
            if ($user) {
                // Обновление инфы существующего юзера
                //$user->LastName = $order->lastname;
                //$user->FirstName = $order->firstname;
                //$user->Patronymic = $order->patronymic;
                //$this->getService('User')->update($user->getValues());
            }
        } else {
            $user = $this->getOne(
                $this->getService('User')->fetchAll(
                    $this->quoteInto(
                        array('LastName = ?', ' AND FirstName = ?', ' AND Patronymic = ?'),
                        array($order->lastname, $order->firstname, $order->patronymic)
                    )
                )
            );
        }

        if (!$user) {
            // Создание пользователя
            $user = $this->getService('User')->insert(
                array(
                     'Login' => $this->getService('User')->generateLogin(),
                     'LastName' => $order->lastname,
                     'FirstName' => $order->firstname,
                     'Patronymic' => $order->patronymic,
                     'mid_external' => $order->mid_external
                )
            );
        }

        return $user;
    }

    public function reject($id, $comments)
    {
        $order = $this->update(
            array(
                'SID' => $id,
                'comments' => $comments,
                'status' => HM_Role_ClaimantModel::STATUS_REJECTED
            )
        );
        if ($order) {
            // Отправка сообщения
            $messenger = $this->getService('Messenger');

            $messenger->setOptions(
                HM_Messenger::TEMPLATE_ORDER_REJECTED,
                array(
                    'subject_id' => $order->CID,
                    'comment'    =>$comments
                )
            );

            $messenger->send(HM_Messenger::SYSTEM_USER_ID, $order->MID);
        }

        return $order;
    }
    
    /**
     * Получаем наименивание типа регистрации по его ИД
     * @param int $typeID
     * @return Ambigous <string, NULL>
     */
    public function getTypeTitle($typeID)
    {
        return HM_Role_ClaimantModel::getType($typeID);
    }
    
    /**
     * Возвращает массив с типами регистрации
     * @return array:NULL 
     */
    public function getTypes()
    {
        return HM_Role_ClaimantModel::getTypes();
    }
    
    /**
     * Возвращает наименование статуса по его ИД
     * @param int $statusID
     * @return NULL
     */
    public function getStatusTitle($statusID){
        return HM_Role_ClaimantModel::getStatus($statusID);
    }
    
    /**
     * Возвращает массив со статусами заявок
     * @return array:NULL 
     */
    public function getStatuses()
    {
        return HM_Role_ClaimantModel::getStatuses();
    }


    /**
     * Нужно для переделки данных, если заявка пришла на базовый курс.
     *
     * @param $data
     */
    public function insert($data)
    {
        if($data['CID'] > 0){
            $subjectId = $data['CID'];
            $subject = $this->getOne($this->getService('Subject')->find($subjectId));
            if($subject && $subject->isBase()){
                unset($data['CID']);
                $data['base_subject'] = $subjectId;
            }
        }

        if ($userId = $this->getService('User')->getCurrentUserId()) {
            $data['created_by'] = $userId;
        } else {
            $data['created_by'] = $data['MID']; // саморегистрация
        }
        
        $insert =  parent::insert($data);

        $this->getService('Process')->startProcess($insert, $subject->claimant_process_id);

        return $insert;
    }
	 /**
     * метод принимает массив array(фамилию, имя, отчество, табельный, MID)
     * @author GlazyrinAE <glazyrin.andre@mail.ru>
     */
	public function updateClaimant()
	{	
		//обновим все индексы дубликатов 
		$resultUpdate = $this->updateWhere(array('dublicate'=>0), array('MID > 0'));//'status'=>0));
		$linkTable = $this->getSelect()->distinct()->from(array('p' => 'claimants'),array('MID'));//->where('status = ?',0);
		$allMid = $linkTable->query()->fetchAll();

		foreach($allMid as $all)
			$str[]= $all['MID'];		
		$count = count($allMid);
		if (!empty($count))
		{
			$allMids = implode(",",$str);	
			$select = $this->getSelect()
                        ->from('People', array('LastName','FirstName'))
                        ->group(array('LastName','FirstName'))
                        ->having('COUNT(*) >= 1')
			            ->where('mid_external = ?', '')
                        ->where('Patronymic = ?', '')
                        ->where("MID IN ($allMids)");
			$r = $select->query()->fetchAll();
		
			if (count($r)==0)
			{
				
				$select = $this->getSelect()->from('People', array('LastName','FirstName','Patronymic'))->group(array('LastName','FirstName','Patronymic'))->having('COUNT(*) > 1')
				->where('mid_external = ?', '')->where("MID IN ($allMids)");
				$r = $select->query()->fetchAll();
				foreach($r as $massmid)
				{
					$lastname    =  $massmid['LastName'];
					$firstname   =  $massmid['FirstName'];
					$patronymic  =  $massmid['Patronymic'];
					$rowUpdate = $this->getService('User')->fetchAll(array('LastName = ?'=>$lastname,'FirstName = ?'=>$firstname,
					'Patronymic = ?'=>$patronymic,'mid_external = ?'=>'',"MID IN ($allMids)"));
					$array = implode(",",$rowUpdate->getList('MID'));
					$data  = array('dublicate' => min($rowUpdate->getList('MID')));
					$where = array('MID IN ('.$array.')');
					$resultUpdateMid = $this->updateWhere($data , $where);		
				}
				$first;			
			}
			
			foreach($r as $m)
			{
				$lastname   =  $m['LastName'];
				$firstname  =  $m['FirstName'];
				$select2 = $this->getSelect()->from('People', array('LastName','FirstName'))->group(array('LastName','FirstName'))->having('COUNT(*) >= 1')
				->where('mid_external = ?', '')->where('LastName = ?',$lastname)->where('FirstName = ?',$firstname)
				->where('Patronymic != ?', '')->where("MID IN ($allMids)");
				$r2 = $select2->query()->fetchAll();
				
				if (count($r2)>0)
				{
					$mid = $this->getService('User')->fetchAll(array('LastName = ?'=>$lastname,'FirstName = ?'=>$firstname,'Patronymic = ?'=>'','mid_external =?'=>'',
					"MID IN ($allMids)"));
					$mid->getList('MID');
					$minmid = min($mid->getList('MID'));
					$all =$this->getService('User')->fetchAll(
						$this->quoteInto(
							array("LastName = ? "," AND FirstName = ? "," AND mid_external = ? AND (Patronymic = '' OR Patronymic != '') AND MID IN ($allMids)"),//" AND (Patronymic = ? OR Patronymic = '' OR Patronymic != '')"," AND mid_external = ? "),
							array($lastname, $firstname,'')
						) 
					);
					if (count($all->getList('MID'))>=1)
					{
						$arrayUpdate = implode(",",$all->getList('MID'));			
						$data  = array('dublicate' => $minmid);
						$where = array('MID IN ('.$arrayUpdate.')');
						$resultUpdate = $this->updateWhere($data , $where);
					}
				}
				
				$second;
				
			}
			if (!isset($first) or !isset($second))
			{
				
				$select = $this->getSelect()->from('People', array('LastName','FirstName','Patronymic'))->group(array('LastName','FirstName','Patronymic'))->having('COUNT(*) > 1')
				->where('mid_external = ?', '')->where("MID IN ($allMids)");
				
				$r = $select->query()->fetchAll();
				foreach($r as $massmid)
				{
					$lastname    =  $massmid['LastName'];
					$firstname   =  $massmid['FirstName'];
					$patronymic  =  $massmid['Patronymic'];
					$rowUpdate = $this->getService('User')->fetchAll(array('LastName = ?'=>$lastname,
					'FirstName = ?'=>$firstname, 'Patronymic = ?'=>$patronymic, 'mid_external =?'=>'', "MID IN ($allMids)"));
					
					$rowOneRecord = $this->getService('User')->fetchAll(array('LastName = ?'=>$lastname,'FirstName = ?'=>$firstname,
						'Patronymic = ?'=>'','mid_external = ?'=>'',"MID IN ($allMids)"));
					if (count($rowOneRecord) == 1)
					{
						$newarray = $rowUpdate->getList('MID');
						array_push($newarray,min($rowOneRecord->getList('MID')));
						$array = implode(",",$newarray);
						$data  = array('dublicate' => min($rowOneRecord->getList('MID')));
						$where = array('MID IN ('.$array.')');				
						$resultUpdateMid = $this->updateWhere($data , $where);	
					}
					else
					{				
						$array = implode(",",$rowUpdate->getList('MID'));
						$data  = array('dublicate' => min($rowUpdate->getList('MID')));
						$where = array('MID IN ('.$array.')');				
						$resultUpdateMid = $this->updateWhere($data , $where);
					}
				}	
			}
			$selectMidExternal = $this->getSelect()->from('People', array('mid_external'))->group(array('mid_external'))
			->having('COUNT(*) > 1')->where('mid_external != ?', '')->where("MID IN ($allMids)");
			$mass = $selectMidExternal->query()->fetchAll();
			foreach($mass as $massmid)
			{
				//$id_user    =  $massmid['MID'];
				$midExternal   =  $massmid['mid_external'];
				$rowUpdateDublicate = $this->getService('User')->fetchAll(array('mid_external = ?'=>$midExternal,"MID IN ($allMids)"));
				$arrayUpdateMid = implode(",",$rowUpdateDublicate->getList('MID'));
				$data  = array('dublicate' => min($rowUpdateDublicate->getList('MID')));
				$where = array('MID IN ('.$arrayUpdateMid.')');
				$resultUpdateMid = $this->updateWhere($data , $where);		
			}
		}	
	}

    /**
     * метод принимает фамилию, имя, отчество, регистрирующегося пользователя 
     * и проверяет наличие пользователя с такими же именем, фамилией и отчеством,
     * а так же табельным номером в таблице people, если пользователь уже 
     * зарегистрирован обновляем поле dublicate с значением поля mid 
     * пользователя у которого совпали фио и табельный номер 
     * @param string $lastName фамилия пользователя
     * @param string $firstName имя пользователя
     * @param string $patronymic отчество пользователя
     * @return integer MID пользователя на которого похож дубликат БД(Table)`People`
     * @author GlazyrinAE <glazyrin.andre@mail.ru>
     */
    public function checkDublicate($lastName, $firstName, $patronymic, $mid, $mid_external)
    {
        
		if (empty($mid_external))
		{
		
		    //формируем данные для запроса на случай если у записи еще нет учитывая фио
			//дубликата и получаем строку из БД(Table) `claimants` с этой записью
			$dataDublNo = array('LastName   = ?'  =>$lastName, 
								'FirstName  = ?'  =>$firstName,
								'Patronymic = ?'  =>$patronymic,
								'dublicate  = ?'  => 0,
								'MID != ?' => $mid);
			$rowDublNo = $this->fetchRow($dataDublNo);
			//формируем данные для запроса на случай если у записи уже есть, как
			//минимум один дубликат и получаем строку из БД(Table)`claimants` с этой записью
			$dataUnicIs = array('LastName   = ?'  =>$lastName, 
								'FirstName  = ?'  =>$firstName,
								'Patronymic = ?'  =>$patronymic,
								'dublicate  = MID');
			$rowDublIs = $this->fetchRow($dataUnicIs); 
		}
		elseif(!empty($mid_external))
		{
			//все тоже самое только анализируем дубликат или нет по табельному номеру mid_external
			$dataDublNo = array('mid_external   = ?'  =>$mid_external, 
								'dublicate  = ?'  => 0,
								'MID != ?' => $mid);
			$rowDublNo = $this->fetchRow($dataDublNo);
			//если у пользователя нет дубликата по критерию поиска таб.номера
			//проверим по критерию поиска фио	
			if (empty($rowDublNo->MID))
			{
				$dataDublNo = array('LastName   = ?'  =>$lastName, 
									'FirstName  = ?'  =>$firstName,
									'Patronymic = ?'  =>$patronymic,
									'dublicate  = ?'  => 0,
									'MID != ?' => $mid);
				$rowDublNo = $this->fetchRow($dataDublNo);	
			}
			
			$dataUnicIs = array('mid_external   = ?'  =>$mid_external, 
								'dublicate  = MID');
			$rowDublIs = $this->fetchRow($dataUnicIs);
			
		}				
		//если дубликата нет на момент регистрации
		if (null !== $rowDublNo)
		{              
			//извлекаем MID у пользователя на которого похож
			//регистрирующийся user
			$valMidDublUpdate = $rowDublNo->MID;
			$data  = array('dublicate' => $valMidDublUpdate);
			$where = array('MID = ?'   => $valMidDublUpdate);
			//обновляем у этого пользователя поле dublicate,
			//теперь, когды мы встретим условие MID = dublicate
			//мы отличим дубликат от уникального пользователя
			//который зарегистрировался раньше дубликата
			$resultUpdate = $this->updateWhere($data , $where);
			if(!empty($resultUpdate))
				return $valMidDublUpdate;
		}
		if (null !==  $rowDublIs)
		{    
			$valMidIs = $rowDublIs->MID;       
			return $valMidIs;
		}
		else 
			return; 
    }
    /**
     * метод предназначен для отделения пользователя-дубликата от уникального
     * -пользователя,не зависимо от того куда мы кликнем по ссылке "объединить",
     * метод позволит всегда найти уникального пользователя, а по нему уже и
     * пользователя на которого кликали по ссылке "объединить".
     * @param integer $midParam MID уникального пользователя 
     * @param integer $dubParam MID пользователя дубликата
     * @return array сток из БД(Table)`People` по дубликату и уникальному user-у
     * @author GlazyrinAE <glazyrin.andre@mail.ru>
     */
   
    public function queryDublicate($midParam, $dubParam)     
    {    
         if (null !==$midParam and null !==$dubParam)
        {        
            $mass = array();
            //Получаем строку по уникальному пользователю
            $rowUnic        = $this->fetchRow(array('MID = ?' => $dubParam));
			$rowUnicPeople	=  $rowUnic->MID;
             //Получаем строку по пользователю - дубликату. 
            $rowDub = $this->fetchRow(array('MID = ?' => $midParam, 'dublicate = ?' => $dubParam));
            $rowDubPeople = $rowDub->MID;
			// если строки равны значит user кликнул по уникальному пользователю
            if($rowUnic == $rowDub)
			{
           // сделаем запрос по первому встречному дубликату    
				$rowDub = $this->fetchRow(array('MID != ?' => $dubParam, 'dublicate = ?' => $dubParam));
				$rowDubPeople = $rowDub->MID;		
			}
			//теперь делаем запрос к таблице `People`
			$rowPastPeopleDateUnical 	= $this->getService('User')->fetchRow(array('MID = ?' => $rowUnicPeople));
			$rowPastPeopleDateDublicate = $this->getService('User')->fetchRow(array('MID = ?' => $rowDubPeople));
            $mass1 = array('lastname'  			=>  $rowPastPeopleDateUnical->LastName, 
							'firstname'   		=>  $rowPastPeopleDateUnical->FirstName,
							'patronymic' 		=>  $rowPastPeopleDateUnical ->Patronymic,
							'gender'     		=>  $rowPastPeopleDateUnical->Gender,
							'year_of_birth'    	=>(( $rowPastPeopleDateUnical->BirthDate == '0000-00-00') ?  '' : substr($rowPastPeopleDateUnical->BirthDate, 0, 4) ),	
							'userlogin'    		=>  $rowPastPeopleDateUnical->Login,
							'email'        		=>  $rowPastPeopleDateUnical ->EMail,
							'midunical'			=>	$rowUnicPeople,
							'middublicate'		=>	$rowDubPeople
                );
			$metadata = $rowPastPeopleDateUnical->getMetadataValues();
            if (count($metadata)) {
                foreach($metadata as $name => $value) {
					$mass1[$name] = $value;
                }
            }
			
            $unitsUnical = $this->getService('Orgstructure')->fetchAll(array('mid = ?' => $rowUnicPeople));
            if (count($unitsUnical)) {
                $mass1['position_id'] = $unitsUnical->current()->soid;
                $mass1['position_name'] = $unitsUnical->current()->name;
                    //pr($form->getElement('position_id')->g)
            }
            
			
			$mass2 = array('lastnameDublicate'  	=>  $rowPastPeopleDateDublicate->LastName, 
							'firstnameDublicate'   	=>  $rowPastPeopleDateDublicate->FirstName,
							'patronymicDublicate' 	=>  $rowPastPeopleDateDublicate ->Patronymic,
							'genderDublicate'     	=>  $rowPastPeopleDateDublicate->Gender, 
							'year_of_birthDublicate'  =>(( $rowPastPeopleDateDublicate->BirthDate == '0000-00-00') ?  '' : substr($rowPastPeopleDateDublicate->BirthDate,0,4) ),
							'userloginDublicate'    	=>  $rowPastPeopleDateDublicate->Login,
							'emailDublicate'        	=>  $rowPastPeopleDateDublicate ->EMail,
							'tel'           =>  $rowPastPeopleDateDublicate->Phone,
							//'position_id' 	=> 0,
							//'position_name'	=>1
                );	
			$metadata =  $rowPastPeopleDateDublicate->getMetadataValues();
            if (count($metadata)) {
                foreach($metadata as $name => $value) {
					$mass2[$name] = $value;
                }
			}
			$unitsDubl = $this->getService('Orgstructure')->fetchAll(array('mid = ?' => $rowDubPeople));
            if (count($unitsDubl)) {
                $mass2['position_id1'] = $unitsDubl->current()->soid;
                $mass2['position_name1'] = $unitsDubl->current()->name;
                    //pr($form->getElement('position_id')->g)
            }

			
			$mass = array($mass1,$mass2);
			
            //$mass[0] = $rowDub;
            //$mass[1] = $rowUnic;
        
            return $mass;
        } 
        else
            return null;
        
    }
    /**
     * метод обрабатывает объединение пользователей.
     * Если дублирующих записей не более одной - дублирующая 
     * запись удаляется в поле dublicate уникальной записи 
     * затирается MID этой записи, если дублирующих записей более
     * одной - дублирующая запись удаляется без обновления
     * статуса у уникальной записи
     * @param integer $midUnic MID уникального пользователя 
     * @param integer $midDub MID пользователя дубликата
     * @return boolean value  
     * @author GlazyrinAE <glazyrin.andre@mail.ru>
     */
    public function deleteDublicate($midDub, $midUnic)
    {
       // echo "дуб=".$midDub;
     //   echo "уникл=".$midUnic;
      //  exit;
        
        if (null !== $midDub and null !== $midUnic)
        {                
            //пытаемся сразу удалить дубликат
            $resultDelete = $this->deleteBy(array('dublicate = ?' => $midUnic, 'MID = ?' => $midDub));
            if (null !== $resultDelete)
            {    
                //проверяем есть ли еще дубликаты
                $rowCheckDubl = $this->fetchRow(array('dublicate = ?' => $midUnic, 'MID != ?' => $midUnic));   
                //если нет то 
                if (null == $rowCheckDubl)
                {    
                    $data  = array('dublicate' => 0);
                    $where = array('MID = ?'   => $midUnic);
                    //обновляем статус уникальной записи очистив поле dublicate 
                    $update = $this->updateWhere($data, $where);
                    if(null !== $update)
                        return true;
                    else 
                        return false;                    
                }
                return true;
            }
            return false;
        } 
        else
            return false;
    }
     public function updateUnic($unicMid, $dublicMid)
    {
        //объявляем пустой массив    
        $arrayCid = array();
        //делаем запрос на все записи у дубликата 
        $rowCid = $this->fetchAll(array('MID = ?' => $dublicMid));              
        //получаем список всех курсов у дубликата
        $arrayCid = $rowCid->getList('CID');  
        //проверяем кол-во курсов у дубликата
        if (count($arrayCid)>0)
        {    
            //проходим по массиву полученных курсов
            foreach($arrayCid as $valCid)
            {
                //если курс существует и не пустое значение, как выяснилось у курсов, которые находятся в сессии значение нулевое
                if (!empty($valCid) || $valCid ==0 )
                {
                    //делаем запрос, а у уникального пользователя есть ли такие же курсы?
                    $result = $this->fetchRow(array('MID = ?' => $unicMid, 'CID = ?' => $valCid, 'base_subject = ?' => 0));  
                    //если есть
                    if (null !== $result)
                    //удаляем такие курсы    
                        $resultDel = $this->deleteBy(array('MID = ?' => $dublicMid , 'CID = ?' => $valCid, 'base_subject = ?' => 0));
                    else 
                    {                    
                        //обновляем запись у дубликата - изменяем его MID на MID уникального пользователя
                        $data  = array('MID' => $unicMid);
                        $where = array('MID = ?' => $dublicMid, 'CID = ?' => $valCid);
                        $resultUpdate = $this->updateWhere($data , $where);                    
                    }
                }                
            }
            
            $rowCheck = $this->fetchAll(array('dublicate = ?' => $unicMid, 'MID != ?' => $unicMid));   
            $update = $rowCheck->getList('MID');
            if (empty($update))
            {
                $dataFilter  = array('dublicate' => 0);
                $whereFilter = array('MID = ?' => $unicMid);
                $resultUpdateFilter = $this->updateWhere($dataFilter , $whereFilter);                           
            }
			//Проверим повторяющиеся курсы на формирование учебной сессии, если такие имеются удалим их
			$rowCheckSession = $this->fetchAll(array('MID = ?' => $unicMid, 'CID = ?' => 0, 'base_subject !=?' => 0));   
            $updateSession = $rowCheckSession->getList('SID');
			if (count($updateSession)>0)
			{
				foreach($updateSession as $valSession)
				{
					$resultSession = $this->fetchAll(array('MID = ?' => $unicMid, 'CID = ?' => 0, 'SID = ?' => $valSession, 'base_subject != ?' => 0));
					if ($resultSession)
					{
						$resultCheckSubject = $resultSession->getList('base_subject');
					//$resultSessionDubl = $this->fetchRow(array('MID = ?' => $unicMid, 'CID = ?' => 0, 'SID = ?' => $valSession, 'base_subject = ?' => min($resultCheckSubject)));
					//if ($resultSessionDubl)
					//{
						$resultDel = $this->deleteBy(array('MID = ?' => $unicMid, 'CID = ?' => 0, 'SID != ?' => $valSession, 
						'base_subject = ?' => min($resultCheckSubject)));
					//}
					}
				}
			
			}
			
            //как только цикл отработал и все значения проанализированы
            //проверим выполнение действий по обновлению и удалению записей,
            //чтобы быть увереным в том, что операции в БД прошли успешно
            if (!empty($resultDel) or !empty($resultUpdate) or !empty($resultUpdateFilter))
                return true;
            //else 
            //    return false;            
        }
        else 
			//return true;
            return false;            
    }
	public function updateDublicate($lastname, $firstname, $patronymic, $lastnameDublicate, $firstnameDublicate, $patronymicDublicate, $midunical)
    {
		$result = $this->fetchAll(array('lastname = ?' => $lastname, 'firstname = ?' => $firstname, 'patronymic = ?'=>$patronymic));
		$countDublicate = $result->getList('MID');
		if (count($countDublicate)==1)
		{
			$dataFilter  = array('dublicate' => 0);
            $whereFilter = array('lastname = ?' => $lastname, 'firstname = ?' => $firstname, 'patronymic = ?'=>$patronymic);
            $resultUpdateFilter = $this->updateWhere($dataFilter , $whereFilter); 		
		}
		elseif(count($countDublicate)>1)
		{
			$dataFilterTwo  = array('dublicate' => min($countDublicate));
            $whereFilterTwo = array('lastname = ?' => $lastname, 'firstname = ?' => $firstname, 'patronymic = ?'=>$patronymic);
			$resultUpdateDublicateTwo = $this->updateWhere($dataFilterTwo , $whereFilterTwo); 
		}
		//////////////////////////////////////////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////
		$researchRests = $this->fetchAll(array('dublicate = ?' => $midunical,
		'lastname = ?' => $lastnameDublicate, 'firstname = ?' => $firstnameDublicate, 'patronymic = ?'=>$patronymicDublicate));
		$countmid = $researchRests->getList('MID');
		$updateIdDublicate = $researchRests->getList('dublicate');
		$min =  min($countmid);
		if(count($countmid)==1)//потом проверим есть ли еще дубликаты помеченные полем dublicate
		{					
			$dataOne  = array('dublicate' => 0);
            $whereOne = array('MID = ?' => $min);
            $resultUpdateOne = $this->updateWhere($dataOne, $whereOne);  		
		}
		elseif(count($countmid)>1)
		{
			$data  = array('dublicate' => $min);
            $where = array('dublicate = ?' => $midunical,'dublicate = ?' => $midunical,
			'lastname = ?' => $lastnameDublicate, 'firstname = ?' => $firstnameDublicate, 'patronymic = ?'=>$patronymicDublicate);
            $resultUpdate = $this->updateWhere($data , $where);   	
		}
		
	}	

}