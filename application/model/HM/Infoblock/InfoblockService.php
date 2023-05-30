<?php

class HM_Infoblock_InfoblockService  extends HM_Service_Abstract
{

    private $_infoblocks = array();

    public function __construct()
    {
        parent::__construct();

        $xml = new DOMDocument('1.0');

        $locale = Zend_Registry::get('Zend_Locale'); 
        $localePath = ($locale != 'ru_RU') ? '/../data/locales/' . $locale : '';
        $xml->load(realpath(APPLICATION_PATH . /*$localePath .*/'/settings/infoblocks.xml'));
        $config = $xml->getElementsByTagName('main')
                      ->item(0);

        $news = $this->getNews();

        $elem = $news->getElementsByTagName('infoblock')->item(0);

        $elem = $xml->importNode($elem, true);
        $charset = Zend_Registry::get('config')->charset;

        $config->appendChild($elem);

        $_infoblocks = new HM_Config_Xml($xml->saveXML(), null, true);

        $this->_infoblocks =$_infoblocks->main
                                        ->toArray();
				
    }

    public function isBlockExists($name, $role = 'student')
    {
        $blocks = $this->getTree($role);
        if (is_array($blocks) && count($blocks)) {
            foreach($blocks as $block) {
                if (isset($block['name']) && ($block['name'] == $name)) return true;
                if (isset($block['block']) && is_array($block['block']) && count($block['block'])) {
                    if (isset($block['block']['name'])) {
                        if ($block['block']['name'] == $name) return true;
                    } else {
                        foreach($block['block'] as $subBlock) {
                            if (isset($subBlock['name']) && ($subBlock['name'] == $name)) return true;
                        }
                    }
                }
            }
        }
        return false;
    }
    
    public function getBlock($name, $role = 'student')
    {
        $blocks = $this->getTree($role);
        if (is_array($blocks) && count($blocks)) {
            foreach($blocks as $block) {
                if (isset($block['name']) && ($block['name'] == $name)) return $this->_translateBlockFields($block);
                
                if (isset($block['block']) && is_array($block['block']) && count($block['block'])) {
                    if (isset($block['block']['name'])) {
                        if ($block['block']['name'] == $name) return $this->_translateBlockFields($block['block']);
                    } else {
                        foreach($block['block'] as $subBlock) {
                            if (isset($subBlock['name']) && ($subBlock['name'] == $name)) return $this->_translateBlockFields($subBlock);
                        }
                    }
                }
            }
        }
        return null;
    }
    
    /**
     * Переводим поля инфоблока и возвращаем его
     * @param unknown_type $block
     * @return Ambiguous
     */
    private function _translateBlockFields($block) {
        $block["title"] = ( isset($block["title"]) )? _($block["title"]):'';
        $block["description"] = ( isset($block["description"]) )? _($block["description"]):'';
		
		// echo '<pre>';
		// exit(var_dump($block));
		
		
        return $block;
    }

    public function getTree($role = 'student', $all = true, $user_id = 0, $charset = null) {

        if($role) {
            $blocks =  $this->_filterRoles($role, $charset);
        }		
        
        if($all == true){
            return $blocks;
        }else{
            return $this->_filterExists($blocks, $role, $user_id);
        }
        return null;
    }

    public function insertBlocks($columns, $role, $user_id = 0)
    {
        $adapter = $this->getSelect()->getAdapter();
        
        // TODO check for undeletable blocks!
        $adapter->delete('interface', array('user_id = ?' => array($user_id),
                                            'role =?'     => array($role))
        );
		
		//--В columns поискать нужный блок, если его нет, то добавить в массив в первую колонку первым элементом.
		if($role == HM_Role_RoleModelAbstract::ROLE_ENDUSER) {
			echo 'columns='; var_dump($columns);
			$blockedWidgets = array(
				'1' => array(
					'passportInfo', //--замена паспорта
					'news_13', //--Информационное письмо
					'cardStudent', //--Учебная карточка студента
					#'recordCardBlock', //--Учетная карточка студента
				),
				'2' => array(
					'news_9', //-- Контакты
					'news_16', //-- Кураторы
				),
				'3' => array(
					'news_18', //--Новые материалы на сайте
				),
			);
			
			/*
			$blockedWidgets = array(
				'news_13', //--Информационное письмо
				'cardStudent', //--Учебная карточка студента
				'recordCardBlock', //--Учетная карточка студента
			); //--Виджеты, которые надо оставить на странице юзера полюбому
			$blockedWidgets2 = array(				
				'news_9', //-- Контакты
			); //--Колонка 2
			*/
			$isExist = array(
				'1' => array(),
				'2' => array(),
				'3' => array(),
			);
			/*
			$isExist = array();
			$isExist2 = array();
			*/
			$isNeedInsertElem = array(
				'1' => array(),
				'2' => array(),
				'3' => array(),
			);
			
			foreach ($columns as $column) {
				foreach ($column as $value) {
					if(in_array($value, $blockedWidgets[1])){ //--колонка 1
						$isExist[1][$value] = $value;
					} elseif(in_array($value, $blockedWidgets[2])){ //--колнка 2					
						$isExist[2][$value] = $value;
					} elseif(in_array($value, $blockedWidgets[3])){ //--колнка 3					
						$isExist[3][$value] = $value;
					}
					/*
					if(in_array($value, $blockedWidgets)){ //--колонка 1
						$isExist[$value] = $value;
					} elseif(in_array($value, $blockedWidgets2)){ //--колнка 2					
						$isExist2[$value] = $value;
					}
					*/
				}
			}	

			//echo 'isExist='; var_dump($isExist);
			//echo 'blockedWidgets='; var_dump($blockedWidgets);			
			//echo 'isExist2='; var_dump($isExist2);
			 //--виджеты, которых нет на данныймомент на странице, но надо оставить.
			$isNeedInsertElem[1] = array_diff($blockedWidgets[1], $isExist[1]); //--колонка 1
			$isNeedInsertElem[2] = array_diff($blockedWidgets[2], $isExist[2]); //--колонка 2
			$isNeedInsertElem[3] = array_diff($blockedWidgets[3], $isExist[3]); //--колонка 3
			//echo 'isNeedInsertElem='; var_dump($isNeedInsertElem);			
			//$isNeedInsertElem = array_diff($blockedWidgets, $isExist); //--виджеты, которых нет на данныймомент на странице, но надо оставить.
			//$isNeedInsertElem2 = array_diff($blockedWidgets2, $isExist2); //--колонка 2
			//echo 'isNeedInsertElem2='; var_dump($isNeedInsertElem2);
			//$columns[0] = $isNeedInsertElem + $columns[0];
			foreach($columns[0] as $i){
				$isNeedInsertElem[1][] = $i;
			}			
			foreach($columns[1] as $i){
				$isNeedInsertElem[2][] = $i;
			}
			foreach($columns[2] as $i){
				$isNeedInsertElem[3][] = $i;
			}
			
			$columns[0]  = $isNeedInsertElem[1];
			$columns[1]  = $isNeedInsertElem[2];
			$columns[2]  = $isNeedInsertElem[3];
			//$columns[0] = array_merge($isNeedInsertElem, $columns[0]);
			//$columns[1] = array_merge($isNeedInsertElem2, $columns[1]);
			//echo 'columns[1]='; var_dump($columns[1]);
			
			//echo 'columns='; var_dump($columns);
			
		}
		//------------------------------------------------------
		
        
        $y = 0;
        foreach ($columns as $column) {
            foreach ($column as $key => $value) {
                if (preg_match('#([a-z]+)_([\d]+)#i',$value, $matches)) {
                    $data = array(  'role'     => $role,
                                    'user_id'  => $user_id,
                                    'block'    => $matches[1],
                                    'x'        => intval($key),
                                    'y'        => intval($y),
                                    'param_id' => $matches[2]
                    );
                    $this->insert($data);
                } else {
                    $data = array(  'role'   => $role,
                                    'user_id'=> $user_id,
                                    'block'  => $value,
                                    'x'      => intval($key),
                                    'y'      => intval($y)
                    );
                    $this->insert($data);
                }
            }
            $y++;
        }
    }


    public function getNews()
    {

        $serviceContainer = Zend_Registry::get('serviceContainer');
        $arr = $serviceContainer->getService('Info')->fetchAll($this->quoteInto($this->quoteIdentifier('show') . '= ?',1));

        $xml=new DomDocument('1.0', 'UTF-8');

        $infoblock = $xml->appendChild($xml->createElement('infoblock'));

        $charset = Zend_Registry::get('config')->charset;

        $infoblock->appendChild($xml->createElement('title', iconv($charset, 'UTF-8', _('Информационные блоки'))));
        $infoblock->appendChild($xml->createElement('name', 'newsBlock'));
        $roles = HM_Role_RoleModelAbstract::getBasicRoles(true, true);
        $roles = array_keys($roles);

        foreach($roles as $values){
            $infoblock->appendChild($xml->createElement('role', $values));
        }

        foreach ($arr as $val){

            $block = $infoblock->appendChild($xml->createElement('block'));
            $block->appendChild($xml->createElement('name', 'news_' . $val->nID));

            foreach($roles as $values){
                $block->appendChild($xml->createElement('role', $values));
            }
            $block->appendChild($xml->createElement('title', iconv($charset, 'UTF-8', _($val->Title))));
            $block->appendChild($xml->createElement('description', iconv($charset, 'UTF-8', _('Текст информационного блока'))));

        }

       return $xml;

    }


    public function _filterRoles($role = 'admin', $charset = null)
    {
            $acl = $this->getService('Acl');
            $blocks = $this->_infoblocks['infoblock'];
            
            $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_INFOBLOCKS);
            Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, &$blocks);
            $blocks = $event->getReturnValue();		

            if(is_array($blocks) && is_numeric(key($blocks))){
                foreach($blocks as $key => $block){

                    if(is_array($block['role']) && is_numeric(key($block['role']))){
                        if(!$acl->inheritsRole($role, $block['role'])){
                            unset($blocks[$key]);
                            continue;
                        }
                    }elseif(isset($block['role'])){

                        if(!$acl->inheritsRole($role, $block['role'])){
                            unset($blocks[$key]);
                            continue;
                        }

                    }

                    if(is_array($block['block']) && is_numeric(key($block['block']))){
                        foreach($block['block'] as $oneKey => &$oneBlock){

                            if(is_array($oneBlock['role']) && is_numeric(key($oneBlock['role']))){
                                if(!$acl->inheritsRole($role, $oneBlock['role'])){
                                    unset($blocks[$key]['block'][$oneKey]);
                                }
                            }elseif(isset($oneBlock['role'])){
                                if(!$acl->inheritsRole($role, $oneBlock['role'])){
                                    unset($blocks[$key]['block'][$oneKey]);
                                }
                            }
                        }
                    }else{

                        $oneBlock = $block['block'];
                        if(is_array($oneBlock['role']) && is_numeric(key($oneBlock['role']))){
                            if(!$acl->inheritsRole($role, $oneBlock['role'])){
                                unset($blocks[$key]['block']);
                            }
                        }elseif(isset($oneBlock['role'])){
                            if(!$acl->inheritsRole($role, $oneBlock['role'])){
                                unset($blocks[$key]['block']);
                            }
                        }
                    }
                }
            }else{

                $block = &$blocks;
                if(is_array($block['role']) && is_numeric(key($block['role']))){
                    if(!$acl->inheritsRole($role, $block['role'])){
                        unset($blocks);
                        continue;
                    }
                }elseif(isset($block['role'])){
                    if(!$acl->inheritsRole($role, $block['role'])){
                        unset($blocks);
                        continue;
                    }

                }

                if(is_array($block['block']) && is_numeric(key($block['block']))){
                    foreach($block['block'] as $oneKey => &$oneBlock){
                        if(is_array($oneBlock['role']) && is_numeric(key($oneBlock['role']))){
                            if(!$acl->inheritsRole($role, $oneBlock['role'])){
                                unset($blocks['block'][$oneKey]);
                            }
                        }elseif(isset($oneBlock['role'])){
                            if(!$acl->inheritsRole($role, $oneBlock['role'])){
                                unset($blocks['block'][$oneKey]);
                            }
                        }
                    }
                }else{

                    $oneBlock = $block['block'];
                    if(is_array($oneBlock['role']) && is_numeric(key($oneBlock['role']))){
                        if(!$acl->inheritsRole($role, $oneBlock['role'])){
                            unset($blocks['block']);
                        }
                    }elseif(isset($oneBlock['role'])){
                        if(!$acl->inheritsRole($role, $oneBlock['role'])){
                            unset($blocks['block']);
                        }
                    }
                }
            }


        foreach($blocks as $key => $value){

            if (null !== $charset) {
                if (isset($value['title'])) {
                    $blocks[$key]['title'] = iconv(Zend_Registry::get('config')->charset, $charset, _($value['title']));
                }

                if (isset($value['description'])) {
                    $blocks[$key]['description'] = iconv(Zend_Registry::get('config')->charset, $charset, _($value['description']));
                }
            }

            if(empty($value['block'])){
                unset($blocks[$key]);
            } else {
                if (is_array($value['block']) && count($value['block'])) {
                    if (isset($value['block']['title'])) {
                        if (null !== $charset) {
                        $blocks[$key]['block']['title'] = iconv(Zend_Registry::get('config')->charset, $charset, _($value['block']['title']));
    
                        if (isset($value['block']['description'])) {
                            $blocks[$key]['block']['description'] = iconv(Zend_Registry::get('config')->charset, $charset, _($value['block']['description']));
                        }
                        }

                    } else {
                        foreach($value['block'] as $index => $block) {
                            if (null !== $charset) {
                                if (isset($block['title'])) {
                                    $blocks[$key]['block'][$index]['title'] = iconv(Zend_Registry::get('config')->charset, $charset, _($block['title']));
                                }

                                if (isset($block['description'])) {
                                    $blocks[$key]['block'][$index]['description'] = iconv(Zend_Registry::get('config')->charset, $charset, _($block['description']));
                                }
                            }

                        }
                    }
                }
            }

        }

        return $blocks;
    }


    public function _filterExists($blocks, $role, $userId = 0){

        if($userId ===0){
            $userInfoBlock = $this->fetchAll(array('role = ?' =>$role, 'user_id = ?' => 0));
        }else{
            $userInfoBlock = $this->fetchAll(array('role = ?' =>$role, 'user_id = ?' => $userId));

            if(count($userInfoBlock) < 1){
                $userInfoBlock = $this->fetchAll(array('role = ?' =>$role, 'user_id = ?' => 0));
            }
        }

        $Exists = array();

    //print_r($blocks);

        if(is_array($blocks) && is_numeric(key($blocks))){
            foreach($blocks as $key => $block){

                if(is_array($block['block']) && is_numeric(key($block['block']))){
                    foreach($block['block'] as $oneKey => &$oneBlock){

                        $iValue = '';

                        for($iKey = 0; $iKey < count($userInfoBlock); $iKey++){

                            $iValue = $userInfoBlock[$iKey];

                            if($oneBlock['name'] == $iValue->block){

                                $Exists[] = array('category' => $blocks[$key]['name'],
                                                  'name'     => $oneBlock['name'],
                                                  'title'    => _($oneBlock['title']),
                                                  'content'  => _($oneBlock['description']),
                                                  'x'        => $iValue->x,
                                                  'y'        => $iValue->y
                                            );

                                unset($blocks[$key]['block'][$oneKey]);

                            }elseif($oneBlock['name'] == $iValue->block . '_' . $iValue->param_id){

                                $Exists[] = array('category' => $blocks[$key]['name'],
                                                  'name'     => $oneBlock['name'],
                                                  'title'    => _($oneBlock['title']),
                                                  'content'  => _($oneBlock['description']),
                                                  'x'        => $iValue->x,
                                                  'y'        => $iValue->y
                                            );

                                unset($blocks[$key]['block'][$oneKey]);

                            }


                        }



                    }
                }else{

                    $oneBlock = $block['block'];
                    $iValue = '';
                    for($iKey = 0; $iKey <= count($userInfoBlock) - 1; $iKey++){

                        $iValue = $userInfoBlock[$iKey];

                        if($oneBlock['name'] == $iValue->block){
                            $Exists[] = array('category' => $blocks[$key]['name'],
                                              'name'     => $oneBlock['name'],
                                              'title'    => _($oneBlock['title']),
                                              'content'  => _($oneBlock['description']),
                                              'x'        => $iValue->x,
                                              'y'        => $iValue->y
                                        );

                            unset($blocks[$key]['block']);

                        }elseif($oneBlock['name'] == $iValue->block . '_' . $iValue->param_id){
                            $Exists[] = array('category' => $blocks[$key]['name'],
                                              'name'     => $oneBlock['name'],
                                              'title'    => _($oneBlock['title']),
                                              'content'  => _($oneBlock['description']),
                                              'x'        => $iValue->x,
                                              'y'        => $iValue->y
                                        );

                            unset($blocks[$key]['block']);

                        }


                    }



                }
            }
        }else{

            $block = $blocks;
            if(is_array($block['block']) && is_numeric(key($block['block']))){
                foreach($block['block'] as $oneKey => &$oneBlock){

                    $iValue = '';

                    for($iKey = 0; $iKey <= count($userInfoBlock) - 1; $iKey++){

                        $iValue = $userInfoBlock[$iKey];

                        if($oneBlock['name'] == $iValue->block){
                            $Exists[] = array('category' => $blocks[$key]['name'],
                                              'name'     => $oneBlock['name'],
                                              'title'    => _($oneBlock['title']),
                                              'content'  => _($oneBlock['description']),
                                              'x'        => $iValue->x,
                                              'y'        => $iValue->y
                                        );

                            unset($blocks['block'][$oneKey]);

                        }elseif($oneBlock['name'] == $iValue->block . '_' . $iValue->param_id){
                            $Exists[] = array('category' => $blocks[$key]['name'],
                                              'name'     => $oneBlock['name'],
                                              'title'    => _($oneBlock['title']),
                                              'content'  => _($oneBlock['description']),
                                              'x'        => $iValue->x,
                                              'y'        => $iValue->y
                                        );

                            unset($blocks['block'][$oneKey]);

                        }
                    }
                }
            }else{

                $oneBlock = $block['block'];
                $iValue = '';

                for($iKey = 0; $iKey <= count($userInfoBlock) - 1; $iKey++){

                    $iValue = $userInfoBlock[$iKey];

                    if($oneBlock['name'] == $iValue->block){
                        $Exists[] = array('category' => $blocks[$key]['name'],
                                          'name'     => $oneBlock['name'],
                                          'title'    => _($oneBlock['title']),
                                          'content'  => _($oneBlock['description']),
                                          'x'        => $iValue->x,
                                          'y'        => $iValue->y
                                    );

                        unset($blocks['block']);
                    }elseif($oneBlock['name'] == $iValue->block . '_' . $iValue->param_id){
                        $Exists[] = array('category' => $blocks[$key]['name'],
                                          'name'     => $oneBlock['name'],
                                          'title'    => _($oneBlock['title']),
                                          'content'  => _($oneBlock['description']),
                                          'x'        => $iValue->x,
                                          'y'        => $iValue->y
                                    );

                        unset($blocks['block']);

                    }
                }
            }
        }



        $blocksRet = array();
        foreach($blocks as $listMain){


            $arr = array();
            if(!empty($listMain['block']) && !is_string(key($listMain['block']))){

                foreach($listMain['block'] as $val){

                    $arr[] = array('title'       => _($val['title']),
                                   'description' => _($val['description']),
                                   'id'          => $val['name']);
                }
            }elseif(!empty($listMain['block'])){

                $arr[] = array('title'       => _($listMain['block']['title']),
                               'description' => _($listMain['block']['description']),
                               'id'          => $listMain['block']['name']);

            }

           $attribs = array('id' => $listMain['name']);
           $title = $listMain['title'];


           $blocksRet = array_merge($blocksRet, array(array('title' => _($title),
                                                            'id'   => $listMain['name']
                                                      ),
                                                      $arr
                                                )
                        );

        }
        //pr($blocksRet);
        return array('all'     => $blocksRet,
        			 'current' => $Exists
               );
    }


    public function returnBlocks($array, $type){
        if(count($array['current']) < 1){
            return false;
        }

        $columns = array();
        foreach($array['current'] as $value){

            if($type == 'edit'){
                    $columns[$value['y']][$value['x']] = array('block'   => 'screenForm',
                                                               'title'   => _($value['title']),
                                                               'content' => $value['content'],
                                                               'attribs' => array('data-category' => $value['category'],
                                                                                  'id'            => $value['name']
                                                                  )
                   );
             }else{
                $explode = explode('_', $value['name']);
                if(count($explode) == 2){

                    list($block, $param) = $explode;
                    $columns[$value['y']][$value['x']]= array('block'   => $block,
                                                              'title'   => _($value['title']),
                                                              'attribs' => array('param' => $param)
                                                        );
                }
                else{
                    $columns[$value['y']][$value['x']]= array('block'   => $value['name'],
                                                              'title'   => _($value['title']),
                                                              'attribs' => array()
                                                        );
                }
            }
        }

        foreach($columns as &$column){
            ksort($column);
        }

        ksort($columns); // echo '<pre>'; exit(var_dump($columns));
        return $columns;
    }


    public function clearUserData($role, $user_id)
    {
        $adapter = $this->getSelect()->getAdapter();
        $adapter->delete('interface', array('user_id = ?' => array($user_id),
                                            'role =?'     => array($role))
        );
    }


}