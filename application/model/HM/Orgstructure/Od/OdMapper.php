<?php
class HM_Orgstructure_Od_OdMapper extends HM_Mapper_Abstract
{
    protected function _createModel($rows, &$dependences = array())
    {
        $collectionClass = $this->getCollectionClass();
        $models = new $collectionClass(array(), $this->getModelClass());
        if (count($rows) < 1) {
            return $models;
        }

        if (count($rows) > 0) {
            $dependences = array();
            foreach($rows as $index => $row) {
                $row['mid'] = trim($row['mid']);
                if (strlen($row['mid'])) {
                    $dependences['mid'][$row['mid']]['user'][$row['mid']] = array(
                        'row' => array(
                            'mid'          => $row['mid'],
                            'mid_external' => $row['mid'],
                            'FirstName'    => ($row['firstname'] ? trim($row['firstname']) : ''),
                            'LastName'     => ($row['lastname'] ? trim($row['lastname']) : ''),
                            'Patronymic'   => ($row['patronymic'] ? trim($row['patronymic']) : '')
                         ),
                        'refClass' => 'HM_User_UserModel'
                    );
                }
                unset($row['firstname']);
                unset($row['lastname']);
                unset($row['patronymic']);

				$row['name'] = trim($row['name']);
				$row['owner_soid'] = trim($row['owner_soid']);
				$row['soid_external'] = trim($row['soid_external']);

                $models[count($models)] = $row;
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