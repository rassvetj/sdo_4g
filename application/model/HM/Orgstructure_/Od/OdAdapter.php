<?php
class HM_Orgstructure_Od_OdAdapter extends HM_Adapter_Import_Abstract
{
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    private $_db = null;

    private function _setDb()
    {
        if (null === $this->_db) {
            $this->_db = Zend_Db::factory($this->_options['adapter'], $this->_options['params']);
        }
        return $this->_db;
    }

    public function needToUploadFile()
    {
        return false;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $select1 = $this->_db->select();
        $select2 = clone $select1;
        $select = clone $select1;

        $select1->from(
            'OD_DEPTS_T',
            array(
                'soid_external' => '#UNID',
                'owner_soid' => 'PARENTREFUNID',
                'name' => 'DEPARTMENTNAMEDP',
                'type' => new Zend_Db_Expr("0"),
                'mid' => new Zend_Db_Expr("''"),
                'firstname' => new Zend_Db_Expr("''"),
                'lastname' => new Zend_Db_Expr("''"),
                'patronymic' => new Zend_Db_Expr("''")
            )
        );

        $select2->from(
            array('posts' => 'OD_POSTS_T'),
            array(
                'soid_external' => 'posts.#UNID',
                'owner_soid' => 'posts.PARENTREFUNID',
                'name' => 'posts.POSITIONNAMEDP',
                'type' => new Zend_Db_Expr("1"),
                'mid' => 'persons.TABELID',
                'firstname' => 'persons.FIRSTNAMEDP',
                'lastname' => 'persons.LASTNAMEDP',
                'patronymic' => 'persons.MIDDLENAMEDP'

            )
        );
        $select2->joinLeft(array('persons' => 'OD_PERSONS_T'), '"persons"."#UNID" = "posts"."USERNAMEID"', array());

        $select->union(array($select1, $select2), Zend_Db_Select::SQL_UNION_ALL);

        return $select->query()->fetchAll();
    }

    public function setOptions($options)
    {
        if (!isset($options['adapter']) || !isset($options['params'])) {
            throw new InvalidArgumentException(_('Required options not found'));
        }

        parent::setOptions($options);
        $this->_setDb($options);
    }
}