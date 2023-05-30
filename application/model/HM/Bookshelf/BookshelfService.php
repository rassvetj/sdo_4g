<?php
class HM_Bookshelf_BookshelfService extends HM_Service_Abstract
{
    public function getItems($subjectId, $groupId = false)
	{
		$criteria = $this->quoteInto('subject_id=?', $subjectId);
		if($groupId){
			$criteria = $this->quoteInto(array(' subject_id = ? ', ' AND group_id = ? '), array($subjectId, $groupId));
		}
		return $this->fetchAll($criteria);
	}
	
	public function addItem($raw)
	{
		$data = array(
			'subject_id'   => (int)$raw['subject_id'],
			'group_id'     => (int)$raw['group_id'],
			'file_id'      => (int)$raw['file_id'],
			'author_id'    => (int)$this->getService('User')->getCurrentUserId(),
			'date_created' => new Zend_Db_Expr('NOW()'),
		);
		return $this->insert($data);
	}
	
	public function getById($bookshelf_id)
	{
		return $this->getOne($this->fetchAll($this->quoteInto('bookshelf_id = ?', intval($bookshelf_id))));
	}
	
	public function delete($id)
    {
		return parent::delete($id);
    }
}