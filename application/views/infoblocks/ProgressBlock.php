<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';

class HM_View_Infoblock_ProgressBlock extends HM_View_Infoblock_ScreenForm
{
    protected $id = 'Progress';
    
    public function progressBlock($title = null, $attribs = null, $options = null)
    {

        
        $service = Zend_Registry::get('serviceContainer');
        
        
        
        $subjects = $service->getService('Dean')->getSubjectsResponsibilities($service->getService('User')->getCurrentUserId());
        
        $list = $subjects->getList('subid', 'name');
        
        $keys = array_keys($list);

        if (!count($keys)) {
            $keys = array(0);
        }
        
        if (!count($keys)) {
            $keys = array(0);
        }
        
        $select = $service->getService('Dean')->getSelect();

        $select->from(
            array('s' => 'Students'),
            array(
                'subject_id' => 's.CID',
                'amount' => new Zend_Db_Expr('COUNT(s.MID)'))
            )
            ->join(array('p' => 'People'),
                's.MID = p.MID AND p.blocked = 0',
                array()
            )
            ->where('s.CID IN (?)', $keys)
            ->group(array('s.CID'));

        $fetch = $select->query()->fetchAll();
        $students = $this->expand($fetch);
        
        
        $select = $service->getService('Dean')->getSelect();
        
        $select->from(
            array('g' => 'graduated'),
            array(
                'subject_id' => 'g.CID',
                'amount' => new Zend_Db_Expr('COUNT(g.MID)'))
            )
            ->join(array('p' => 'People'),
                'g.MID = p.MID AND p.blocked = 0',
                array()
            )
            ->where('g.CID IN (?)', $keys)
            ->where('g.certificate_id IS NOT NULL')
            ->group(array('g.CID'));

        $fetch = $select->query()->fetchAll();
        $graduated = $this->expand($fetch);
        
        if($options['format'] == 'array'){
            $format = true;
        }else{
            $format = false;
        }        
        
        
        $result = array();
        foreach($subjects as $value){
            
            $resultRow  = $this->createRow($value, $students, $graduated, $format);
            if($resultRow[1] != 0){
                $result[] = $resultRow;
            }
        }
        

        $this->view->array  = json_encode($result);
        
        if($options['format'] == 'array'){
            return $result;
        }
        
        $this->view->rowCount = count($result);
        
		$content = $this->view->render('ProgressBlock.tpl');

        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }
    
    
    public function expand($array){
        
        $ret = array();
        
        foreach($array as $val){
            $ret[$val['subject_id']] = $val['amount'];
        }
        
        return $ret;
    }
    
    public function createRow($model, $students, $graduated, $array = false){
        $all = $students[$model->subid] + $graduated[$model->subid];
        
        
        if($array === false){
            $card = $this->view->cardLink($this->view->url(array('controller' => 'list', 'module' => 'subject', 'action' => 'card', 'subject_id' => $model->subid)), _('Карточка учебного курса'));
            $link =  '<div><a class="lightbox" target="lightbox" href="/subject/list/card/subject_id/222" title=""><img src="/images/content-modules/grid/card.gif" title=""   class = "ui-els-icon " style="vertical-align: middle;" /></a>' . '<a href="' . $this->view->url(array('module' => 'marksheet','controller' => 'index', 'action'=>'index', 'subject_id' => $model->subid)) . '">' . iconv(Zend_Registry::get('config')->charset, 'UTF-8', $model->name) . '</a></div>';
        
        }
        else{
            $link = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $model->name);
        }
            
        if($all == 0){
           $allDiv = 1; 
        } else{
            $allDiv = $all; 
        }
        return array($link, $all,(int)$students[$model->subid], (int)$graduated[$model->subid], round($graduated[$model->subid] / $allDiv * 100));
    }
    
}