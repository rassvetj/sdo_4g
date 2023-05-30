<?php
class HM_Tag_Ref_Course_CourseService extends HM_Tag_Ref_RefService
{
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefService_Interface::getItemViewAction()
     */
    public function getItemViewAction($itemId)
    {
        if ( !$this->getService('Acl')->isCurrentAllowed('mca:course:index:index') ) return null;
        return array('module' => 'course',
        		     'controller' => 'index',
                     'action' => 'index',
                     'course_id' => $itemId);
    }
    
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefService_Interface::getItemTitle()
     */
    public function getItemTitle($itemId)
    {
        $rService = $this->getService('Course');
        $item = $rService->getOne($rService->find($itemId));
        return ( $item )? $item->Title : _('Нет');
    }
    
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefService_Interface::getItemDescription()
     */
    public function getItemDescription($itemId)
    {
        $rService = $this->getService('Course');
        $item = $rService->getOne($rService->find($itemId));
        return ( $item )? $item->Description : '';
    }
    
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefService_Interface::getFilterSelect()
     */
    public function getFilterSelect($search, Zend_Db_Select $select)
    {
        $search = trim( $search );
        
        if ( !$search ) return $select;
        
        return  $select->joinInner( array('ref' => 'tag_ref'),
                            	    'ref.item_id = CID AND ref.item_type = ' . $this->getCourseType(),
                                    array())
                       ->joinInner( array('tg' => 'tag'),
                             		'ref.tag_id = tg.id',
                                    array())
                       ->where( $this->getService('Resource')->quoteInto('LOWER(tg.body) LIKE ?', '%' . $search . '%') );
    }
    
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefService_Interface::getIcon()
     */
    public function getIcon()
    {
        return array('src' =>'/images/events/4g/64x/course.png','title' => _('Учебный модуль'));
    }
}