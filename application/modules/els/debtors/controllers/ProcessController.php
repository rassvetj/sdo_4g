<?php
class Debtors_ProcessController extends HM_Controller_Action
{
    private $_importService = null;
    protected $_importManagerClass = 'HM_Debtors_Import_Manager';

    
	# Стандартный импорт
	public function baseAction(){
		$this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
		
		
		$importManager = new HM_Debtors_Import_Manager();
        $importManager->restoreFromCache();
		$isImport = $importManager->importBase();
		
		$isLessonAssignRemove	= $importManager->removeLessonAssignTutors();		
		$msg 					= $isLessonAssignRemove ? 'Удалены назначения тьюторов за занятиями' : 'Ни одно закрепление тьюторов за занятиями не было удалено';
		
		echo json_encode(array(
			'message' => 'Было импорировано '.$isImport.' из '.count($importManager->getUpdateData()).' записей. '.$msg,
		));
		#echo 'Было импорировано '.$isImport.' из '.count($importManager->getUpdateData()).' записей';
		die;
	}
	
	
	
	# импорт прошедших обучение, которые не набрали проходной балл.
	public function graduateAction(){
		$this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
		
		
		$importManager = new HM_Debtors_Import_Manager();
        $importManager->restoreFromCache();
		$isImport = $importManager->importGraduated();
		
		
		echo json_encode(array(
			'message' => 'Было импорировано '.intval($isImport).' из '.count($importManager->getGraduatedDebtors()).' записей',
		));
		#echo 'Было импорировано '.$isImport.' из '.count($importManager->getUpdateData()).' записей';
		die;
	}
	
	
	# импорт прошедших обучение, которые не набрали проходной балл.
	public function graduatePassedAction(){
		$this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
		
		
		$importManager = new HM_Debtors_Import_Manager();
        $importManager->restoreFromCache();
		$isImport = $importManager->importGraduatedPass();
		
		
		echo json_encode(array(
			'message' => 'Было импорировано '.intval($isImport).' из '.count($importManager->getGraduatedDebtorsEnded()).' записей',
		));
		#echo 'Было импорировано '.$isImport.' из '.count($importManager->getUpdateData()).' записей';
		die;
	}
	
	public function assignTutorSubjectAction(){		
		$this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
		
		$importManager = new HM_Debtors_Import_Manager();
        $importManager->restoreFromCache();
		$isImport = $importManager->importAssignTutorSubject();
		
		
		echo json_encode(array(
			'message' => 'Было назначено '.intval($isImport).' из '.count($importManager->getTutorsAssignSubjects()).' записей',
		));		
		die;		
	}
	
	public function assignTutorGroupAction(){		
		$this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
		
		$importManager = new HM_Debtors_Import_Manager();
        $importManager->restoreFromCache();
		$isImport = $importManager->importAssignTutorGroup();
		
		
		echo json_encode(array(
			'message' => 'Было назначено '.intval($isImport).' из '.count($importManager->getTutorsAssignGroups()).' записей',
		));		
		die;		
	}
	
	
	public function updateTutorSubjectAction(){		
		$this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
		
		$importManager = new HM_Debtors_Import_Manager();
        $importManager->restoreFromCache();
		$isImport = $importManager->importUpdateTutorSubject();
		
		
		echo json_encode(array(
			'message' => 'Было изменено '.intval($isImport).' из '.count($importManager->getTutorsUpdateSubjects()).' записей',
		));		
		die;		
	}
	
	
	/**
	 * удаляет дату продления лишних тьюторов. Политика следующая: в каждый момент времени на сессии может быть только один тьютор каждого из продлений и сколько угодно обычных тьюторов.
	*/
	public function setBaseRoleTutorsAction(){		
		$this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
		
		$importManager = new HM_Debtors_Import_Manager();
        $importManager->restoreFromCache();
		$isImport = $importManager->setBaseRoleTutors();
		
		echo json_encode(array(
			'message' => 'Операция выполнена',
		));		
		die;		
	}
	
	/**
	 * удаляет все закрепления на занятия в сессии для указанных в csv сессий и тьюторов. Т.е. это удаление разлеление на роли лаборант/практик/лектор
	*/
	public function removeLessonAssignTutorsAction(){		
		$this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
		
		$importManager = new HM_Debtors_Import_Manager();
        $importManager->restoreFromCache();
		$isImport 	= $importManager->removeLessonAssignTutors();		
		$msg 		= $isImport ? 'Операция выполнена' : 'Ни одно закрепление не было изменено';
		
		echo json_encode(array(
			'message' => $msg,
		));		
		die;		
	}
	
	
}