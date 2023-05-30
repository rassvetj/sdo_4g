<?php 
class Orgstructure_ResultController extends HM_Controller_Action_Orgstructure {
	
	public function pollAction(){
		
		$org_id = (int) $this->_getParam('org_id');

        $item = $this->getOne($this->getService('Orgstructure')->find($org_id));

        $users = array(-999);
        if ($item) {
            if ($item->getType() == HM_Orgstructure_OrgstructureModel::TYPE_POSITION) {
                $users[] = $item->mid;
            } else {
                $collection = $this->getService('Orgstructure')->fetchAll(array('owner_soid = ?' => $org_id));
                if (count($collection)) {
                    foreach($collection as $item) {
                        $users[] = $item->mid;
                    }
                }
            }
        }

        $select = $this->getService('LessonAssign')->getSelect();
        $select
        		//->distinct()
        		->from(
                    array('s' => 'scheduleID'), 
                    array(
	                    'name' => 'sj.name', 
	                    'title' => 'sch.Title', 
	                    'sheid' => 's.SHEID', 
	                    'date' => 'sch.begin',
	                    'countTotal' => 'COUNT(*)'
                    )
                )
                ->join(
                	array('sch' => 'schedule'),
                	'sch.SHEID = s.SHEID AND sch.typeID = '.HM_Event_EventModel::TYPE_POLL,
                	array()
                )
                ->join(
                	array('sj' => 'subjects'), 
                	'sj.subid = sch.CID', 
                	array()
                )
                ->joinLeft(
                	array('logForStatus' => 'loguser'), 
                	'logForStatus.sheid=s.SHEID AND logForStatus.mid = s.MID AND logForStatus.mid = s.MID AND (
	                	logForStatus.status = '.HM_Test_Result_ResultModel::STATUS_FINISHED.' 
	                	OR logForStatus.status = '.HM_Test_Result_ResultModel::STATUS_FORCED.' 
	                	OR logForStatus.status = '.HM_Test_Result_ResultModel::STATUS_LIMIT.'
	                )',       
                	array(
                			'countFinished' => 'COUNT(logForStatus.stid)',
                			'percent' => 'AVG(free)', 
                			'balmax' => 'AVG(balmax)', 
                			'balmin' => 'AVG(balmin)', 
                			'bal' => 'AVG(bal)', 
                			'balmax2' => 'AVG(balmax2)', 
                			'balmin2' => 'AVG(balmin2)'
                	)
                )                
                ->group(array('sj.name', 'sch.Title', 's.SHEID', 'sch.begin'))
                ->where("s.MID IN (?)", $users)
                ->order(array('sj.name', 'sch.Title'))
                ;

        $grid = $this->getGrid($select, 
            array(
            	'countTotal' => array('title' => _('Кол-во опрашиваемых')),
            	'countFinished' => array('title' => _('Кол-во завершенных опросов')),
            	'name' => array('title' => _('Название курса')),
                'title' => array('title' => _('Название опроса')),
                'date' => array('title' => _('Дата опроса')),
            	'percent' => array('title' => _('Средний процент выполнения')),
	            'balmax' => array('hidden' => true),
	            'balmin' => array('hidden' => true),
	            'bal' => array('title' => _('Средний балл')),
	            'balmax2' => array('hidden' => true),
	            'balmin2' => array('hidden' => true),
                'sheid' => array('hidden' => true),
                'stid' => array('hidden' => true)
            
            ),
            array(
            	'name' => null,
                'title' => null,
                'date' => array('render' => 'DateTimeStamp'),
                'countTotal' => null,
                'countFinished' => null,
                'percent' => null,
                'bal' => null
            )
        );
                
        $grid->updateColumn('percent',
                array(
                	'callback' =>
	                array(
	                    'function' => array(HM_Test_Result_ResultService, 'getEveragePercent'),
	                    'params' => array('{{balmax}}', '{{balmin}}', '{{bal}}', '{{balmax2}}', '{{balmin2}}')
	                ))
        );   
           
        $grid->updateColumn('bal',
                array(
                	'callback' =>
	                array(
	                    'function' => array(HM_Test_Result_ResultService, 'getEverageMark'),
	                    'params' => array('{{balmax}}', '{{balmin}}', '{{bal}}', '{{balmax2}}', '{{balmin2}}')
	                ))
        );
                
        $grid->updateColumn('date', array(
	            'format' => array(
	                'date',
	                array('date_format' => HM_Locale_Format::getDateFormat())
	            ))
        );
        
        
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
		
	}
	
}