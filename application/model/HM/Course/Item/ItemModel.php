<?php
class HM_Course_Item_ItemModel extends HM_Model_Abstract
{
    public function getModule()
    {
        if (isset($this->module)) {
            return $this->module[0];
        }
        return false;
    }

    static public function factory($data, $default = 'HM_Course_Item_ItemModel')
    {

        if($data['vol1'] > 0 && $data['module'] <= 0){
            return new HM_Course_Item_Test_TestModel($data);
        }elseif($data['vol2'] > 0 && $data['module'] <= 0) {
            return new HM_Course_Item_Resource_ResourceModel($data);
        }elseif($data['vol1'] <= 0 && $data['vol2'] <= 0 && $data['module'] > 0){
            return new $default($data);
        }else{
            return new HM_Course_Item_Empty_EmptyModel($data);
        }

    }
    
    public function getExecuteUrl(){
    	
        $service = Zend_Registry::get('serviceContainer')->getService('Library');
        $book = $service->getOne($service->find($this->module));

        $file = explode('?', basename($book->filename));

        $urlAddons = array();
        if($file[1]) $urlAddons[] = $file[1];

        //if($aicc = $this->urlAddonsAICC($book->bid)) $urlAddons[] = $aicc;
        
        $path = $this->findFile('/library'.$book->filename, $file[0]);
        if (!$path) {
            return '/library'.$book->filename;
        }
        //$path = ($path) ? $path : '/library'.$book->filename;
        return $path.'?'.implode('&', $urlAddons);
        
    }

    public function getContentType()
    {
        $service = Zend_Registry::get('serviceContainer')->getService('Library');
        $book = $service->getOne($service->find($this->module));
        if ($book && ($book instanceof HM_Library_Item_FileItemModel)) {
            return $book->getContentType();
        }
        return '';
    }

    // для упоротых вымпелкомовских курсов
    protected function findFile($path, $filename){
    	
	    	$dirs = explode('/', $path);
	    	if(count($dirs) == 6) return $fullpath;
	    	$dirs[count($dirs)-1] = $filename;
	    	
	    	$fullpath = implode('/', $dirs);
	    	$debug = '!before '.$fullpath.'<br>';
	    	$i = 4;
	    	while(!file_exists(APPLICATION_PATH . '/../public/unmanaged' . $fullpath)){
	    		$debug .= count($dirs).' '.$i.' '.$fullpath.' don\'t found<br>';
	    		if((count($dirs)+1) < $i) return false;//return $debug;
	    		unset($dirs[++$i]);
	    		$fullpath = implode('/', $dirs);
	    	}
	    	
    	return $fullpath;
    	
    }

    protected function urlAddonsAICC($bid){
    	$view = Zend_Registry::get('view');
    	return 'aicc_sid='.(int) $bid;//.'&aicc_url='.Zend_Registry::get('config')->url->base.'course/api/store-data'; - move into item/view.tpl

    }




}