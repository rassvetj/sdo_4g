<?php
class Orgstructure_AjaxController extends HM_Controller_Action
{
    public function init()
    {
        $this->_helper->ContextSwitch()->addActionContext('tree', 'xml')->initContext('xml');
    }

    public function treeAction()
    {
        $owner = 0;
        $itemId = (int) $this->_getParam('item_id', 0);
        $onlyDepartments = (int) $this->_getParam('only-departments', false);

        if ($itemId) {
            $item = $this->getOne($this->getService('Orgstructure')->find($itemId));
            if ($item) {
                $owner = $item->owner_soid;
            }
        }

        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');

        $items = array();
        //$tree = $this->getService('Orgstructure')->getTreeContent($itemId, true, null, array(HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT, HM_Orgstructure_OrgstructureModel::TYPE_POSITION));
        $collection = $this->getService('Orgstructure')->fetchAllDependence(
            'Descendant',
            $this->quoteInto(
                array('owner_soid = ?', ' AND type IN (?)'),
                array($itemId, $onlyDepartments ? array(HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) : array(HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT, HM_Orgstructure_OrgstructureModel::TYPE_POSITION))
            ),
            false,
            null,
            array('type', 'name')
        );

        if (count($collection)) {
            foreach($collection as $unit) {

                if ($onlyDepartments) {
                    $leaf = true;
                    if (isset($unit->descendants) && count($unit->descendants)) {
                        foreach ($unit->descendants as $descendant) {
                        	if ($descendant->type == HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
                        	    $leaf = false;
                        	    break;
                        	}
                        }
                    }
                } else {
                    $leaf = $unit->isPosition();
                }

                $items[] = '<item id="'. $unit->soid .'" value="'.htmlspecialchars($unit->name).'" '.($leaf ? 'leaf="yes"' : '').'/>';
            }
        }

        $xml = "<?xml version=\"1.0\" encoding=\"".Zend_Registry::get('config')->charset."\"?><tree owner=\"".$owner."\">".join('', $items)."</tree>";
        $this->view->xml = $xml;

    }
}