<?
class HM_Orgstructure_Unit_UnitService extends HM_Service_Abstract{

    /**
     * for each $org_id descendant delete every links with $currentClassifierTypes classifiers
     * add links with $currentClassifiers
     * @param  $org_id
     * @param  $currentClassifierTypes
     * @param  $currentClassifiers
     * @return void
     */
    public function setClassifiers($org_id, $currentClassifierTypes, $currentClassifiers){

        $classifiers = $this->getService('Classifier')->fetchAll(array('type IN (?)' => $currentClassifierTypes))->getList('classifier_id', 'classifier_id');
        $descendants = $this->getService('Orgstructure')->getDescendants($org_id);
        $descendants[] = $org_id;

        // structure links
        $res = $this->getService('ClassifierLink')->deleteBy(array(
                                                                  'classifier_id IN (?)' => $classifiers,
                                                                  'item_id IN (?)' => $descendants,
                                                                  'type = ?' => HM_Classifier_Link_LinkModel::TYPE_STRUCTURE
                                                             ));
        foreach($descendants as $descendant){
            foreach($currentClassifiers as $classifier)
                $res = $this->getService('ClassifierLink')->insert(array(
                                                                        'classifier_id' => $classifier,
                                                                        'item_id' => $descendant,
                                                                        'type' => HM_Classifier_Link_LinkModel::TYPE_STRUCTURE
                                                                   ));
        }

        // users links
/*        $users = $this->getService('Orgstructure')->fetchAll(array('soid IN (?)' => $descendants, 'mid IS NOT NULL', 'mid != 0'))->getList('soid', 'mid');
        $res = $this->getService('ClassifierLink')->deleteBy(array(
                                                                  'classifier_id IN (?)' => $classifiers,
                                                                  'item_id IN (?)' => $users,
                                                                  'type = ?' => HM_Classifier_Link_LinkModel::TYPE_PEOPLE
                                                             ));
        foreach($users as $user){
            foreach($currentClassifiers as $classifier)
                $res = $this->getService('ClassifierLink')->insert(array(
                                                                        'classifier_id' => $classifier,
                                                                        'item_id' => $user,
                                                                        'type' => HM_Classifier_Link_LinkModel::TYPE_PEOPLE
                                                                   ));
        }*/
    }

    public function getInfo($unit)
    {
        $department = ($unit->owner_soid) ? $this->getOne($this->getService('Orgstructure')->find($unit->owner_soid)) : false;
        $classifiers = $this->getService('ClassifierLink')->fetchAllDependence(
            'Classifier',
            $this->getService('ClassifierLink')->quoteInto(
                array('item_id = ?', 'AND type = ?'),
                array($unit->soid, HM_Classifier_Link_LinkModel::TYPE_STRUCTURE)
            )
        )->getList('classifier_id', 'classifiers');
        return array('department' => $department, 'post' => $unit, 'classifiers' => $classifiers);
    }

}