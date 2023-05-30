    <?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_News extends HM_View_Infoblock_ScreenForm
{                                          
    
    protected $id = 'news';

    //Определяем класс отличный от других
    protected $class = 'scrollable';
    
    public function news($title = null, $attribs = null, $options = null)
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');
        //$service = Zend_Registry::get('serviceContainer')->getService('Info');
        // todo: $attribs['param'] перенести в $options['news_id']
        $this->id = strtolower(substr(str_replace('HM_View_Infoblock_', '', get_class($this)), 0, 1)).substr(str_replace('HM_View_Infoblock_', '', get_class($this)), 1);
        $this->id .= "_".$attribs['param'];
        
        //$select= $serviceContainer->getService('User')->getSelect()->from('news2')->where('nID = ?',$attribs['param']);
         $select= $serviceContainer->getService('Info')->getOne($serviceContainer->getService('Info')->find($attribs['param']));
        if(!isset($attribs['id'])){
            $attribs['id'] = $this->id;
        }
        //$res = $select->query()->fetchAll();
        //$this->view->news = $res[0];
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/news/style.css');
        $this->view->news = $select;
        $content = $this->view->render('newsBlock.tpl');
        $infoService = $serviceContainer->getService('Info');
        $content = $infoService->replacePlaceholders($content);
        
        unset($attribs['param']);

        if ($title == null) return $content;
        
        return parent::screenForm($title, $content, $attribs);

    }
}