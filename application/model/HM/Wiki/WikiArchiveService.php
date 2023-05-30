<?php

class HM_Wiki_WikiArchiveService extends HM_Activity_ActivityService
{
    private $_authorsCache = array();
    
    private $_urls = array();
    
    public function insert($data)
    {
        $data['created'] = $this->getDateTime();
        $item = parent::insert($data);
        if ($item) {
            // $mainItem = $this->getService('WikiArticles')->find($item->ARTICLE_ID)->current();
            // $doc = new HM_Activity_Search_Document(array(
                // 'activityName' => 'Wiki',
                // 'activitySubjectName' => $item->SUBJECT_NAME,
                // 'activitySubjectId' => $item->SUBJECT_ID,
                // 'id' => $item->ID,
                // 'title' => $mainItem->TITLE,
                // 'preview' => $item->BODY
            // ));

            // $this->indexActivityItem($doc);
        }
        return $item;
    }
    
    public function getAuthors($articleId)
    {
        $select = $this->getSelect()
            ->from(array('wiki_archive'),
                array('DISTINCT(author) as id'))
            ->where('article_id = ?', $articleId)
            ->order('created DESC');
        $authors = $select->query()->fetchAll();
        $ret = array();
        foreach($authors as $author) {
            if(!isset($this->_authorsCache[$$author['id']])) {
                $this->_authorsCache[$author['id']] = $this->getOne($this->getService('User')->find($author['id']));
            }
            $ret []= $this->_authorsCache[$author['id']];
        }
        return $ret;
    }
    
    public function getHistory($articleId)
    {
        return $this->fetchAll(array('article_id = ?' => $articleId), 'created DESC');
    }
    
    public function getAllHistory($subjectId, $subjectName = null)
    {
        $select = $this->getSelect()
            ->from(array('b' => 'wiki_archive'),
                   array('created' => 'MAX(b.created)')
            )
            ->joinInner(array('a' => 'wiki_articles'),
                'a.id = b.article_id',
                array('a.title')
            )
            ->where('a.subject_id = ?', $subjectId);
        if ($subjectName) {
            $select->where('subject_name = ?', $subjectName);
        } else {
            $select->where('subject_name IS NULL');
        }
        $select->where('lesson_id IS NULL');
        $select->group('a.title');
        $res = $select->query()->fetchAll();
        $ret = array();
        foreach($res as &$item) {
            $date = date('d.m.Y', strtotime($item['created']));
            if(!array_key_exists($date, $ret)) {
                $ret[$date] = array();
            }
            $ret[$date] []= $item['title'];
        }
        return $ret;
    }
    
    public function getBody($articleId)
    {
        return $this->fetchAll(array('article_id = ?' => $articleId), 'created DESC', 1)->current();
    }
    
    public function render(HM_Wiki_WikiArchiveModel $item)
    {
        $this->_urls = array();
        
        $body = $item->body;
        if(iconv_strlen($body) == 0) {
            return '';
        }
        $defEncoding = Zend_Registry::get('config')->charset;
        $body = preg_replace_callback('#\[\[(.+?)\]\]#', array(&$this, 'parseUrl'), $body);
        
        $body = trim(preg_replace('#h\d\.(.+)#', "\n$0\n", $body));//fix Textile bug with \n

        $body = iconv($defEncoding, 'utf-8', $body);
        $converter = new HM_Parser_Textile();
        $body = $converter->TextileThis($body);
        $body = iconv('utf-8', $defEncoding, $body);
        $body = str_replace(array_keys($this->_urls), array_values($this->_urls), $body);
        $body = stripslashes($body);
        return $body;
    }
    
    public function parseUrl($mathces)
    {
        $replaced = str_replace(' ', '', microtime());
        $replaced = str_replace('.', '', $replaced);
        $replaced = '(url'.$replaced.')';
        $urlData = $mathces[1];
        if(strstr($urlData, '|') === false) {
            $item = $this->getService('WikiArticles')->getByTitle(
                $urlData, 
                $this->_cabinet->getActivitySubjectId(),
                $this->_cabinet->getActivitySubjectName()
            );
            $class = '';
            if($item && $item->id) {
                $url = Zend_View_Helper_Url::url(array(
                    'module' => 'wiki',
                    'controller' => 'index',
                    'action' => 'view',
                    'subject' => $this->_cabinet->getActivitySubjectName(), 
                    'subject_id' => $this->_cabinet->getActivitySubjectId(),
                    'title' => $item->getUrl()
                ), null, true);
            } else {
                $class = 'notfound';
                $url = Zend_View_Helper_Url::url(array(
                    'module' => 'wiki',
                    'controller' => 'index',
                    'action' => 'edit',
                    'subject' => $this->_cabinet->getActivitySubjectName(), 
                    'subject_id' => $this->_cabinet->getActivitySubjectId(),
                    'title' => HM_Wiki_WikiArticlesModel::getUrl($urlData)
                ), null, true, false);
            }
            $this->_urls[$replaced] = '<a class="'.$class.'" href="'.$url.'">'.$urlData.'</a>';
            return $replaced;
        } else {
            $urlData = explode('|', $urlData);
            $this->_urls[$replaced] = '<a href="'.$urlData[1].'">'.$urlData[0].'</a>';
            return $replaced;
        }
    }
}
