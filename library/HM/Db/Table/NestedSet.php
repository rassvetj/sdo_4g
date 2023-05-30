<?php
include_once('Zend/Db/Table.php');

/**
 * Abstract class that extends capabilities of Zend_Db_Table 
 * class, providing API for managing some Nested set table in 
 * database.
 *
 * @author Nikola Posa <posa.nikola@gmail.com>
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License
 */
abstract class HM_Db_Table_NestedSet extends HM_Db_Table
{
	const FIRST_CHILD  = 'firstChild';
	const LAST_CHILD   = 'lastChild';
	const NEXT_SIBLING = 'nextSibling';
	const PREV_SIBLING = 'prevSibling';
	
	const LEFT_TBL_ALIAS  = 'node';
	const RIGHT_TBL_ALIAS = 'parent';

	
	/**
     * Valid objective node positions.
     *
     * @var array
     */
	protected $_validPositions = array(
		self::FIRST_CHILD, 
		self::LAST_CHILD, 
		self::NEXT_SIBLING, 
		self::PREV_SIBLING
	);
	
    /**
     * Left column name in nested table.
     *
     * @var string
     */
    protected $_left;
 
    /**
     * Right column name in nested table.
     *
     * @var string
     */
    protected $_right;

    protected $_level;
    
    protected $_dependenceTable;
    
    protected $_dependenceField;
	
    protected $_dependenceTableName;
	/**
     * Constructor.
     *
	 * Supported params for $config are:
     *   left  - left column name,
     *   right - right column name
	 *
	 * @param array An array of user-specified config options.
     * @return Zend_Db_Table
     */
	public function __construct($config = array())
    {
		if (!empty($config)) {
            $this->setNestedOptions($config);
        }
		
        parent::__construct($config);
		
		$this->_setupPrimaryKey();
		
		$this->_setupLftRgt();
    }
	
	/**
	 * Sets config options.
	 *
	 * @param array Config options.
	 * @return void
	 */
	public function setNestedOptions($options)
	{
		foreach ($options as $key => $value) {
			switch ($key) {
                case 'left':
                    $this->_left = (string)$value;
                    break;
                case 'right':
                    $this->_right = (string)$value;
                    break;
                case 'level':
                    $this->_level = (string)$value;
                    break;
                default:
                    break;
            }
		}
	}
	
	/**
	 * Defined by Zend_Db_Table_Abstract.
	 *
	 * @return void
	 */
	protected function _setupPrimaryKey()
	{
		parent::_setupPrimaryKey();
		
		if (count($this->_primary) > 1) { //Compound key?
			include_once('NP/Db/Table/NestedSet/Exception.php');
			throw new HM_Db_Table_NestedSet_Exception('Tables with compound primary key are not currently supported.');
		}
	}
	
	/**
	 * Validating supplied "left" and "right" columns.
	 *
	 * @return void
	 */
	protected function _setupLftRgt()
	{
		if (!$this->_left || !$this->_right) {
			include_once('NP/Db/Table/NestedSet/Exception.php');
			throw new HM_Db_Table_NestedSet_Exception('Both "left" and "right" column names must be supplied.');
		}
		
		$this->_setupMetadata();
		
//		if (count(array_intersect(array($this->_left, $this->_right), array_keys($this->_metadata))) < 2) {
//			include_once('NP/Db/Table/NestedSet/Exception.php');
//			throw new HM_Db_Table_NestedSet_Exception('Supplied "left" and "right" were not found.');
//		}
	}

	/**
     * Overriding fetchAll() method defined by Zend_Db_Table_Abstract.
     *
     * @param string|array|Zend_Db_Table_Select $where  	 OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
	 * @param bool 								$getAsTree   OPTIONAL Whether to retrieve nodes as tree.
	 * @param string                      		$parentAlias OPTIONAL If this argument is supplied, additional column, named after value of this argument, will be returned, containing id of a parent node will be included in result set.
     * @param string|array                      $order       OPTIONAL An SQL ORDER clause.
     * @param int                               $count  	 OPTIONAL An SQL LIMIT count.
     * @param int                               $offset 	 OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract
     */
	public function fetchAll($where = null, $getAsTree = false, $parentAlias = null, $order = null, $count = null, $offset = null)
	{
		if ($getAsTree === true) { //If geeting nodes as tree, other arguments are omitted.
			return $this->getTree($where);
		}
		elseif ($parentAlias != null) {
			return parent::fetchAll($this->getSelect($where, $parentAlias, $order, $count, $offset));
		}
		else {
			return parent::fetchAll($where, $order, $count, $offset);
		}
	}
	
	/**
     * Overriding fetchRow() method defined by Zend_Db_Table_Abstract.
     *
     * @param string|array|Zend_Db_Table_Select $where  	 OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
	 * @param string                      		$parentAlias OPTIONAL If this argument is supplied, additional column, named after value of this argument, will be returned, containing id of a parent node will be included in result set.
     * @param string|array                      $order       OPTIONAL An SQL ORDER clause.
     * @return Zend_Db_Table_Row_Abstract|null
     */
	public function fetchRow($where = null, $parentAlias = null, $order = null)
	{
		if ($parentAlias != null) {
			return parent::fetchRow($this->getSelect($where, $parentAlias, $order));
		}
		else {
			return parent::fetchRow($where, $order);
		}
	}
	
	/**
     * Generates and returns select that is used for fetchAll() and fetchRow() methods.
     *
     * @param string|array|Zend_Db_Table_Select|null $where  	  An SQL WHERE clause or Zend_Db_Table_Select object.
	 * @param string|null                      		 $parentAlias Additional column, named after value of this argument, will be returned, containing id of a parent node will be included in result set.
     * @param string|array|null                      $order       An SQL ORDER clause.
	 * @param int|null                               $count  	  OPTIONAL An SQL LIMIT count.
     * @param int|null                           	 $offset 	  OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Select
     */
	protected function getSelect($where, $parentAlias, $order, $count = null, $offset = null)
	{
		$parentAlias = (string)$parentAlias;
		
		$leftCol = $this->getAdapter()->quoteIdentifier($this->_left, true);
		$rightCol = $this->getAdapter()->quoteIdentifier($this->_right, true);
		
		$parentSelect = $this->select()
			->from($this->_name, array($this->_primary[1]))
			->where(self::LEFT_TBL_ALIAS . '.' . $leftCol . ' BETWEEN ' . $leftCol . '+1 AND ' . $rightCol)
			->order("$this->_left DESC")
			->limit(1);
			
		$select = $this->select()->from(array(self::LEFT_TBL_ALIAS => $this->_name), array('*', $parentAlias => "($parentSelect)"));
		
		if ($where !== null) {
			$this->_where($select, $where);
		}

		if ($order !== null) {
			$this->_order($select, $order);
		}

		if ($count !== null || $offset !== null) {
			$select->limit($count, $offset);
		}
		
		return $select;
	}
	
	/**
	 * Gets whole tree, including depth information.
	 *
	 * @param mixed An SQL WHERE clause or Zend_Db_Table_Select object.
	 * @return array
	 */
	public function getTree($where = null)
	{
        $fields = array_keys($this->_metadata);
        unset($fields[array_search($this->_primary[1], $fields)]);
        foreach($fields as $index => $field) {
            $fields[$index] = self::LEFT_TBL_ALIAS.'.'.$field;
        }

		$primary = $this->getAdapter()->quoteIdentifier($this->_primary[1], true);
		$leftCol = $this->getAdapter()->quoteIdentifier($this->_left, true);
		$rightCol = $this->getAdapter()->quoteIdentifier($this->_right, true);
		
		$node = self::LEFT_TBL_ALIAS;
		$parent = self::RIGHT_TBL_ALIAS;
		
		$select = $this->select()->setIntegrityCheck(false)
			->from(array(self::LEFT_TBL_ALIAS => $this->_name), array(self::LEFT_TBL_ALIAS . '.'.$this->_primary[1], 'depth' => new Zend_Db_Expr('(COUNT(' . $parent . '.' . $primary . ') - 1)')) + $fields)
			->join(array(self::RIGHT_TBL_ALIAS => $this->_name), '(' . self::LEFT_TBL_ALIAS . '.' . $leftCol . ' BETWEEN ' . self::RIGHT_TBL_ALIAS . '.' . $leftCol . ' AND ' . self::RIGHT_TBL_ALIAS . '.' . $rightCol . ')', array())
			->group(array(self::LEFT_TBL_ALIAS . '.' . $this->_primary[1]) + $fields)
			->order(self::LEFT_TBL_ALIAS . '.' . $this->_left);
		
		if ($where !== null) {
			$this->_where($select, $where);
		}
		return parent::fetchAll($select);
	}

    public function getChildren($nodeId, $oneLevel = true, $where = null)
    {
        $primary = $this->getAdapter()->quoteIdentifier($this->_primary[1], true);
        $row = $this->fetchRow("$primary = ".(int) $nodeId);
        if ($row) {
            if (null !== $where) {
                $where .= ' AND ';
            } else {
                $where = '';
            }

            $where .= '('.self::LEFT_TBL_ALIAS . '.' . $this->_left .' > '.$row->{$this->_left}
            .' AND '.self::LEFT_TBL_ALIAS . '.' . $this->_right .' < '.$row->{$this->_right};

            if ($oneLevel) {
                $where .= ' AND '.self::LEFT_TBL_ALIAS . '.' . $this->_level . ' = '.(int) ($row->{$this->_level} + 1);
            }

            $where .= ')';

            return $this->getTree($where);
        } else {
            if (null !== $where) {
                $where .= ' AND ';
            } else {
                $where = '';
            }

            if ($oneLevel) {
                $where .= self::LEFT_TBL_ALIAS . '.' . $this->_level . ' = 0';
            }

            return $this->getTree($where);
        }

    }
	
	/**
     * Overriding insert() method defined by Zend_Db_Table_Abstract.
     *
	 * @param array Submitted data.
	 * @param int|null Objective node id (optional).
	 * @param string Position regarding on objective node (optional).
	 * @return mixed
     */
	public function insert($data, $objectiveNodeId = null, $position = self::LAST_CHILD)
	{
		if (!$this->checkNodePosition($position)) {
			include_once('NP/Db/Table/NestedSet/Exception.php');
			throw new HM_Db_Table_NestedSet_Exception('Invalid node position is supplied.');
		}

        $data[$this->_level] = 0;

        if ($objectiveNodeId > 0) {
            $primary = $this->getAdapter()->quoteIdentifier($this->_primary[1], true);
            $row = $this->fetchRow("$primary = ".$objectiveNodeId);
            if ($row) {
                $data[$this->_level] = $row->{$this->_level};
                switch($position){
                    case self::FIRST_CHILD:
                    case self::LAST_CHILD:
                        $data[$this->_level] ++;
                        break;
                }
            }
        }
		
		$data = array_merge($data, $this->getLftRgt($objectiveNodeId, $position));
		return parent::insert($data);
	}
	
	/**
     * Updates info of some node.
     *
	 * @param array Submitted data.
	 * @param int Id of a node that is being updated.
	 * @param int Objective node id.
	 * @param string Position regarding on objective node.
	 * @return mixed
     */
	public function updateNode($data, $id, $objectiveNodeId, $position = self::LAST_CHILD, $recursive = true)
	{
		$id = (int)$id;
		$objectiveNodeId = (int)$objectiveNodeId;
		
		if (!$this->checkNodePosition($position)) {
			include_once('NP/Db/Table/NestedSet/Exception.php');
			throw new HM_Db_Table_NestedSet_Exception('Invalid node position is supplied.');
		}
		
        $children = array();
		if ($objectiveNodeId !== (int) $this->getCurrentObjectiveId($id, $position)) { //Objective node differs?
            $children = $this->getChildren($id, true);

			$this->reduceWidth($id);

            $primary = $this->getAdapter()->quoteIdentifier($this->_primary[1], true);
            $row = $this->fetchRow("$primary = ".$objectiveNodeId);
            if ($row) {
                $data[$this->_level] = $row->{$this->_level};
                switch($position){
                    case self::FIRST_CHILD:
                    case self::LAST_CHILD:
                        $data[$this->_level] ++;
                        break;
                }
            } else {
                $data[$this->_level] = 0;
            }

			$data = array_merge($data, $this->getLftRgt($objectiveNodeId, $position, $id));
		}
		
		$primary = $this->getAdapter()->quoteIdentifier($this->_primary[1], true);
		$where = $this->getAdapter()->quoteInto($primary . ' = ?', $id, Zend_Db::INT_TYPE);
		unset($data[$primary]); // MSSQL валится при попытке обновить primaryKey
		
		$ret = $this->update($data, $where);

        $primary = '';
        if (count($children) && $recursive) {
            foreach($children as $child) {
                if (!$primary) {
                    $primary = $this->_primary[1];
                    if (!$child->{$primary}) {
                        $primaries = $this->getPrimaryKey();
                        if (isset($primaries[1])) {
                            $primary = $primaries[1];
                        }
                    }
                }
                if ($primary) {
                    $this->updateNode(array(), $child->{$primary}, $id, self::LAST_CHILD, $recursive);
                }
            }
        }

        return $ret;
	}
	
	/**
	 * Checks whether valid node position is supplied.
	 *
	 * @param string Position regarding on objective node.
	 * @return bool
	 */
	private function checkNodePosition($position)
	{
		if (!in_array($position, $this->_validPositions)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Deletes some node(s) and returns ids of deleted nodes.
	 *
	 * @param mixed Id of a node.
	 * @param bool Whether to delete all child nodes.
	 * @return array
	 */
	public function deleteNode($id, $recursive = false)
	{
		$retval = array();
		
		$id = (int)$id;

		$primary = $this->getAdapter()->quoteIdentifier($this->_primary[1], true);

		if ($recursive == false) {
			$this->reduceWidth($id);
			
			//Deleting node.
			$where = $this->getAdapter()->quoteInto($primary . ' = ?', $id, Zend_Db::INT_TYPE);
			$affected = $this->delete($where);
			
			if ((int)$affected > 0) { //Only if we really deleted some nodes.	
				$retval = array($id);
			}
		}
		else {
			$tableName = $this->getAdapter()->quoteIdentifier($this->_name, true);
			$leftCol = $this->getAdapter()->quoteIdentifier($this->_left, true);
			$rightCol = $this->getAdapter()->quoteIdentifier($this->_right, true);
			
			$sql = "SELECT $leftCol, $rightCol, ($rightCol - $leftCol + 1) AS width FROM $tableName WHERE $primary = " . $this->getAdapter()->quote($id, Zend_Db::INT_TYPE);
			$result = $this->getAdapter()->fetchRow($sql);
			$lft = $result[$this->_left];
			$rgt = $result[$this->_right];
			$width = $result['width'];
			$result = $this->fetchAll("$leftCol BETWEEN ".(int) $lft ." AND ". (int) $rgt); //Getting ids of nodes that will be deleted, as those will be return value of this method.
			if ($result) {
                $pk = $this->getPrimaryKey();
				foreach ($result as $row) {
		            $retval[] = $row[$pk[1]];
				}
				
				//Deleting items.
				$this->delete("$leftCol BETWEEN ".(int) $lft. " AND ". (int) $rgt);
				
				$this->update(array($this->_left=>new Zend_Db_Expr("$leftCol - $width")), "$leftCol > ". (int) $lft);
				
				$this->update(array($this->_right=>new Zend_Db_Expr("$rightCol - $width")), "$rightCol > ". (int) $rgt);
			}	
		}
		
		return $retval;
	}
	
	/**
	 * Generates left and right column value, based on id of a 
	 * objective node.
	 *
	 * @param mixed Id of a objective node.
	 * @param string Position in tree.
	 * @param int|null Id of a node for which left and right column values are being generated (optional).
	 * @return array
	 */
	protected function getLftRgt($objectiveNodeId, $position, $id = null)
	{
		$lftRgt = array();
		
		$primary = $this->getAdapter()->quoteIdentifier($this->_primary[1], true);
		$leftCol = $this->getAdapter()->quoteIdentifier($this->_left, true);
		$rightCol = $this->getAdapter()->quoteIdentifier($this->_right, true);
		
		if ($objectiveNodeId) { //User selected some objective node?
			$result = $this->fetchRow($this->getAdapter()->quoteInto("$primary = ?", $objectiveNodeId, Zend_Db::INT_TYPE));
            $left = $result[$this->_left];
			$right = $result[$this->_right];
			
			$sql1 = '';
			$sql2 = '';
			switch ($position) {
				case self::FIRST_CHILD :
					$lftRgt[$this->_left] = $left + 1;
					$lftRgt[$this->_right] = $left + 2;
					
					$this->update(array($this->_right=>new Zend_Db_Expr("$rightCol + 2")), "$rightCol > $left");
					$this->update(array($this->_left=>new Zend_Db_Expr("$leftCol + 2")), "$leftCol > $left");
					
					break;
				case self::LAST_CHILD :
					$lftRgt[$this->_left] = $right;
					$lftRgt[$this->_right] = $right + 1;

					$this->update(array($this->_right=>new Zend_Db_Expr("$rightCol + 2")), "$rightCol >= $right");
					$this->update(array($this->_left=>new Zend_Db_Expr("$leftCol + 2")), "$leftCol > $right");
					break;
				case self::NEXT_SIBLING :
					$lftRgt[$this->_left] = $right + 1;
					$lftRgt[$this->_right] = $right + 2;
					
					$this->update(array($this->_right=>new Zend_Db_Expr("$rightCol + 2")), "$rightCol > $right");
					$this->update(array($this->_left=>new Zend_Db_Expr("$leftCol + 2")), "$leftCol > $right");
					
					break;
				case self::PREV_SIBLING :
					$lftRgt[$this->_left] = $left;
					$lftRgt[$this->_right] = $left + 1;
					
					$this->update(array($this->_right=>new Zend_Db_Expr("$rightCol + 2")), "$rightCol > $left");
					$this->update(array($this->_left=>new Zend_Db_Expr("$leftCol + 2")), "$leftCol >= $left");
					
					break;
			}
		}
		else {
			$id = (int)$id;
			$sql = "SELECT MAX($rightCol) AS max_rgt FROM " . $this->getAdapter()->quoteIdentifier($this->_name, true) . " WHERE $primary != $id";
			$result = $this->getAdapter()->fetchRow($sql);
			
			if ($result == null) { //No data? First node...
				$lftRgt[$this->_left] = 1;
			}
			else {
				$lftRgt[$this->_left] = (int)$result['max_rgt'] + 1;
			}
			
			$lftRgt[$this->_right] = $lftRgt[$this->_left] + 1;
		}
		
		return $lftRgt;
	}
	
	/**
	 * Reduces lft and rgt values of some nodes, on which some 
	 * node that is changing position in tree, or being deleted, 
	 * has effect.
	 *
	 * @param mixed Id of a node.
	 * @return void
	 */
	protected function reduceWidth($id)
	{
		$primary = $this->getAdapter()->quoteIdentifier($this->_primary[1], true);
		$leftCol = $this->getAdapter()->quoteIdentifier($this->_left, true);
		$rightCol = $this->getAdapter()->quoteIdentifier($this->_right, true);
		
		$sql = "SELECT $leftCol, $rightCol, ($rightCol - $leftCol + 1) AS width 
			FROM " . $this->getAdapter()->quoteIdentifier($this->_name) . " 
			WHERE $primary = " . $this->getAdapter()->quote($id, Zend_Db::INT_TYPE);
		if ($result = $this->getAdapter()->fetchRow($sql)) {
		
		$left = $result[$this->_left];
		$right = $result[$this->_right];
		$width = $result['width'];
		
		if ((int)$width > 2) { //Some node that has childs.
			//Updating child nodes.
			$this->update(array($this->_left=>new Zend_Db_Expr("$leftCol - 1"), $this->_right=>new Zend_Db_Expr("$rightCol - 1")), "$leftCol BETWEEN $left AND $right");
		}
		
		//Updating parent nodes and nodes on higher levels.
		
		$this->update(array($this->_left=>new Zend_Db_Expr("$leftCol - 2")), "$leftCol > $left AND $rightCol > $right");
		
		$this->update(array($this->_right=>new Zend_Db_Expr("$rightCol - 2")), "$rightCol > $right");
		}
	}
	
	/**
	 * Gets id of some node's current objective node.
	 *
	 * @param mixed Node id.
	 * @param string Position in tree.
	 * @return string|null
	 */
	public function getCurrentObjectiveId($nodeId, $position)
	{	
		$sql = '';
		
		$tableName = $this->getAdapter()->quoteIdentifier($this->_name, true);
		$primary = $this->getAdapter()->quoteIdentifier($this->_primary[1], true);
		$leftCol = $this->getAdapter()->quoteIdentifier($this->_left, true);
		$rightCol = $this->getAdapter()->quoteIdentifier($this->_right, true);
		
		switch ($position) {
			case self::FIRST_CHILD :
				$sql = $this->getAdapter()->quoteInto("SELECT node.$primary 
				FROM $tableName node, (SELECT $leftCol, $rightCol FROM $tableName WHERE $primary = ?) AS current 
				WHERE current.$leftCol BETWEEN node.$leftCol+1 AND node.$rightCol AND current.$leftCol - node.$leftCol = 1
				ORDER BY node.$leftCol DESC", $nodeId, Zend_Db::INT_TYPE);
				
				break;
			case self::LAST_CHILD :
				$sql = $this->getAdapter()->quoteInto("SELECT node.$primary 
				FROM $tableName node, (SELECT $leftCol, $rightCol FROM $tableName WHERE $primary = ?) AS current 
				WHERE current.$leftCol BETWEEN node.$leftCol+1 AND node.$rightCol AND node.$rightCol - current.$rightCol = 1
				ORDER BY node.$leftCol DESC", $nodeId, Zend_Db::INT_TYPE);
				
				break;
			case self::NEXT_SIBLING :
				$sql = $this->getAdapter()->quoteInto("SELECT node.$primary
				FROM $tableName node, (SELECT $leftCol FROM $tableName WHERE $primary = ?) AS current 
				WHERE current.$leftCol - node.$rightCol = 1", $nodeId, Zend_Db::INT_TYPE);
				
				break;
			case self::PREV_SIBLING :
				$sql = $this->getAdapter()->quoteInto("SELECT node.$primary
				FROM $tableName node, (SELECT $rightCol FROM $tableName WHERE $primary = ?) AS current 
				WHERE node.$leftCol - current.$rightCol = 1", $nodeId, Zend_Db::INT_TYPE);
				
				break;
		}
		
		$result = $this->getAdapter()->fetchRow($sql);

      	if (!isset($result[$this->_primary[1]])) {
            $primary = $this->getPrimaryKey();
            return $result[$primary[1]];
        }
		
		return $result[$this->_primary[1]];
	}
}
?>