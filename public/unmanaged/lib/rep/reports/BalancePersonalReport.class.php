<?php 

require_once($GLOBALS['wwf'].'/lib/classes/Person.class.php');

class CBalancePersonalReport extends CReportData{
    function getReportData() {
        $inputData = $this->getInputData();
        
        $mid   = (int) $inputData['MID'];
        $track = (int) $inputData['track'];
        
        //if ($mid) {
            //$person = CPerson::get($mid, 'LFP');
            //$this->additionalData[_('ФИО')] = $person->getNameLFP();
        //}
        
        $sql = "SELECT * FROM money_log WHERE mid = '$mid' ORDER BY mlid";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            $data = array();
            $data['n1'] = $row['mlid'];
            $data['n2'] = $row['doc'];
            $data['date'] = date('d.m.Y',$row['date']);
            $data['action'] = $row['reason'];
            $person = CPerson::get($row['who'], 'LFP');
            if ($person) {
                $data['who'] = $person->getNameLFP();
            }
            $this->data[] = $data;
        }
        
        //$this->data[] = $result;           
            
        $this->data = parent::getReportData($this->data);
        return $this->data;
    }
    
    function getReportInputField($inputFieldName,$inputFieldData=false) {
        
        $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
        
        $ret = array();
        
        switch($inputFieldName) {
            
            case 'MID':
               
                $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login
                        FROM tracks2mid tm
                        LEFT JOIN People ON (People.MID=tm.mid)
                        WHERE tm.trid = '".(int) $inputFieldData['track']."'
                        ORDER BY LastName, FirstName, Login";
                $res = sql($sql);                
                while($row = sqlget($res)) {
                    if (!$peopleFilter->is_filtered($row['MID'])) continue;                    
                    $ret[$row['MID']] = $row['LastName'].' '.$row['FirstName'].(strlen($row['Patronymic']) ? ' '.$row['Patronymic'] : '').' ('.$row['Login'].') ';
                                                            
                }                            
            break;
            
            case 'track':
                $sql = "SELECT trid, name FROM tracks";
                $res = sql($sql);
                $ret[0] = _('Выберите элемент');
                while($row = sqlget($res)) {
                    $ret[$row['trid']] = $row['name'];
                }
            break;
                
        }        
        
        return $ret;
    } 
    
    function getSajaxFunctions() {
        return array('process_filter_MID');
    }    
}

function process_filter_MID($search, $current=0) {
    $html = '';
    if (!empty($search)) {
        $search = iconv('UTF-8','Windows-1251',unicode_urldecode($search));
        $search = trim($search);    
        $search = str_replace('*','%',$search);
        $where = "WHERE (People.LastName LIKE '%".addslashes($search)."%'
        OR People.FirstName LIKE '%".addslashes($search)."%'
        OR People.Login LIKE '%".addslashes($search)."%')";
        $people = CBalancePersonalReport::getReportInputField('MID',$GLOBALS['s']['reports']['current']['inputData'],$where);
        if (is_array($people) && count($people)) {
            foreach($people as $mid=>$name) {
                $html .= "<option value=\"$mid\"";
                if ($current == $mid) {
                    $html .= " selected ";
                }
                $html .= "> ".htmlspecialchars($name,ENT_QUOTES)."</option>";
            }
        }
    }
    return $html;
}

?>