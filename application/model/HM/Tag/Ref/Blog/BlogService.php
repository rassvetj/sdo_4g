<?php
class HM_Tag_Ref_Blog_BlogService extends HM_Tag_Ref_RefService
{

    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefService_Interface::getItemViewAction()
     */
    public function getItemViewAction($itemId)
    {
        if ( !$this->getService('Acl')->isCurrentAllowed('mca:blog:index:view') ) return null;
        return array('module' => 'blog',
        		     'controller' => 'index',
                     'action' => 'view',
                     'id' => $itemId);
    }
        
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefService_Interface::getItemTitle()
     */
    public function getItemTitle($itemId)
    {
        $rService = $this->getService('Blog');
        $item = $rService->getOne($rService->find($itemId));
        return ( $item )? $item->title : _('Нет');
    }
    
    
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefService_Interface::getItemDescription()
     */
    public function getItemDescription($itemId)
    {
        $rService = $this->getService('Blog');
        $item = $rService->getOne($rService->find($itemId));
        $result = '';
        // Получаем первые несколько предложений для анонса
        if ( $item ) {
            $text = strip_tags($item->body);
            $arPredl = explode('. ', $text);
            $result =  ( count($arPredl) > 5 )? implode('. ', array_slice($arPredl, 0,5)) : implode('. ', $$arPredl);
        } 
        
        return $result;
    }
    
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefService_Interface::getIcon()
     */
    public function getIcon()
    {
       return array('src' =>'/images/events/4g/64x/wiki.png','title' => _('Блог'));
    }
    
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefService_Interface::getFilterSelect()
     */
    public function getFilterSelect($search, Zend_Db_Select $select)
    {
        $search = trim( $search );
        
        if ( !$search ) return $select;
        
        return  $select->joinInner( array('ref' => 'tag_ref'),
                            	    'ref.item_id = b.id AND ref.item_type = ' . $this->getBlogType(),
                                    array())
                       ->joinInner( array('t' => 'tag'),
                             		'ref.tag_id = t.id',
                                    array())
                       ->where( $this->getService('Blog')->quoteInto('LOWER(t.body) LIKE ?', '%' . $search . '%') );
    }
}
