<?php
class HM_Activity_Search_Document extends Zend_Search_Lucene_Document
{
    public function __construct(array $fields = null)
    {        
        $necessaryFields = array(
            'id',
            'title',
            'preview'
        );

        foreach($necessaryFields as $field) {
            if (!isset($fields[$field])) {
                throw new HM_Exception(sprintf(_('Necessary field not found: %s'), $field));
            }
        }

        $this->addField(Zend_Search_Lucene_Field::Keyword('document_id', strtolower($necessaryFields['id'])));
        $this->addField(Zend_Search_Lucene_Field::Text('document_title', strtolower($necessaryFields['title'])));        
        $this->addField(Zend_Search_Lucene_Field::Text('document_preview', strtolower($necessaryFields['preview'])));        

    }
}