<?php
require_once APPLICATION_PATH . '/views/infoblocks/ResourcesBlock.php';
/*
* Логика следующая: 
* - не считает неопубликованные ресурсы (только статусы 1 и 7)
* - в столбце "не классифицированы" отображаются _вообще_ не классифицированные, т.е. ни по одному из классификаторов
* - один ресурс может быть посчитан несколько раз (если привязан к нескольким рубрикам внутри одного классификатора); но если привязан к нескольким подрубрикам одной общей рубрики - считается один раз;
*/
class Infoblock_ResourcesController extends HM_Controller_Action_Chart
{
	protected $session;

	public function getDataAction()
	{
        $data = $series = $colors = $topLevelClassifiers = array();
        $palette = self::getPalette();
		$this->session = new Zend_Session_Namespace('infoblock_resources');
        if (($key = $this->_getParam('key')) && ($value = $this->_getParam('value'))) {
			$this->session->$key = $value;
			$this->view->$key = $value;
		}

		$series['NULL'] = _('Не классифицированы');
        $collection = Zend_Registry::get('serviceContainer')->getService('Classifier')->fetchAll(array(
            'level = ?' => 0,
            'type = ?' => $this->session->classifier
        ));
        foreach ($collection as $classifier) {
            $series[$classifier->classifier_id] = $classifier->name;
            $topLevelClassifiers[$classifier->classifier_id] = $classifier;
        }

        if (count($series) > 1) {
            foreach ($series as $classifierId => $name) {
    
        		$select = Zend_Registry::get('serviceContainer')->getService('Resource')->getSelect();
        		$select->from(array('r' => 'resources'), array(
        	        'r.resource_id',
                ))
        		->joinLeft(array('cl' => 'classifiers_links'), 'cl.item_id = r.resource_id AND cl.type = ' . HM_Classifier_Link_LinkModel::TYPE_RESOURCE, array())
        		->joinLeft(array('c' => 'classifiers'), 'cl.classifier_id = c.classifier_id', array())
        		//->joinLeft(array('c' => 'classifiers'), 'cl.classifier_id = c.classifier_id AND c.type = ' . $topLevelClassifiers[$classifierId]->type, array())
        		->where('r.status != ?', HM_Resource_ResourceModel::STATUS_UNPUBLISHED)
        		->where('r.parent_id = ?', 0)
        		->where('r.location = ?', HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL)
        		->group('r.resource_id');
    
        		if ($classifierId != 'NULL') {
            		$select->where(Zend_Registry::get('serviceContainer')->getService('Resource')->quoteInto(array(
        		        '((c.lft > ? AND ',
        		        'c.rgt < ?) OR ',
        		        'c.classifier_id = ?)'
        	        ), array(
        	            $topLevelClassifiers[$classifierId]->lft,
        	            $topLevelClassifiers[$classifierId]->rgt,
        	            $classifierId,
                    )));
        		} else {
        		    $select->having('MAX(c.classifier_id) IS NULL');
        		}
    
        		if ($this->session->from) {
        		    $from = new HM_Date($this->session->from);
        		    $select->where('r.created > ?', $from->toString('YYYY-MM-dd'));
        		}
        		if ($this->session->to) {
        		    $to = new HM_Date($this->session->to);
        		    $to->addDay(1);
        		    $select->where('r.created < ?', $to->toString('YYYY-MM-dd'));
        		}
    
                //exit($select->__toString());
    
        		$collection = $select->query()->fetchAll();
        		$data[$classifierId] = count($collection);
        		$colors[$classifierId] = array_shift($palette);
            }
        }

		$this->view->series = $series;
		$this->view->data = $data;
		$this->view->colors = $colors;
	}

	static public function getPalette()
	{
    	return array(
            '#555555',
            '#C24E5F',
            '#CF7725',
            '#D4B922',
            '#949E08',
            '#4B8C3E',
            '#003F7E',
            '#C24E5F', // @todo: сочинить много уникальных цветов
            '#CF7725',
            '#D4B922',
            '#949E08',
            '#4B8C3E',
            '#003F7E',
            '#C24E5F', // @todo: сочинить много уникальных цветов
            '#CF7725',
            '#D4B922',
            '#949E08',
            '#4B8C3E',
            '#003F7E',
        );
	}
}