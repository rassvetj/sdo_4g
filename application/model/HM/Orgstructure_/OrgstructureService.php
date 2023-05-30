<?php
class HM_Orgstructure_OrgstructureService extends HM_Service_Nested
{

    public function delete($id)
    {
        $collection = $this->fetchAll(
            $this->quoteInto('owner_soid = ?', $id)
        );

        if (count($collection)) {
            foreach($collection as $item) {
                $this->delete($item->soid);
            }
        }

        return parent::delete($id);
    }

    public function getTreeContent($parent = 0, $notEncode = true, $current = null, $type = null)
    {
        $tree = array();

        if($type == null){
            $type = array(HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT);
        }


        $collection = $this->fetchAll($this->quoteInto(array('owner_soid = ?', ' AND type IN (?)'), array($parent, $type)), false, null, array('type', 'name'));
        if (count($collection)) {
            foreach($collection as $unit) {
                $tree[] = array(
                    'title' => (($notEncode === false) ? iconv(Zend_Registry::get('config')->charset, 'UTF-8', $unit->name) : $unit->name),
                    'key' => (string) $unit->soid,
                    'isLazy' => ($parent == 0 ? false : true),
                    'isFolder' => true,
                );

                if ($current == $unit->soid) {
                    $tree[count($tree)-1]['active'] = true;
                }

                if ($parent == 0) {
                    $tree[count($tree)-1]['expand'] = true;
                    $tree[] = $this->getTreeContent($unit->soid, $notEncode);
                }
            }
        }

        return $tree;
    }

    public function getDescendants($parent){

        $descendants = array();
        $collection = $this->fetchAll($this->quoteInto('owner_soid = ?', $parent), 'name');
        if (count($collection)) {
            foreach($collection as $unit) {
                $descendants[] = (int)$unit->soid;
                $descendants = array_merge($descendants, $this->getDescendants($unit->soid));
            }
        }
        array_unique($descendants);
        return $descendants;
    }

    public function getDescendansForMultipleSoids($soids)
    {
        $descendants = array();
        if (count($soids)) {
            foreach ($soids as $soid) {
                // рекурсивно всем вложенным
                $descendants = array_merge($descendants, $this->getService('Orgstructure')->getDescendants($soid));
            }
            $checkedPositions = $this->fetchAll($this->quoteInto(
                array('soid IN (?) AND ', 'type = ?'),
                array($soids, HM_Orgstructure_OrgstructureModel::TYPE_POSITION)
            ));
            foreach ($checkedPositions as $position) {
                $descendants[] = (int)$position->soid;
            }
        }
        return $descendants;
    }

    public function getPositionsCodes()
    {
        $positions = array();
        $q = $this->getSelect()
            ->from(
                array('so' => 'structure_of_organ'),
                array(
                    'code' => 'DISTINCT(so.code)',
                    'so.name'
                )
            )
            ->where('so.code IS NOT NULL');
        //  print $q;exit;
        $res = $q->query()->fetchAll();
        foreach($res as $item) {
            $positions[$item['code']] = $item['name'];
        }
        return $positions;
    }

    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('подразделение plural', '%s подразделение', $count), $count);
    }

    public function pluralFormPositionsCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('должность plural', '%s должность', $count), $count);
    }

     /**
     * Возвращает массив с ID всех дочерних элементов структуры для указанного родительского
     * @param int $soid ID родительского элемента
     * @return array
     */
    public function getChildIDs($soid)
    {
        $arChilds = array();
        $items = $this->fetchAll();
        $arWork = $items->getList('soid','owner_soid');

        foreach ( $arWork as $id=>$parentID) {

            if (is_array($parentID)) {
                $parentID = $parentID['id'];
            }

            if (!is_array($arWork[$id])) {
                $arWork[$id] = array( 'id'        => $id,
                                      'childrens' => array()
                                    );
            }

            if (!is_array($arWork[$parentID])) {
                $arWork[$parentID] = array( 'id'        => $parentID,
                                            'childrens' => array()
                                    );
            }

            $arWork[$parentID]['childrens'][] = &$arWork[$id];
        }

        $needElement = (isset($arWork[$soid]))? $arWork[$soid]['childrens'] : array();

        array_walk_recursive($needElement, array($this,'walkRecursiveFunction'), &$arChilds);

        return $arChilds;
    }

    public function walkRecursiveFunction($item,$key,$arChilds)
    {
       $arChilds[] = $item;
    }

    
    /**
     * Делает то же самое, что inserUser 
     * 
     */
    public function assignUser($userId, $ownerSoid, $positionName)
    {
        $owner = $this->getOne($this->find($ownerSoid));
        
        $positionPrev = $this->getOne(
            $this->fetchAll(
                $this->getService('Orgstructure')->quoteInto('mid = ?', $userId)
        ));
        
        if (empty($ownerSoid) || empty($owner)) $ownerSoid = 0; 
        if (empty($positionName)) $positionName = _('Сотрудник'); 
        
        // если ничего не изменилось - ничего не делаем
        if (($positionPrev->name != $positionName) || ($ownerSoid != $positionPrev->owner_soid)) {
        
            if ($positionPrev) {
                // если это перемещение - удаляем и воссоздаём в другом месте
                $this->getService('Orgstructure')->deleteBy(array('soid = ?' => $positionPrev->soid));
                $data = $positionPrev->getValues();
                unset($data['soid']);
            } else {
                // дефолтные параметры для нового сотрудника
                $data = array(
                    'name' => $positionName,
                    'mid' => $userId,
                    'type' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                    'owner_soid' => $ownerSoid
                );
            }
        
            $data['name'] = $positionName;
            $data['owner_soid'] = $ownerSoid;
            
            $this->insert($data, $ownerSoid);
        }
         
    }
    
    
    /**
     * DEPRECATED!
     *      */
    public function insertUser($userId, $positionId, $positionName = null)
    {

        /**
         * Добавил вариант что $positionId может быть не задано.
         * @author Artem Smirnov <tonakai.personal@gmail.com>
         * @date 28 december 2012
         */
        $position = false;
        if($positionId == 0){
            $this->updateWhere(array('mid' => 0), $this->quoteInto('mid = ?', $userId));
            $position = $this->insert(
                array(
                    'name' => !$positionName ? _('Сотрудник') : $positionName,
                    'mid' => $userId,
                    'type' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                    'owner_soid' => !$positionId ? 0 : $positionId
                ));
            return $position;
        }
        $unit = $this->getOne($this->find($positionId));
        $this->updateWhere(array('mid' => 0), $this->quoteInto('mid = ?', $userId));
        if ($unit) {
            if ($unit->type == HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
                $position = $this->insert(
                    array(
                        'name' => !$positionName ? _('Сотрудник') : $positionName,
                        'mid' => $userId,
                        'type' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                        'owner_soid' => !$positionId ? 0 : $positionId
                    ),
                    $unit->soid
                );
            } else {
                $position = $this->updateNode(array('name' => !$positionName ? _('Сотрудник') : $positionName,'mid' => $userId), $positionId, $unit->owner_soid);
            }
        }

        return $position;
    }

    // DEPRECATED!!! 
    public function updateEmployees($soid)
    {
        $orgUnit = $this->getOne($this->find($soid));

        if($orgUnit && $orgUnit->is_manager == HM_Orgstructure_OrgstructureModel::SUPERVISOR){
            $employees = $this->fetchAll(array('owner_soid = ?' => $orgUnit->owner_soid, 'soid != ?' => $soid, 'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION));

            $list = $employees->getList('soid', 'mid');

            if(count($list)){
                $this->getService('User')->updateWhere(array('head_mid' => $orgUnit->mid), array('MID IN (?)' => array_values($list)));
                $this->getService('Orgstructure')->updateWhere(array('is_manager' => 0), array('soid IN (?)' => array_keys($list)));
            }
            $this->getService('Supervisor')->assign($orgUnit->mid);
        }else{
            if($orgUnit->mid > 0){
                $this->getService('Employee')->assign($orgUnit->mid);
            }
        }
    }




    public function update($data){
        $res = parent::update($data);
        //$this->updateEmployees($res->soid);
        return $res;
    }

    public function insert($data, $objectiveNodeId = 0, $position = HM_Db_Table_NestedSet::LAST_CHILD){
        $res = parent::insert($data, $objectiveNodeId, $position);
        //$this->updateEmployees($res->soid);
        return $res;
    }

    public function getDefaultParent()
    {
        // эта логика должна измениться, когда появится настройка области ответственности супервайзера
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_SUPERVISOR)) {
            $position = $this->fetchAllDependence('Parent', array('mid = ?' => $this->getService('User')->getCurrentUserId()));
            if (count($position) && count($position->current()->parent) ) {
                return $position->current()->parent->current();
            }
        }

        $root = new stdClass();
        $headUnitTitle = $this->getService('Option')->getOption('headStructureUnitName');
        if (!strlen($headUnitTitle)) {
            $headUnitTitle = _(HM_Orgstructure_OrgstructureModel::DEFAULT_HEAD_STRUCTURE_ITEM_TITLE);
        }

        $root->soid = 0;
        $root->name = $headUnitTitle;

        return $root;
    }

    static public function getIconClass($type, $isManager)
    {
        $return = 'type-' . $type;
        if ($isManager) {
            $return .= '-manager';
        }
        return $return;
    }

    static public function getIconTitle($type, $isManager)
    {
        if ($type == HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
            return _('Подразделение');
        } elseif ($isManager) {
            return _('Руководитель подразделения');
        }
        return _('Сотрудник');
    }
}