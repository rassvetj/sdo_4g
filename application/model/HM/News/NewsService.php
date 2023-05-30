<?php

class HM_News_NewsService extends HM_Activity_ActivityService implements HM_Service_Schedulable_Interface
{    
	//const FILTER_TYPE_TAG = 'tag';
    const FILTER_TYPE_AUTHOR = 'author';
    //const FILTER_TYPE_DATE = 'date';//date format is yyyy-mm
	
	const CACHE_NAME = 'HM_News_NewsService';

	protected $_subjectsNews	= array();
    protected $_isIndexable = false;

    public function insert($data)
    {
        $data['created'] = $this->getDateTime();
        $news = parent::insert($data);
        if ($news) {
            /*
            $doc = new HM_Activity_Search_Document(array(
                'activityName' => 'News',
                'activitySubjectName' => $news->subject_name,
                'activitySubjectId' => $news->subject_id,
                'id' => $news->id,
                'title' => $news->announce,
                'preview' => $news->announce
            ));

            $this->indexActivityItem($doc);
             *
             */
        }
        return $news;
    }

    public function getNews($newsId, $subjectName, $subjectId, $position){
    	
    	if(!$position) $news = $this->getOne($this->find($newsId));
    	else{
	   		$way = ($position < 0) ? '<' : '>';
	   		$order = ($position < 0) ? 'DESC' : 'ASC';
	   		
	    	$news = $this->getOne($this->fetchAll(
	    					$this->quoteInto(
	    							array('subject_name = ?', ' AND subject_id = ?', ' AND id '.$way.' ?'),
	       							array($subjectName, $subjectId, $newsId)
	       					),
	       					array('created '.$order),
	       					1
	       	));
    	}
	    
    	return $news;
    	
    }
    
    public function getNewsTriple($newsId, $subjectName, $subjectId){
    	
    	$selectPrev = $this->getSelect();
    	$selectPrev->from('News np', array('np.id', 'np.announce', 'np.subject_name', 'np.subject_id', 'np.author', 'np.created_by'));
    	$selectPrev->where('np.subject_name = ?', $subjectName);
    	$selectPrev->where('np.subject_id = ?', $subjectId);
    	$selectPrev->where('np.id < ?', $newsId);
    	$selectPrev->order('np.created DESC');
    	$selectPrev->limit(1);
    	
    	$selectNext = $this->getSelect();
    	$selectNext->from('News nn', array('nn.id', 'nn.announce', 'nn.subject_name', 'nn.subject_id', 'nn.author', 'nn.created_by'));
    	$selectNext->where('nn.subject_name = ?', $subjectName);
    	$selectNext->where('nn.subject_id = ?', $subjectId);
    	$selectNext->where('nn.id >= ?', $newsId);
    	$selectNext->order('nn.created ASC');
    	$selectNext->limit(2);
    	
    	$select = $this->getSelect();
    	$select->union(array($selectPrev, $selectNext));
    	
    	$res = $select->query()->fetchAll();
    	//pr($res);
    	return $res;
    	
    }
    
    public function onCreateLessonForm(Zend_Form $form, $activitySubjectName, $activitySubjectId, $title = null)
    {
        $form->addElement('select', 'module', array(
            'Label' => _('Новости'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array(0))
            ),
            'filters' => array('int'),
            'multiOptions' => array(1 => 'Новость 1', 2 => 'Новость 2')
        ));
    }
    
    public function onLessonUpdate($lesson, $form)
    {
    }

    public function getLessonModelClass()
    {
        return "HM_News_NewsModel";
    }
    
    // передрано с blogService
    public function getNewsCondition($subjectId, $subjectName = null, $filter = array(), $split = false)
    {
        $where = array();
        $where['subject_id = ?'] = $subjectId;
        if ($subjectName) {
            $where['subject_name = ?'] = $subjectName;
        } else {
            $where["subject_name IS NULL OR subject_name LIKE ''"] = null;
        }
        foreach($filter as $type => $value) {
            switch($type) {
/*                case self::FILTER_TYPE_TAG:
                    $tagObj = $this->getOne($this->getService('Tag')->fetchAllManyToMany(
                        'Blog', 
                        'TagRefBlog', 
                        $this->quoteInto('BODY LIKE ?', $value)
                    ));
                    $ids = array();
                    foreach($tagObj->blogs as $blog) {
                        $ids []= $blog->id;
                    }
                    if(count($ids) > 0) {
                    $where['id IN (?)'] = $ids;
                    }
                break;*/
                case self::FILTER_TYPE_AUTHOR:
                    $where['created_by = ?'] = $value;
                break;
/*                case self::FILTER_TYPE_DATE:
                    $start = $value.'-01';
                    $end = $value.'-'.date('t', strtotime($start));
                    $where['created >= ?'] = $start;
                    $where['created <= ?'] = $end;
                break;*/
                default:
                    throw new InvalidArgumentException('Unknown filter type '.$type);
            }
        }
        if(!$split) {
            return $where;
        }
        $parts = array();
        foreach($where as $k=>$v) {
            $parts []= $this->quoteInto($k, $v);
        }
        return '('.implode(') AND (', $parts).')';
    }
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'subjectsNews'  		=> $this->_subjectsNews,                                                
            ),
            self::CACHE_NAME
        );
    }

    public function clearCache()
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME);
    }

    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_subjectsNews   		= $actions['subjectsNews'];                                   
            return true;
        }
        return false;
    }
	
	public function getSubjectNews($subject_ids = false, $is_caching = true)
	{
		if($is_caching){
			if(empty($this->_subjectsNews)) { $this->restoreFromCache();   }		
			if(!empty($this->_subjectsNews)){ return $this->_subjectsNews; }
		}
		
		$select = $this->getSelect();
		$select->from(array('n' => 'news'),
			array(
				'subject_id' 	=> 'n.subject_id',
				'announce' 		=> 'n.announce',
				'message' 		=> 'n.message',
			)
		);
		$select->where('n.subject_id > ?', 0); 
		
		if(is_array($subject_ids)){
			if(empty($subject_ids)){
				$select->where('1=0');
			} else {
				$select->where($this->quoteInto('n.subject_id IN (?)', $subject_ids));
			}
		}
		
		$res = $select->query()->fetchAll();
		
		$data = array();
		if(empty($res)) { return $data; }	
		foreach($res as $i){
			$data[$i['subject_id']][] = array(
				'announce' => $i['announce'],
				'message'  => $i['message'],
			);
		}
		
		if($is_caching){		
			$this->_subjectsNews = $data;
			$this->saveToCache();
		}
		
		return $data;
	}
	
	public function getBySubject($subjectId)
	{
		$cache      = Zend_Registry::get('cache');
		$cache_name = self::CACHE_NAME . '__' . __FUNCTION__;
		$lifetime   = 60; # сек. - время жизни
		
		$items    = $cache->load($cache_name);
		
		$item     = $items[$subjectId];
		$news     = $item['items'];
		$expired  = $item['expired'];
		
		if((int)$expired < time()){
			$news = false;
		}
		
		if(!$news){
			$news = $this->fetchAll($this->quoteInto('subject_id = ?', $subjectId));
			$items[$subjectId]['items']   = $news;
			$items[$subjectId]['expired'] = time() + $lifetime;
						
			$cache->save($items, $cache_name);			
		}
		return $news;
	}
	
	public function clearString($str)
	{
		$str	= strip_tags($str); 
		$str 	= preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $str);
		$str	= str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    ',"&nbsp;", " ", '.'), '', $str);
		$str	= str_replace(" ",'',$str);
		return $str;
	}
	
}
