<?php

class Resource_SearchController extends HM_Controller_Action_List
{
    const ITEMS_PER_PAGE = 10;

    public function indexAction()
    {
        $this->getService('Unmanaged')->setHeader(_('Результаты поиска'));

        $query = $this->_getParam('search_query', '');
        $this->view->error = false;
        if($query == ''){
            $this->view->error = _('Пустой запрос');
        }else{
            $this->view->query = $query;

            $sphinx = new HM_Search_Sphinx();
            $sphinx->SetLimits(0,1000,1000);
            $sphinx->SetMatchMode( SPH_MATCH_BOOLEAN );
//            $res = $sphinx->Query(iconv('Windows-1251','UTF-8',$query),'*');
            $resResource = $sphinx->Query($query,'resources');
            $resCourse = $sphinx->Query($query,'courses');

            $res['matches'] = array();
            $words = array_flip(explode(' ', $query));

            if(is_array($resResource['matches']) && count($resResource['matches']) > 0){
                $res['matches'] = $res['matches']  + $resResource['matches'];
                $words = $words + $resResource['words'];
            }

            if(is_array($resCourse['matches']) && count($resCourse['matches']) > 0){
                $res['matches'] = $res['matches']  + $resCourse['matches'];
                $words = $words + $resCourse['words'];
            }

            //$res['matches'] = $resResource['matches'] + $resCourse['matches'];
            $results = array();
            $resources = array();
            $courses = array();
            if(count($res['matches']) > 0 ){
    	        foreach($res['matches'] as $key => $value){
    	            if ($value['attrs']['index'] == HM_Search_Sphinx::TYPE_RESOURCE && $value['attrs']['status'] == HM_Resource_ResourceModel::STATUS_PUBLISHED && $value['attrs']['location'] == HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL){
    	               $results[$key] = $value;
    	            } elseif ($value['attrs']['index'] == HM_Search_Sphinx::TYPE_COURSE && $value['attrs']['status'] == HM_Course_CourseModel::STATUS_ACTIVE){
    	                $results[$key] = $value;
    	            }
    	        }
            }

            if(count($results) == 0){
               $this->view->error = _('Искомая комбинация слов нигде не встречается');
            }

            $this->view->words = $words;
            $this->_setPaginator($results);
            
            if ($format = $this->_getParam('export')) {
                $this->_export($format);
        }
    }
    }


    /**
     *  Логика поиска следующая:
     *  между элементами формы используется логическое И
     *  между значениями multiOptions в пределах одного элемента используется ИЛИ
     *  Напрмер: "название == 'блабла' И носитель = (CD ИЛИ DVD)"
     */
    public function advancedAction()
    {
        $this->getService('Unmanaged')->setHeader(_('Результаты поиска'));

        $params = $this->_getAllParams();

        require_once(APPLICATION_PATH . '/modules/els/kbase/forms/SearchAdvanced.php'); // как правильно подключить форму из другого module?
        $form = new HM_Form_SearchAdvanced();

        $sphinxFilters = $sphinxSubQueries = array();

        // если поиск не работает - возможно что-то забили добавить в NonSearchParams? 
        $nonSearchParams = self::getNonSearchParams();
        $fullTextParams = self::getFullTextParams();
        $rangeParams = self::getRangeParams();

        array_walk($params, array('Resource_SearchController', '_unescapeDots'));

        $rubrics = array(); // рубрики приходят из разных полей формы, они собираются в цикле ниже
        $tags = $params['tags'];

        foreach ($params as $key => $value) {

            if (in_array($key, $nonSearchParams)) {
                unset($params[$key]);
                continue;
            }

            $keyParts = explode('_', $key);
            if (strpos($key, 'classifier') !== false) {
                if (is_array($value) && count($value)) {
                    $params['rubrics'] = array_merge($params['rubrics'], $value);
                    $nonSearchParams[] = $key;
                } else {
                    unset($params[$key]);
                }
            } elseif (array_key_exists($keyParts[0], $rangeParams)) {
                if ($value = strtotime($value)) {
                    if (!isset($sphinxFilters[$keyParts[0]])) {
                        $sphinxFilters[$keyParts[0]] = $rangeParams[$keyParts[0]]; // дефолтные значения диапазона
                    }
                    $sphinxFilters[$keyParts[0]][$keyParts[1]] = $value;
                } else {
                    unset($params[$key]);
                }
            } elseif (in_array($key, $fullTextParams)) {
                if (strlen($value = trim($value))) {
                    $sphinxSubQueries[] = sprintf('@%s %s', $key, $value);
                } else {
                    unset($params[$key]);
                }
            } else { // attributes
                if ($value != -1) {
                    $sphinxFilters[$key] = $value;
                } else {
                    unset($params[$key]);
                }
            }
        }

        $sphinxQuery = implode(' ', $sphinxSubQueries);

        if (count($params) || count($tags) || count($rubrics)) {

            $sphinx = new HM_Search_Sphinx();
            $sphinx->SetLimits(0,1000,1000);
            $sphinx->SetMatchMode( SPH_MATCH_EXTENDED2 );
            foreach ($sphinxFilters as $key => $value) {
            	if (!is_array($value)) {
            	    $sphinx->SetFilter($key, array($value));
            	} else {
            	    $sphinx->SetFilterRange($key, array_shift($value), array_shift($value));
            	}
            }

            $resResource = $sphinx->Query($sphinxQuery,'resources');
            $resCourse = $sphinx->Query($sphinxQuery,'courses');

            $res['matches'] = array();

            if(is_array($resResource['matches']) && count($resResource['matches']) > 0){
                $res['matches'] = $res['matches']  + $resResource['matches'];
            }

            if(is_array($resCourse['matches']) && count($resCourse['matches']) > 0){
                $res['matches'] = $res['matches']  + $resCourse['matches'];
            }

            $results = array();
            $resources = array();
            $courses = array();

            if (count($res['matches']) > 0 ) {

                // можно их тоже искать средствами Sphinx?
                $taggedResources = $taggedCourses = array();
    	        if (count($tags)) {
                    $tagRefs = $this->getService('TagRef')->fetchAll(array('tag_id IN (?)' => $tags));
                    foreach ($tagRefs as $tagRef) {
                        if ($tagRef->item_type == HM_Tag_Ref_RefModel::TYPE_RESOURCE) {
                            $taggedResources[] = $tagRef->item_id;
                        } elseif ($tagRef->item_type == HM_Tag_Ref_RefModel::TYPE_COURSE) {
                            $taggedCourses[] = $tagRef->item_id;
                        }
                    }
    	        }

    	        $classifiedResources = $classifiedCourses = array();
    	        if (count($rubrics)) {
                    $classifierLinks = $this->getService('ClassifierLink')->fetchAll(array('classifier_id IN (?)' => $rubrics));
                    foreach ($classifierLinks as $classifierLink) {
                        if ($classifierLink->type == HM_Classifier_Link_LinkModel::TYPE_RESOURCE) {
                            $classifiedResources[] = $classifierLink->item_id;
                        } elseif ($classifierLink->type == HM_Classifier_Link_LinkModel::TYPE_COURSE) {
                            $classifiedCourses[] = $classifierLink->item_id;
                        }
                    }
    	        }

    	        foreach ($res['matches'] as $key => $value) {
    	            if (
                        ($value['attrs']['index'] == HM_Search_Sphinx::TYPE_RESOURCE) &&
                        ($value['attrs']['location'] == HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL) &&
                        (!count($tags) || in_array($value['attrs']['nid'], $taggedResources)) &&
                        (!count($rubrics) || in_array($value['attrs']['nid'], $classifiedResources))
                    ) {
                        $results[$key] = $value;
                    } elseif (
                        ($value['attrs']['index'] == HM_Search_Sphinx::TYPE_COURSE) &&
                        (!count($tags) || in_array($value['attrs']['nid'], $taggedCourses)) &&
                        (!count($rubrics) || in_array($value['attrs']['nid'], $classifiedCourses))
                    ){
    	                $results[$key] = $value;
    	            }
    	        }
            }

            $this->view->query = $params['content'];
            $this->_setPaginator($results);

            if ($format = $this->_getParam('export')) {
                $this->_export($format);
            }            
            
            $form->setDefaults($params);

            array_walk($params, array('Resource_SearchController', '_escapeDots'));
            $this->view->params = $params;
        }

        if (count($results) == 0){
           $this->view->error = _('Не найдено результатов, удовлетворяющих поисковому запросу');
        }

        $this->view->form = $form;
    }

    private function _setPaginator($results)
    {
        if ($this->view->error == false){

            $page = $this->_getParam('page', 0);
    		$paginator = Zend_Paginator::factory ($results);
    		$paginator->setCurrentPageNumber((int)$page);
    		$paginator->setItemCountPerPage($page === 'all' ? $paginator->getTotalItemCount() : self::ITEMS_PER_PAGE);

    		$currentItems = $paginator->getCurrentItems();
    		foreach($currentItems as $key => $value){
    			if ($value['attrs']['index'] == HM_Search_Sphinx::TYPE_RESOURCE) {
    				$resources[] = $value['attrs']['nid'];
    			}

    			if ($value['attrs']['index'] == HM_Search_Sphinx::TYPE_COURSE) {
    				$courses[] = $value['attrs']['nid'];
    			}
    		}
    		
    		if (count($resources) > 0) {
                $resourcesCollection = $this->getService('Resource')->fetchAllManyToMany('Tag', 'TagRef', array('resource_id IN (?)' => $resources))->asArrayOfObjects();
    		}
        	if(count($courses) > 0){
                $coursesCollection = $this->getService('Course')->fetchAllManyToMany('Tag', 'TagRef', array('CID IN (?)' => $courses))->asArrayOfObjects();
    		}

    		foreach ($currentItems as $key => &$value) {
    		    // $value['obj'] - для показа в результатах поиска; только текущая страница
    		    // $this->_data - для экспорта; все страницы
    		    $this->_data[] = $value['obj'] = ($value['attrs']['index'] == HM_Search_Sphinx::TYPE_RESOURCE) ? $resourcesCollection[$value['attrs']['nid']] : $coursesCollection[$value['attrs']['nid']]; 
    		}

//             $this->view->courses = $coursesCollection;
//             $this->view->resources = $resourcesCollection;
            $this->view->paginator = $paginator;
        }
    }

    public function tagAction()
    {
        $arItems = array();
        $tag = trim(strip_tags($this->_getParam('tag', false)));

        $this->getService('Unmanaged')->setHeader(_('Результаты поиска'));
        $this->getService('Unmanaged')->setSubHeader($tag);

        if ( !$tag ) {
            $this->_flashMessenger->addMessage(_('Не указана метка'));
            $this->_redirector->gotoSimpleAndExit('index','index','kbase');
        }

        $objTags = $this->getOne($this->getService('Tag')->fetchAllDependence('TagRef',
                                                                $this->getService('Tag')->quoteInto('body LIKE ?',"%$tag%")));
        $itemIds = array();
        $types = array_keys(HM_Tag_Ref_RefModel::getBZTypes());
        $refs = $objTags->tagRef;
        if( count($refs) ) {
            foreach ($refs as $ref) {
                if ( !in_array($ref->item_type, $types)) continue;
                $itemIds[$ref->item_type][$ref->item_id] = true;
                /*
                if ( $ref->item_type == HM_Tag_Ref_RefModel::TYPE_RESOURCE) {
                    $resource = $this->getService('Resource')->find($ref->item_id)->current();
                    if ($resource && $resource->location == HM_Resource_ResourceModel::LOCALE_TYPE_LOCAL) continue;
                }
                $objItem = new stdClass();
                $objItem->title = $ref->getService()->getItemTitle($ref->item_id);
                $objItem->icon  = $ref->getService()->getIcon();
                $objItem->description = $ref->getService()->getItemDescription($ref->item_id);
                $objItem->keywords = $this->getService('Tag')->getStrTagsByIds($ref->item_id,$ref->item_type);
                $objItem->item_id = $ref->item_id;
                $objItem->viewAction = $ref->getService()->getItemViewAction($ref->item_id);
                $arItems[] = $objItem;
                */
            }
        }

        $items = array();
        if (count($itemIds[HM_Tag_Ref_RefModel::TYPE_RESOURCE])) {
            $where = array();
            $where['resource_id IN (?)'] = array_keys($itemIds[HM_Tag_Ref_RefModel::TYPE_RESOURCE]);
            if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_RoleModelAbstract::ROLE_MANAGER)){
                $where = $this->getService('Resource')->quoteInto(
                    array(
                        'resource_id IN (?)',
                        ' AND (location = ? OR location IS NULL)',
                    ),
                    array(
                        array_keys($itemIds[HM_Tag_Ref_RefModel::TYPE_RESOURCE]),
                        HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL,
                    ));
            }
            if (count($resourcesCollection = $this->getService('Resource')->fetchAllManyToMany('Tag', 'TagRef', $where))) {
                foreach ($resourcesCollection as $resource) {
                    $items[] = $resource;
                }
            }
        }
        if (count($itemIds[HM_Tag_Ref_RefModel::TYPE_COURSE])) {
            $where = array();
            $where['CID IN (?)'] = array_keys($itemIds[HM_Tag_Ref_RefModel::TYPE_COURSE]);
            if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_RoleModelAbstract::ROLE_MANAGER)){
                $where = $this->getService('Course')->quoteInto(
                    array(
                        'CID IN (?)',
                        ' AND (chain IS NULL OR chain = ?)',
                    ),
                    array(
                        array_keys($itemIds[HM_Tag_Ref_RefModel::TYPE_COURSE]),
                        0,
                    ));
            }
            if (count($coursesCollection = $this->getService('Course')->fetchAllManyToMany('Tag', 'TagRef', $where))) {
                foreach ($coursesCollection as $course) {
                    $items[] = $course;
                }
            }
        }

        // @todo: подключить paginator
        $this->view->tag = $tag;
        $this->view->items = $this->_data = $items;
        
        if ($format = $this->_getParam('export')) {
            $this->_export($format);
        }           
    }    

    static public function getRangeParams()
    {
        return array(
            'created' => array('from' => 0, 'to' => time()), // key => defaultRange
            'developed' => array('from' => 0, 'to' => time()),
        );
    }

    static public function getFullTextParams()
    {
        return array(
            'content',
            'title',
            'filename',
            'description',
            'resource_id_external',
            'comment',
            'placement',
            'requirements',
        );
    }

    static public function getNonSearchParams()
    {
        return array(
            'module',
            'controller',
            'action',
            'submit',
            'cancelUrl',
            'page',
            'rubrics',
            'tags',
            'export',
        );
    }

    static public function _escapeDots(&$value)
    {
        $value = str_replace('.', '~', $value);
    }

    static public function _unescapeDots(&$value)
    {
        $value = str_replace('~', '.', $value);
    }
    
    protected function _getExportAttribs()
    {
        return array(
            _('Тип объекта') => 'getClassName',        
            _('Название') => 'getName',        
            _('Описание') => 'description',        
            _('Дата публикации') => 'getCreateUpdateDate',        
        );
    } 
}