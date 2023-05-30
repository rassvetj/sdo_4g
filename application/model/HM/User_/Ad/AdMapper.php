<?php
class HM_User_Ad_AdMapper extends HM_Mapper_Abstract
{
    protected function _createModel($rows, &$dependences = array())
    {
        $collectionClass = $this->getCollectionClass();
        $models = new $collectionClass(array(), $this->getModelClass());
        if (count($rows) < 1) {
            return $models;
        }

        $dns = $roles = false;
        if (Zend_Registry::get('config')->ldap->roles && Zend_Registry::get('config')->ldap->units) {
            $roles = Zend_Registry::get('config')->ldap->roles->toArray();
            $dns   = Zend_Registry::get('config')->ldap->units->toArray();
        }

        if (count($rows) > 0) {
            $dependences = array();
            foreach($rows as $index => $row) {

                if (!isset($row[Zend_Registry::get('config')->ldap->user->uniqueIdField][0])) continue;

                if (strtolower(Zend_Registry::get('config')->ldap->user->uniqueIdField) == 'objectguid') {
                    $row['objectguid'][0] = bin2hex($row['objectguid'][0]);
                }

                $model = array('mid_external' => trim(iconv('UTF-8', Zend_Registry::get('config')->charset, $row[Zend_Registry::get('config')->ldap->user->uniqueIdField][0])));

                if (strtolower(Zend_Registry::get('config')->ldap->user->uniqueIdField) == 'objectguid') {
                    $model['mid_external'] = md5($model['mid_external']);
                }

                $mapping = Zend_Registry::get('config')->ldap->mapping->user->toArray();
                foreach($mapping as $field => $value) {
                    if (!isset($row[$field][0])) continue;
                    $model[$value] = trim(iconv('UTF-8', Zend_Registry::get('config')->charset, $row[$field][0]));
                }

                if ($roles && is_array($roles) && count($roles)) {
                    foreach($roles as $dnId => $role) {
                        if (isset($dns[$dnId])) {
                            if (false !== strstr($row['dn'], $dns[$dnId])) {
                                $model['role'] = $role;
                                break;
                            }
                        }
                    }
                }

                $model['isAD'] = 1;

                $models[count($models)] = $model;
                unset($rows[$index]);
            }


            $models->setDependences($dependences);
        }

        return $models;

    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $rows = $this->getAdapter()->fetchAll($where, $order, $count, $offset);
        return $this->_createModel($rows);
    }

}