<?php
//class Cardstudent_IndexController extends HM_Controller_Action
class StudyCard_IndexController extends HM_Controller_Action_Crud
{
	protected $_studyCardService = null;

    protected $_studyCardID  = 0;
	
	public function init(){
		
		$this->_studyCardID = (int) $this->_getParam('study_card_id', 0);
        //$this->_studyCardService = $this->getService('Cardstudent');
        $this->_studyCardService = $this->getService('StudyCard');
		
        parent::init();		
	}
	
	public function indexAction()
    {	
		
		
		$config = Zend_Registry::get('config');
		$this->view->setHeader(_('Учебная карточка'));
		
		$this->getHelper('viewRenderer')->setNoRender();
		
		
		$select = $this->_studyCardService->getIndexSelect();
		
		$where = $this->_studyCardService->quoteInto(
			array('StudyCode=?'),
			array(				
				$this->getService('User')->getCurrentUser()->mid_external,
			)
		);
		$select->where($where);
		
		$gridId = 'grid';
		
		$grid = $this->getGrid(
            $select,
            array(                                
                'Hours' 	=> array('hidden' => true),
                'Company' 	=> array('hidden' => true),
                'Position' 	=> array('hidden' => true),
                'Manager' 	=> array('hidden' => true),
				
				'StudyCode' 	=> array('hidden' => true),
				'Teacher' 		=> array('hidden' => true),
				//'UopInfoID'	=> array('title' => _('ID')), 
				'UopInfoID'	=> array('hidden' => true),
				'Disciplina'	=> array('title' => _('Название дисциплины')), 				
				/*
				'Ball'	=> array(
					'title' => _('Оценка'),
					'callback' => array('function' => array($this, 'updateBall'), 'params' => array('{{Ball}}')),
				), 
				*/
				//'Ball'	=> array('title' => _('Балл')), 
				'Ball'	=> array(
					'title' => _('Оценка'),
					'callback' => array('function' => array($this, 'updateBall'), 'params' => array('{{Ball}}')),
				),

				'Mark'	=> array(
					'title' => _('Оценка'),
					'callback' => array('function' => array($this, 'updateMark'), 'params' => array('{{Mark}}')),
				),
				
				'NumPop'	=> array('title' => _('Попытка')), 
				'DocNum'	=> array('title' => _('Номер документа')), 
				'Type'	=> array(
					'title' => _('Тип'),
					'callback' => array('function' => array($this, 'updateType'), 'params' => array('{{Type}}')),
				), 
				'Date'	=> array('title' => _('Дата')), 
				'Vid'	=> array('hidden' => true), 
            ),
            array(
				//'Type' => array('values' => HM_Cardstudent_CardstudentModel::getTypes()),
				'Type' => array('values' => HM_StudyCard_StudyCardModel::getTypes()),
				//'Ball' => array('values' => HM_Cardstudent_CardstudentModel::getBall()),
				//'Ball' => array('values' => HM_StudyCard_StudyCardModel::getBall()),
				//'Mark' => array('values' => HM_StudyCard_StudyCardModel::getMark()),				
				'Ball' => null,
				'Disciplina' => null,
				'DocNum' => null,
				//'Date' => null,								
				'Date' => array('render' => 'DateSmart'),	
            ),
            $gridId
        );	
		
		$grid->updateColumn('Date', array(
            'format' => array(
                'Date',                
                array('date_format' => Zend_Locale_Format::getDateFormat())
															 
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{Date}}')
            )
        )
        );
		
		$content_grid = $grid->deploy();
		
		echo $content_grid;
		
	}
	
	public function updateType($type) {        
        $types = HM_StudyCard_StudyCardModel::getTypes();
        return $types[$type];
    }
	
	public function updateBall($ball) {
        
		$ball_i = intval($ball);
		
		if($ball_i < 1) return '';
		
		return $ball_i;		
    }
	
	public function updateMark($mark) {     
        $marks = HM_StudyCard_StudyCardModel::getMark();
        return $marks[$mark];
    }
	
	public function updateDate($date)
    {        
		if (!strtotime($date)) return '';
		
        return $date;
    }
}