<?php
class HM_Event_EventService extends HM_Service_Abstract
{
    public function delete($id)
    {
        $path = Zend_Registry::get('config')->path->upload->event . $id . '.jpg';
        @unlink($path);

        $this->getService('Lesson')->updateWhere(array('typeID' => HM_Event_EventModel::TYPE_EMPTY), $this->quoteInto('typeID = ?', -$id));

        return parent::delete($id);
    }

    public function updateIcon($eventId, $icon)
    {
        if($icon->isUploaded()){
            $path = Zend_Registry::get('config')->path->upload->event . $eventId . '.jpg';
            $icon->addFilter('Rename', $path, 'icon', array( 'overwrite' => true));
            unlink($path);
            $icon->receive();
            $img = PhpThumb_Factory::create($path);
	        $img->resize(63, 63);
	        $img->save($path);
            return true;
        }
        return false;
    }

    static public function normalizeWeights(&$weightsByType)
    {
        $totalWeight = array_sum($weightsByType);
        foreach ($weightsByType as $typeId => &$weight) {
        	$weight = $weight / $totalWeight;
        }
    }

    public function inheritsType($type, $builtInType)
    {
        if ($type == $builtInType) {
            return true;
        } else {
            if ($type < 0) {
                if ($this->_eventsCache === null) {
                    $this->_eventsCache = $this->fetchAll()->getList('event_id', 'tool');
                }
                return (isset($this->_eventsCache[-$type]) && ($this->_eventsCache[-$type] == $builtInType));
            }
        }
        return false;
    }

}