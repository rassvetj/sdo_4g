<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';

class HM_View_Infoblock_InterestingFactBlock extends HM_View_Infoblock_ScreenForm
{
    protected $id = 'interestingFact';
    
    public function interestingFactBlock($title = null, $attribs = null, $options = null)
    {
        $services = Zend_Registry::get('serviceContainer');
        $userId = $services->getService('User')->getCurrentUserId();
        $isModerator = $services->getService('News')->isUserActivityPotentialModerator($userId);
        
        $order = 'RAND()';

        if ($services->getService('InfoblockFact')->getSelect()->getAdapter() instanceof Zend_Db_Adapter_Oracle) {
            $order = 'dbms_random.value';
        }
        
        
        
        $fact = $services->getService('InfoblockFact')->getOne(
            $services->getService('InfoblockFact')->fetchAll(
                    'status = 1',
                    $order,
                    1
            )
        );
            
        $this->view->fact = $fact;
        $this->view->isModerator = $isModerator;
        
        if(($fact->title != "") && (null !== $title)) {
            $title = $title . ': ' . $fact->title;
        }
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/intersting-fact/style.css');
		$content = $this->view->render('interestingFactBlock.tpl');
		
        return parent::screenForm($title, $content, $attribs);
    }
}