<?php
require_once APPLICATION_PATH . '/views/infoblocks/ActivitydevBlock.php'; // chart model wanted?

class Infoblock_ActivitydevController extends HM_Controller_Action_Chart
{
	protected $session;

	public function getDataAction()
	{
        $data = array();
		$this->session = new Zend_Session_Namespace('infoblock_activitydev');
        if (($key = $this->_getParam('key')) && ($value = $this->_getParam('value'))) {
			$this->session->$key = $value;
			$this->view->$key = $value;
		}

        $service = Zend_Registry::get('serviceContainer')->getService('Session');
		$period = HM_Date::getCurrendPeriod($this->session->period);
		$tomorrow = HM_Date::now()->getDate()->add(1, HM_Date::DAY);
		$begin = $period['begin']->toString('yyyy-MM-dd');
		$end = $period['end']->toString('yyyy-MM-dd');
		$realEnd = ($period['end']->get(Zend_Date::TIMESTAMP) > $tomorrow->get(Zend_Date::TIMESTAMP)) ? $tomorrow : $end;
		$duration = ceil((strtotime($realEnd) - strtotime($begin)) / 86400);

        $select = $service->getSelect();
		$select->where(new Zend_Db_Expr($service->quoteInto(array("s.start BETWEEN ? ", "AND ?"), array($begin, $end))));

		switch ($this->session->type) {

			case HM_View_Infoblock_ActivitydevBlock::TYPE_TIMES:

//				если чел начал сессию в 9:05, а закончил в 11:05 - попадает в 3 диапазона (9, 10 и 11)
				$legendX = _('Среднее количество пользователей в это время');
				$legendY = _('Час суток');
		        $select->from(array('h24' => new Zend_Db_Expr('hours24')), array(
			                'date_period' 		=> new Zend_Db_Expr('h24.h'),
			                'value'				=> new Zend_Db_Expr("100*COUNT(h24.h)/{$duration}"),
		            	)
		            )
		            ->join(array('s' => 'sessions'), 'h24.h >= HOUR(s.start) AND h24.h <= HOUR(s.stop)', array())
		        	->group(new Zend_Db_Expr('h24.h'))
		        	->order(new Zend_Db_Expr('h24.h'));

		       	$iterator = clone $period['begin'];
		       	for ($i = 0; $i < 24; $i++) {
		       		$data[$iterator->get('HH')] = $iterator->get('H') - 1; // так больше похоже на правду
		       		$iterator->add(1, HM_Date::HOUR);
		       	}
				break;

			case HM_View_Infoblock_ActivitydevBlock::TYPE_DATES:
//				если чел начал сессию во вторник, а закончил в среду - попадает только в 1 диапазон (во вторник)
				$legendX = _('Среднее количество пользователей в этот день');
				$legendY = _('День недели');
		        $select->from(array('s' => 'sessions'), array(
			                'date_period' 		=> new Zend_Db_Expr("weekday(s.start)"),
			                'value'				=> new Zend_Db_Expr('100*COUNT(DISTINCT s.mid)'),
		            	)
		            )
		        	->group(new Zend_Db_Expr("weekday(s.start)"))
		        	->order(new Zend_Db_Expr("weekday(s.start)"));

		       	$iterator = clone $period['begin'];
    			$weekday = $iterator->get('e') ? $iterator->get('e') - 1 : 6;
        		$iterator->sub($weekday, HM_Date::DAY);
		       	for ($i = 0; $i < 7; $i++) {
		       		$data[$iterator->get('EEEE', 'ru_RU')] = $iterator->get('e');
		       		$iterator->add(1, HM_Date::DAY);
		       	}
				break;
			default:
				break;
		}
//		exit($select->__toString());
		$series = array_keys($data);
		$graphs = array_fill(0, count($data), 0);
        if ($rowset = $select->query()->fetchAll()) {
        	foreach ($rowset as $row) {
        		$key = array_search(array_search($row['date_period'], $data), $series);
        		if (isset($graphs[$key])) {
        			$graphs[$key] = round($row['value']/100, 2);
        		}
        	}
        }

		$this->view->legendX = $legendX;
		$this->view->legendY = $legendY;
		$this->view->series = $series;
		$this->view->graphs = $graphs;
	    $this->view->type = $this->session->type;
	}
}