<?php
class HM_Search_Indexer
{
    const OBJECT_ACTIVITY = 'activity';

    protected $_index;

    public function __construct($object)
    {

        Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num());
        Zend_Search_Lucene_Search_Query_Wildcard::setMinPrefixLength(0);

        $path = '';
        switch($object) {
            case self::OBJECT_ACTIVITY:
                $path = Zend_Registry::get('config')->path->index->activity;
                break;
        }

        if (!strlen($path)) throw new HM_Exception(_('Индекс не найден'));

        if (file_exists($path)) {
            $this->_index = Zend_Search_Lucene::open($path);
        } else {
            $this->_index = Zend_Search_Lucene::create($path);
        }

    }

    public function insert($document)
	{
		if ($this->_index) {
			$this->_index->addDocument($document);
			$this->_index->commit();
		}
	}

	public function update($id, $document)
	{
		if ($id) {
			$hits = $this->find("object_id:".$id);
			if (count($hits)) {
				$documentFields = $document->getFieldNames();
				foreach($hits as $hit) {
					$doc = $hit->getDocument();
					$docFields = $doc->getFieldNames();


					if (is_array($docFields) && count($docFields)) {
						foreach($docFields as $field) {
							if (!in_array($field, $documentFields)) {
								if (strlen($doc->getFieldValue($field))) {
								    $document->addField($doc->getField($field));
								}
							}
						}
					}
					$this->_index->delete($hit->id);
				}
			}
			$this->insert($document);
		}
	}

	public function delete($id)
	{
		if ($id) {
            $hits = $this->find("object_id:".$id);
            if (count($hits)) {
                foreach($hits as $hit) {
                    $this->_index->delete($hit->id);
                }
            }
	    }
	}


	public function findBy($by, $query)
	{
        try {
            $query = trim($query);
            $words = explode(' ', $query);
            if (is_array($words) && count($words)) {
                $query = '';
                foreach($words as $word) {
                    if (strlen($query)) $query .= ' ';
                    $query .= sprintf('(%s:*%s || %s:%s* || %s:*%s* || %s:%s)', $by, $word, $by, $word, $by, $word, $by, $word);
                }
            }
            return $this->_index->find(strtolower($query));
        } catch (Zend_Search_Lucene_Exception $e) {
            return array();
        }
	}

	public function find($query)
	{
        try {
			$query = trim($query);
			$words = explode(' ', $query);
			if (is_array($words) && count($words)) {
				$query = '';
				foreach($words as $word) {
					$query .= sprintf('(*%s || %s* || *%s* || %s)', $word, $word, $word, $word);
				}
			}
		    return $this->_index->find(strtolower($query));
		} catch (Zend_Search_Lucene_Exception $e) {
			return array();
		}
	} 

}