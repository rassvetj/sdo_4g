<?php

require_once('lib/classes/CompetenceRole.class.php');
require_once('lib/classes/Poll.class.php');
require_once('lib/classes/Position.class.php');

class CPollCompetenceSummaryReport extends CReportData {
    
    function getReportData() {
        
            $inputData = $this->getInputData();
            
            $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);                        
            
            $mids = false;
            if ($inputData['soid']) {
                if ($positions = CUnitPosition::getSlaves($inputData['soid'])) {
                    foreach($positions as $position) {
                        if ($position->attributes['mid']) {
                            if (!$peopleFilter->is_filtered($position->attributes['mid'])) continue;
                            $mids[] = $position->attributes['mid'];                           
                        }
                    }
                }
            }
            
            $where = '';
            if ($mids) {
                $where = " AND polls_people.mid IN ('".join("','",$mids)."') ";
            }
            
            $sql = "SELECT People.LastName, People.FirstName, People.Patronymic People.MID
                    FROM polls_people
                    INNER JOIN People ON (People.MID = polls_people.mid)
                    WHERE polls_people.poll = '".(int) $inputData['pid']."' $where";
            $res = sql($sql);

            while($row = sqlget($res)) {
                $people[$row['MID']] = htmlspecialchars($row['LastName'].' '.$row['FirstName'].(strlen($row['Patronymic']) ? ' '.$row['Patronymic'] : ''),ENT_QUOTES);
            }
            
            $sql = "SELECT competence_roles.id, competence_roles.name
                    FROM competence_roles
                    INNER JOIN polls_people ON (polls_people.role = competence_roles.id)
                    WHERE polls_people.poll = '".(int) $inputData['pid']."' $where";
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                $roles[$row['id']] = htmlspecialchars($row['name'],ENT_QUOTES);
            }
            
            $sql = "SELECT structure_of_organ.soid, structure_of_organ.name
                    FROM structure_of_organ
                    INNER JOIN polls_people ON (polls_people.soid = structure_of_organ.soid)
                    WHERE polls_people.poll = '".(int) $inputData['pid']."' $where";
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                $positions[$row['soid']] = htmlspecialchars($row['name'],ENT_QUOTES);
            }

            $fields[_("ФИО")] = array('field' => 'name');
            $fields[_("Должность")] = array('field' => 'position');
            
            $data = array();
            $results = new CPollResults($inputData['pid']);
            foreach($results->process($mids) as $result) {
                if (isset($people[$result->mid]) /*&& isset($roles[$result->role]) && isset($positions[$result->soid])*/) {
                    $data[$result->mid]['name']     = $people[$result->mid];
                    $data[$result->mid]['position'] = $positions[$result->soid];
                    $data[$result->mid][str_replace(' ','_',$roles[$result->role])] = $result->result['avg'];
                    $fields[$roles[$result->role]] = array('field' => str_replace(' ','_',$roles[$result->role]));
                }
            }
            
            if (count($data)) {
                foreach($data as $value) {
                    $this->data[] = $value;
                }
            }
                                                                   
            // Нестандартная таблица
            $this->parseFields($fields);
            $this->parseFilterData($this->filterDataBackup);
            // ОБЯЗАТЕЛЬНО!! ВЫПОЛНИТЬ ФУНКЦИЮ ПРЕДКА
            $this->data = parent::getReportData($this->data);
            
            return $this->data;
            
    }
    
    
    /**
    * Функция должна возвращать массив:
    */
    function getReportInputField($inputFieldName,$inputFieldData=false) {
        
        $ret = '';
        
        switch($inputFieldName) {
            
            case 'pid':
            
                $sql = "SELECT id, name FROM polls ORDER BY name";
                $res = sql($sql);
                
                while($row = sqlget($res)) {
                    
                    $ret[$row['id']] = $row['name'];
                    
                }

            break;
            
            case 'soid':
            
                $ret[0] = "Все";
                
                $soidFilter = new CSoidFilter($GLOBALS['SOID_FILTERS']);
                
                $sql = "SELECT soid, name FROM structure_of_organ WHERE type=2 ORDER BY name";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    if (!$soidFilter->is_filtered($row['soid'])) continue;
                    $ret[$row['soid']] = $row['name'];
                                                            
                }
            
            break;
                        
        }
        
        return $ret;

    }
    
}


?> 
