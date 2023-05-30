<?php
class HM_Webinar_History_HistoryService extends HM_Service_Abstract
{

    public function insertCurrentItem($pointId, $itemId){
        
        
        $userId = $this->getService('User')->getCurrentUserId();
        
        if ($userId > 0) {
        	$data = array(
                        'userId' => $userId,
                        'pointId' => $pointId,
                        'action' => 'set',
                        'item' => $itemId,
                        'datetime' => date('Y-m-d H:i:s')
                    );
            return $this->insert($data);
        }
    }
    
    
    public function setCurrentItem($pointId, $itemId){
    
    
    
    
    }

	public function getFiles($pointId) {
		$files = array();

		if ($pointId) {
			$select = $this->getSelect()->from('webinar_history')
				->where('pointId = ?', $pointId)
				->where('action = ?', 'record start')
				->where('item <> ?', '');

			$historyItems = $select->query()->fetchAll();
			if ($historyItems) {
				foreach ($historyItems as $item) {
					if (strlen($item['item'])) {
						$list = Webinar_Service::getInstance()->getFiles($item['item']);
						if (is_array($list) && count($list)) {
							foreach ($list as $file) {
								$files[$file] = $file;
							}
						}
					}
				}
			}
		}

		return $files;
	}
	/**
	 * возвращ€ет путь где наход€тс€ файлы на сервере ped5
	 * @param $pointId
	 * @return $pathToFiles
	 */
	public function getPathToFiles($pointId) {
		$pathToFiles = '';
		if ($pointId) {
			$select = $this->getSelect()->from('webinar_history')
				->where('pointId = ?', $pointId)
				->where('action = ?', 'record start')
				->where('item <> ?', '')
				->limit('1', '');
			$historyItem = $select->query()->fetchAll();
			if ($historyItem) {
				if (strlen($historyItem[0]['item'])) {
					$pathToFiles = Webinar_Service::getInstance()->getPathToFiles($historyItem[0]['item']);
				}
			}
		}
		return $pathToFiles;
	}
    
    
}