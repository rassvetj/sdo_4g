<?php
class Report_TimetableController extends HM_Controller_Action_Crud
{
	public function init()
    {
        parent::init();
       
        $this->getService('Unmanaged')->setHeader(_('Отчет о заполнении расписания'));
    }
	
	
    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');		
		$this->view->form = new HM_Form_Timetable();		
    }
	
	public function getAction()
    {		
		$request	= $this->getRequest();
		$dateFrom   = $request->getParam('dateFrom', 0);
		$dateTo		= $request->getParam('dateTo', 0);
		
		$dateFrom = $dateFrom ? str_replace(array('%2E', '_'), '.', $dateFrom) : $dateFrom;
		$dateTo   = $dateTo   ? str_replace(array('%2E', '_'), '.', $dateTo)   : $dateTo;
				
		$dtFrom       = DateTime::createFromFormat('d.m.Y', $dateFrom);
		$dtFromFormat = ($dtFrom) ? $dtFrom->format('Y-m-d') : false;
		
		$dtTo       = DateTime::createFromFormat('d.m.Y', $dateTo);
		$dtToFormat = ($dtTo) ? $dtTo->format('Y-m-d') : false;
		
		if(!$dtFromFormat){ die('Укажите дату "С"');  }
		if(!$dtToFormat)  { die('Укажите дату "По"'); }
		
		$serviceReport	= $this->getService('Report');
		$select 		= $serviceReport->getSelect();
		
		$fields = array(
			'timetable_id' 	=> 't.timetable_id',
			'mid_external' 	=> 't.mid_external',
			'Time_1' 		=> 't.Time_1',
			'Time_2' 		=> 't.Time_2',
			'Caption' 		=> 't.Caption',
			'Groups' 		=> 't.Groups',
			'group_code' 	=> 't.group_code',
			'Place' 		=> 't.Place',
			'Name' 			=> 't.Name',
			'Teacher' 		=> 't.Teacher',
			'Type' 			=> 't.Type',
			'Rank' 			=> 't.Rank',
			'Nch' 			=> 't.Nch',
			'Day' 			=> 't.Day',
			'Faculty' 		=> 't.Faculty',
			'Form' 			=> 't.Form',
			'DateZ' 		=> 't.DateZ',
			'link' 			=> 'ta.link',
			'users' 		=> 'ta.users',
			'file_path' 	=> 'ta.file_path',
			'subject_path' 	=> 'ta.subject_path',
			'date_updated' 	=> 'ta.date_updated',
			'link2' 		=> 'ta.link2',
			'link3' 		=> 'ta.link3',
		);
		
		$select->from(array('t' => 'timetable'), $fields);
		$select->joinLeft(array('ta' => 'timetable_additional'), 'ta.external_id = t.external_id', array());
		$select->where($this->quoteInto('t.DateZ >= ?', $dtFromFormat));
		$select->where($this->quoteInto('t.DateZ <= ?', $dtToFormat));
		
		$grid = $this->getGrid(
            $select,
            array(
                'timetable_id'	=> array('hidden' => true),
				'mid_external'	=> array('title' => _('Тьютор. Код')),
				'Time_1'		=> array('title' => _('Время. С')),
				'Time_2'		=> array('title' => _('Время. По')),
				'Caption'		=> array('title' => _('Пара')),
				'Groups'		=> array('title' => _('Группа')),
				'group_code'	=> array('title' => _('Группа. Код')),
				'Place'			=> array('title' => _('Аудитория')),
				'Name'			=> array('title' => _('Дисциплина')),
				'Teacher'		=> array('title' => _('Преподаватель')),
				'Type'			=> array('title' => _('Тип')),
				'Rank'			=> array('title' => _('Звание')),
				'Nch'			=> array('title' => _('Неделя')),
				'Day'			=> array('title' => _('День недели')),
				'Faculty'		=> array('title' => _('Факультет')),
				'Form'			=> array('title' => _('Форма')),
				'DateZ'			=> array('title' => _('Дата')),
				'link'			=> array('title' => _('On-line занятие')),
				'users'			=> array('title' => _('Слушатели')),
				'file_path'		=> array('title' => _('Запись трансляции')),
				'date_updated'	=> array('title' => _('Дата изменения')),
				'link2'			=> array('hidden' => true),
				'link3'			=> array('hidden' => true),
            ),
            array(
                'mid_external'	=> null,
                'mid_external'	=> null, 
				'Time_1' 		=> array('values' => HM_Timetable_TimetableModel::getListTimeBegin()),
				'Time_2' 		=> array('values' => HM_Timetable_TimetableModel::getListTimeEnd()),
				'Caption' 		=> array('values' => HM_Timetable_TimetableModel::getListAcademicHours()),
				'Groups'		=> null,
				'group_code'	=> null,
				'Place'			=> null,
				'Name'			=> null,
				'Teacher'		=> null,
				'Type' 			=> array('values' => HM_Timetable_TimetableModel::getListDisciplineTypes()),
				'Rank'			=> null,
				'Nch' 			=> array('values' => HM_Timetable_TimetableModel::getListEvenOdd()),
				'Day' 			=> array('values' => HM_Timetable_TimetableModel::getListWeekDays()),
				'Faculty' 		=> array('values' => HM_Timetable_TimetableModel::getListFaculties()),
				'Form' 			=> array('values' => HM_Timetable_TimetableModel::getListStudyForms()),
				'link'			=> null,
				'users'			=> null,
				'file_path'		=> null,
				'date_updated' 	=> array('render' => 'DateSmart'),
            )
        );
		
		
		$grid->updateColumn('DateZ', array(
            'format' 	=> array('date', array('date_format' => HM_Locale_Format::getDateFormat())),
            'callback' 	=> array(
				'function' 	=> array($this, 'updateDate'),
                'params' 	=> array('{{DateZ}}')
            )
        ));
		
		$grid->updateColumn('date_updated', array(
            'format' 	=> array('date', array('date_format' => HM_Locale_Format::getDateFormat())),
            'callback' 	=> array(
				'function' 	=> array($this, 'updateDate'),
                'params' 	=> array('{{date_updated}}')
            )
        ));
		
		
		$grid->updateColumn('Time_1', array(
				'callback' => array(
					'function' 	=> array($this, 'updateTimeBegin'),
					'params' 	=> array('{{Time_1}}')
				)
		));
		
		$grid->updateColumn('Time_2', array(
				'callback' => array(
					'function' 	=> array($this, 'updateTimeEnd'),
					'params' 	=> array('{{Time_2}}')
				)
		));
		
		$grid->updateColumn('Caption', array(
				'callback' => array(
					'function' 	=> array($this, 'updateAcademicHours'),
					'params' 	=> array('{{Caption}}')
				)
		));
		
		$grid->updateColumn('Type', array(
				'callback' => array(
					'function' 	=> array($this, 'updateDisciplineTypes'),
					'params' 	=> array('{{Type}}')
				)
		));
		
		$grid->updateColumn('Nch', array(
				'callback' => array(
					'function' 	=> array($this, 'updateEvenOdd'),
					'params' 	=> array('{{Nch}}')
				)
		));
		
		$grid->updateColumn('Day', array(
				'callback' => array(
					'function' 	=> array($this, 'updateWeekDays'),
					'params' 	=> array('{{Day}}')
				)
		));
		
		$grid->updateColumn('Faculty', array(
				'callback' => array(
					'function' 	=> array($this, 'updateFaculties'),
					'params' 	=> array('{{Faculty}}')
				)
		));
		
		$grid->updateColumn('Form', array(
				'callback' => array(
					'function' 	=> array($this, 'updateStudyForms'),
					'params' 	=> array('{{Form}}')
				)
		));
		
		$grid->updateColumn('link', array(
				'callback' => array(
					'function' 	=> array($this, 'updateLink'),
					'params' 	=> array('{{link}}', '{{link2}}', '{{link3}}')
				)
		));
		
	
		
		try {
			$this->view->gridAjaxRequest = $this->isGridAjaxRequest();
			$this->view->grid 			 = $grid->deploy();		
		} catch (Exception $e) {
			echo $e->getMessage(), "\n";
		}
	}
	
	
	public function updateDate($date)
	{
		return $date;
	}
	
	public function updateTimeBegin($time_id)
	{
		$list = HM_Timetable_TimetableModel::getListTimeBegin();
		return $list[$time_id]; 
	}
	
	public function updateTimeEnd($time_id)
	{
		$list = HM_Timetable_TimetableModel::getListTimeEnd();
		return $list[$time_id]; 
	}
	
	public function updateAcademicHours($hour_id)
	{
		$list = HM_Timetable_TimetableModel::getListAcademicHours();
		return $list[$hour_id]; 
	}
	
	public function updateDisciplineTypes($type_id)
	{
		$list = HM_Timetable_TimetableModel::getListDisciplineTypes();
		return $list[$type_id]; 
	}
	
	public function updateEvenOdd($id)
	{
		$list = HM_Timetable_TimetableModel::getListEvenOdd();
		return $list[$id]; 
	}
	
	public function updateWeekDays($day_id)
	{
		$list = HM_Timetable_TimetableModel::getListWeekDays();
		return $list[$day_id]; 
	}
	
	public function updateFaculties($faculty_id)
	{
		$list = HM_Timetable_TimetableModel::getListFaculties();
		return $list[$faculty_id]; 
	}
	
	public function updateStudyForms($form_id)
	{
		$list = HM_Timetable_TimetableModel::getListStudyForms();
		return $list[$form_id]; 
	}
	
	public function updateLink($link, $link2, $link3)
	{
		$data  = '';
		$data .= empty($link)  ? '' : ', ' . $link;
		$data .= empty($link2) ? '' : ', ' . $link2;
		$data .= empty($link3) ? '' : ', ' . $link3;
		return trim($data, ',');
	}
	
	
}