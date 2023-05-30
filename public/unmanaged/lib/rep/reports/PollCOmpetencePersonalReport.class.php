<?php

require_once('lib/classes/CompetenceRole.class.php');
require_once('lib/classes/Poll.class.php');
require_once('lib/classes/Position.class.php');


class CPollCompetencePersonalReport extends CReportData {
    
    function getReportData() {
        
            $inputData = $this->getInputData();
                        
            $sql = "SELECT competence_roles.id, competence_roles.name
                    FROM competence_roles
                    INNER JOIN polls_people ON (polls_people.role = competence_roles.id)
                    WHERE polls_people.poll = '".(int) $inputData['pid']."' $where";
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                $roles[$row['id']] = htmlspecialchars($row['name'],ENT_QUOTES);
            }
            
            $results = new CPollResults($inputData['pid']);
            foreach($results->process(array($inputData['mid'])) as $result) {
                if (is_array($result->result['info']) && count($result->result['info']) && isset($roles[$result->role])) {
                    foreach($result->result['info'] as $competence => $values) {
                        $data['role']  = $roles[$result->role];
                        $data['name']  = $competence;
                        $data['level'] = $values['avg'];
                        
                        $this->data[] = $data;
                    }
                }
            }
                                                                   
            // ОБЯЗАТЕЛЬНО!! ВЫПОЛНИТЬ ФУНКЦИЮ ПРЕДКА
            $this->data = parent::getReportData($this->data);
            
            return $this->data;
            
    }
    
    
    /**
    * Функция должна возвращать массив:
    */
    function getReportInputField($inputFieldName, $inputFieldData=false) {
        
        $ret = '';
        
        switch($inputFieldName) {
            
            case 'pid':
            
                $sql = "SELECT id, name FROM polls ORDER BY name";
                $res = sql($sql);
                
                while($row = sqlget($res)) {
                    
                    $ret[$row['id']] = $row['name'];
                    
                }

            break;
            
            case 'mid':
                
                if (!$inputFieldData['pid']) {
                    $sql = "SELECT id, name FROM polls ORDER BY name LIMIT 1";
                    $res = sql($sql);
                    
                    if($row = sqlget($res)) {
                        $inputFieldData['pid'] = $row['id'];
                    }
                }
                            
                $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
                
                $sql = "SELECT People.LastName, People.FirstName, People.Login, People.MID 
                        FROM People 
                        INNER JOIN polls_people ON (polls_people.mid=People.MID)
                        WHERE polls_people.poll = '".(int) $inputFieldData['pid']."' ORDER BY People.LastName, People.FirstName, People.Login";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    if (!$peopleFilter->is_filtered($row['MID'])) continue;
                    $ret[$row['MID']] = htmlspecialchars($row['LastName'].' '.$row['FirstName'].((!empty($row['Login'])) ? ' ('.$row['Login'].')' : ''),ENT_QUOTES);                                                            
                }
            
            break;
                        
        }
        
        return $ret;

    }
    
}


?> 
