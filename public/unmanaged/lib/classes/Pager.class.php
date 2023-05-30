<?php
/**
 * Created by Yuri Novitsky (c) 2007
 *
 */

class CDBPager {
    var $options = array();
    
    function CDBPager($options = array()) {
        $this->options = $options;
    }
    
    function _getCount() {
        $count = -1;
        $sql = sprintf("SELECT COUNT(DISTINCT %s) AS cnt FROM %s %s %s",
                        $this->options['CDBPager_idName'], 
                        $this->options['CDBPager_table'], 
                        $this->options['CDBPager_join'], 
                        (strlen($this->options['CDBPager_where']) ? 'WHERE '.$this->options['CDBPager_where'] : '')); 
        $res = sql($sql);
        if ($row = sqlget($res)) $count = $row['cnt'];
        return $count;
    }
    
    function getData() {
        $page = array();
        if ($this->options['totalItems'] = $this->_getCount()) {
            require_once($GLOBALS['wwf'].'/lib/PEAR/Pager/Pager.php');
            
            $pager = Pager::factory($this->options);
            
            $page['totalItems']   = $pager_options['totalItems'];
            $page['links']        = $pager->links;
            $page['page_numbers'] = array(
                'current' => $pager->getCurrentPageID(),
                'total'   => $pager->numPages()
            );
            list($page['from'], $page['to']) = $pager->getOffsetByPageId();
            
            $ids = array();
            $sql = sprintf("SELECT %s AS id FROM %s %s %s %s",
                            $this->options['CDBPager_idName'], 
                            $this->options['CDBPager_table'],
                            $this->options['CDBPager_join'],
                            (strlen($this->options['CDBPager_where']) ? 'WHERE '.$this->options['CDBPager_where'] : ''),
                            (strlen($this->options['CDBPager_order']) ? 'ORDER BY '.$this->options['CDBPager_order'] : '')                                                        
                            );
            $res = sql($sql);
            while($row = sqlget($res)) {
                $ids[$row['id']] = $row['id'];
            }

            
            $where = '';
            if (count($ids)) {
                $ids = array_slice($ids,$page['from']-1,$this->options['perPage']);
                $where = 'WHERE '.$this->options['CDBPager_idName']." IN ('".join("','",$ids)."')";
            }
            
            if (strlen($where)) {
                $sql = sprintf("SELECT %s FROM %s %s %s %s",
                                (strlen($this->options['CDBPager_select']) ? $this->options['CDBPager_select'] : $this->options['CDBPager_table'].'*'), 
                                $this->options['CDBPager_table'],
                                '',
                                $where,
                                (strlen($this->options['CDBPager_order']) ? 'ORDER BY '.$this->options['CDBPager_order'] : ''));
                                
                $page['result'] = sql($sql);
            }
            
        }
        return $page;
    }
}

?>