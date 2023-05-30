<?php
class HM_Role_StudentService extends HM_Service_Abstract
{

    public function insert($data)
    {
        if (!isset($data['time_registered'])) {
            $data['time_registered'] = $this->getDateTime();
        }

        return parent::insert($data);
    }

    public function isUserExists($subjectId, $userId)
    {
        $collection = $this->fetchAll(array('CID = ?' => $subjectId, 'MID = ?' => $userId)
            
            //$this->quoteInto(array('CID = ?', 'MID = ?'), array($subjectId, $userId))
        );
        return count($collection);
    }
    
    /**
     * Получить список ID всех пользователей предмета по ID предмета
     * 
     * @param numeric $subjectId
     * @return array
     */
    public function getUsersIds($subjectId)
    {
        $collection = $this->fetchAll(array('CID = ?' => $subjectId));
        return $collection->getList('MID');
    }

    public function getSubjects($userId = null)
    {
        if (!$userId) $userId = $this->getService('User')->getCurrentUserId();
        $collection = $this->fetchAll(array('MID = ?' => $userId));
        $list = $collection->getList('CID','MID');
        if (!count($list)) {
            $list = array(0 => 0);
        }
        return $this->getService('Subject')->fetchAll(array('subid IN (?)' => array_keys($list)), 'name');
        
        /*return $this->getService('Subject')->fetchAllDependenceJoinInner(
            'Student',
            $this->quoteInto('Student.MID = ?', $userId),
            'self.name'
        );*/
        
    }
    
    /**
     *  метод принимает mid дубликата и mid уникальной записи
     *  проверяем совпадают ли курсы у mid дубликат в таблице
     *  student-если да то просто удаляем эту запись, не нарушая 
     *  целостность данных в таблтитце.Затем добавляем все
     *  курсы дубликата-dublicMid уникальному-unicMid
     *  @param integer unicMid - MID уникального пользователя
     *  @param integer dublicMid - MID пользователя-дубликата
     *  @return boolean type 
     *  @author GlazyrinAE 
     */
    public function updateUnic($unicMid, $dublicMid)
    {
        //объявляем пустоц массив    
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
                //если курс существует и не пустое значение
                if (!empty($valCid))
                {
                    //делаем запрос, а у уникального пользователя есть ли такие же курсы?
                    $result = $this->fetchRow(array('MID = ?' => $unicMid, 'CID = ?' => $valCid));  
                    //если есть
                    if (null !== $result)
                    //удаляем такие курсы    
                        $resultDel = $this->deleteBy(array('MID = ?' => $dublicMid , 'CID = ?' => $valCid));
                    else 
                    {                    
                        //обновляем запись у дубликата - изменяем его MID на MID уникального пользователя
                        $data  = array('MID' => $unicMid);
                        $where = array('MID = ?' => $dublicMid, 'CID = ?' => $valCid);
                        $resultUpdate = $this->updateWhere($data , $where);                    
                    }
                }                
            }
            //как только цикл отработал и все значения проанализированы
            //проверим выполнение действий по обновлению и удалению записей,
            //чтобы быть увереным в том, что операции в БД прошли успешно
            if (!empty($resultDel) or !empty($resultUpdate))
                return true;
            else 
                return false;            
        }
        else            
            return false;            
    }

    public function getUserIdFromStudentId($studentId, $subjectId) {
        /** @var HM_Role_StudentModel $student */
        $student = $this->getOne($this->fetchAll(
            array(
                'CID = ?' => $subjectId,
                'SID = ?' => $studentId
            )
        ));
        return $student->MID;
    }
	
	/**
	 * Кол-во студентов, назначенных на курс. Не учитывает заблокированных и несуществующих студентов
	 * @return int
	*/
	public function getAssignStudents($subject_id){
		return $this->fetchAll(array('CID = ?' => $subject_id))->getList('MID');
	}
	
	/**
	 * Получаем все причины неназначения студента на сессию
 	 * @return array
	 *
	*/
	public function getAssignErrors($userId, $subjectId)
	{
		$errors  = array();
		$user    = $this->getService('User')->getById($userId);		
		$subject = $this->getOne($this->getService('Subject')->findDependence(array('Student'), $subjectId));
		
		if($subject->isStudent($userId)){
			$errors[HM_Role_StudentModel::ERR_ALREADY_ASSIGN] = HM_Role_StudentModel::ERR_ALREADY_ASSIGN;
		}
		
		if(!$this->isSameBeginLearning($subject, $user)){
			$errors[HM_Role_StudentModel::ERR_BEGIN_LEARNING] = HM_Role_StudentModel::ERR_BEGIN_LEARNING;
		}
		
		if(!$this->isSameLanguage($subject, $user)){
			$errors[HM_Role_StudentModel::ERR_LANGUAGE] = HM_Role_StudentModel::ERR_LANGUAGE;
		}
		return $errors;
	}
	
	public function isSameBeginLearning($subject, $user)
	{
		# для старых сессий дату начала обучения не учитываем
		if($subject->isOld()){ return true; }
		
		return $subject->getBeginLearning() != $user->getBeginLearning() ? false : true;
	}
	
	public function isSameLanguage($subject, $user)
	{
		if(!$subject->isLanguage()){ return true; }
		
		$user_language_code = $this->getService('LanguagesAssignBase')->getCurrentLanguageCode($user->mid_external, $subject->semester, $subject->language_code);
		
		if($user_language_code == $subject->language_code){
			return true;
		}
		
		return false;
	}
	
	public function getAssignErrorsAsText($userId, $subjectId)
	{
		$errors = $this->getAssignErrors($userId, $subjectId);
		if(empty($errors)){ return false; }
		
		$data = array();		
		foreach($errors as $code){
			$data[$code] = HM_Role_StudentModel::getErrorText($code);
		}
		return $data;
	}
	
	# TODO преобразование кодов ошибок назначений в объект $this->_flashMessenger					
    #
	#
	
}