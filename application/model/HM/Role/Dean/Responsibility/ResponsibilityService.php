<?php
class HM_Role_Dean_Responsibility_ResponsibilityService extends HM_Service_Abstract
{

    public function checkUsers($select, $mid_column, $org_column = 0)
    {

        $userId = $this->getService('User')->getCurrentUserId();
        $options = $this->getService('Dean')->getResponsibilityOptions($userId);
        
        if(!$options['unlimited_classifiers']){
            $responsibilities = $this->getResponsibilities($userId);
            $select->joinInner(
                array('cl' => 'classifiers_links'),
                '(cl.type = '.HM_Classifier_Link_LinkModel::TYPE_PEOPLE.' AND cl.item_id = '.$mid_column . ') OR (cl.type = '.HM_Classifier_Link_LinkModel::TYPE_STRUCTURE.' AND cl.item_id = '.$org_column . ')',
                array()
            );
            $area = $responsibilities->getList('classifier_id', 'classifier_id');
            if(count($area))
                $select->where('cl.classifier_id IN (?)', $responsibilities->getList('classifier_id', 'classifier_id'));
            else
                $select->where('cl.classifier_id IS NULL');
        }

        return $select;
    }

    public function isResponsibilitySet($userId, $classifierId)
    {
        $res = $this->countAll($this->quoteInto(
                                   array('user_id = ?', ' AND classifier_id = ?'),
                                   array($userId, $classifierId)
                               ));
        return !!$res;
    }

    public function addResponsibility($userId, $classifierId)
    {
        $already = $this->countAll($this->quoteInto(
                                       array('user_id = ?', ' AND classifier_id = ?'),
                                       array($userId, $classifierId)
                                   ));
        if(!$already){
            $this->insert(array('user_id' => (int)$userId, 'classifier_id' => (int)$classifierId));
        }
        return !$already;

    }

    public function deleteResponsibilities($userId)
    {
        return $this->deleteBy($this->quoteInto(
                                   array('user_id = ? AND classifier_id > 0'),
                                   array($userId)
                               ));
    }

    public function getResponsibilities($userId)
    {
        $links = $this->fetchAll(
            $this->quoteInto(
                array('user_id = ?'),
                array($userId)
            )
        );
        return $links;
    }

    /**
     * Проверяем наличие классификаторов оргединиц и пиплов и сбрасываем обл ответственности если нужно
     * @return void
     */
    public function checkForUnlimitedClassifiers($classifierTypeId = null)
    {
        $types = $this->getService('ClassifierType')->getClassifierTypes(HM_Classifier_Link_LinkModel::TYPE_UNIT);
        if (!count($types)) {
            $this->getService('DeanResponsibility')->deleteBy(
                $this->quoteInto('classifier_id > ?', 0)
            );

            $this->getService('DeanOptions')->updateWhere(
                array('unlimited_classifiers' => 1),
                $this->quoteInto('user_id > ?', 0)
            );
        } else {

            /*
            Необходимо сделать, чтобы при отключении в классификаторе области его применения "оргструктура
            и уч. записи", для всех Кураторов имеющих ограничения связанные с этим классификатором эти
            ограничения сбрасывались.
            */
            if (null !== $classifierTypeId) {

                $classifierType = $this->getOne($this->getService('ClassifierType')->find($classifierTypeId));
                if ($classifierType) {
                    if (false === strstr($classifierType->link_types, (string) HM_Classifier_Link_LinkModel::TYPE_UNIT)) {
                        $classifiers = $this->getService('Classifier')->fetchAll(
                            $this->quoteInto(array('type = ?', ' AND level = ?'), array($classifierTypeId, 0))
                        );
                        if (count($classifiers)) {
                            foreach($classifiers as $classifier) {
                                $this->getService('DeanResponsibility')->deleteBy(
                                    $this->quoteInto(
                                        array('classifier_id >= ?', ' AND classifier_id <= ?'),
                                        array($classifier->lft, $classifier->rgt)
                                    )
                                );
                            }
                        }
                    }
                }
            }
        }
    }
    
    public function isResponsibleFor($userId)
    {
        // @todo
        return true;
    }
}