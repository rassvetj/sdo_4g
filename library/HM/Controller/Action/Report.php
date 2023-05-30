<?php
class HM_Controller_Action_Report extends HM_Controller_Action
{

    private $_cache = array();

    public function getUserMetadata($name, $information)
    {
        $user = new HM_User_UserModel(array('Information' => $information));
        return $user->getMetadataValue($name);
    }

    public function getUserGender($name, $value)
    {
        $values = HM_User_Metadata_MetadataModel::getGenderValues();
        if (isset($values[$value])) return $values[$value];
        return _('Не задан');
    }

    public function getPeriod($name, $period)
    {
        if (strlen($period)) {
            $parts = explode(' - ', $period);
            if (count($parts) == 2) {
                $beginDate = new HM_Date($parts[0]);
                $endDate = new HM_Date($parts[1]);
                return sprintf("%s - %s", $beginDate->get(HM_Date::DATE_SHORT), $endDate->get(HM_Date::DATE_SHORT));
            }
        }
        return $period;
    }

    public function getValue($value, $values)
    {
        return $values[$value];
    }

    public function getEventType($name, $type)
    {
        $types = HM_Event_EventModel::getAllTypes();
        return $types[$type];
    }

	public function getUserAge($name, $date)
	{
	    if ($dateBirth = strtotime($date)) {
	        return floor(date('Y') - date('Y', $dateBirth));
	    }
	    return '';
	}

	public function getClaimantType($name, $type)
	{
	    return HM_Role_ClaimantModel::getType($type);
	}

	public function getScheduleMark($name, $mark)
	{
	    if ($intmark = (int)$mark) {
            /* Коррект 14/08/2013  100, а если будет больше 100? баллов тоже результат, и нужно вернуть 0 если пусто, так как не посчитает SUM */
    	    if ($intmark > 0) {
    	        return $intmark;
    	    }
            /* Коррект 14/08/2013 - Закоментил! Лучше вернуть число чем пусто! */
    	    // return ''; // какое-то левое число,
	    }
        /* -1 не начатое занятие, возвращаем оценку 0 */
	    return ($mark < 0 || $mark == '') ? 0 : $mark; // какая-то строка (напр., результат функции - см. yml)
	}

	// теряется весь смысл генератора, но похоже под-другому никак
	public function getStudentProgress($name, $sid)
	{
	    if (!isset($this->_cache['student-progress'])) {

    	    $select = Zend_Registry::get('serviceContainer')->getService('LessonAssign')->getSelect();
    		$select->from(
    			array('st' => 'Students'),
    			array('SID')
    		)->joinLeft(
    			array('sch' => 'schedule'),
    			'st.CID = sch.CID',
    			array()
    		)->joinLeft(
    			array('schid' => 'scheduleID'),
    			'sch.SHEID = schid.SHEID AND schid.MID=st.MID',
    			array('progress' => new Zend_Db_Expr("CASE WHEN COUNT(schid.SSID) !=0 THEN ROUND(100 * SUM(CASE WHEN (schid.V_STATUS IS NULL) OR (schid.V_STATUS = '') OR (schid.V_STATUS = 0) OR (schid.V_STATUS = -1) THEN 0 ELSE 1 END) / COUNT(schid.SSID)) ELSE -1 END"))
    		)->group('st.SID');

    		if ($collection = $select->query()->fetchAll()) {
    		    foreach ($collection as $row) {
                    $this->_cache['student-progress'][$row['SID']] = ($row['progress'] >= 0) ? $row['progress'] : '';
    		    }
    		}
	    }

	    return isset($this->_cache['student-progress'][$sid]) ? $this->_cache['student-progress'][$sid] : '';
	}

    public function getStudentProgressFilter($data)
    {
        $search = trim($data['value']);
        if (strlen($search)) {
            //$data['select']->where( subselect here);
        }
    }

    public function updateGroupConcat($field)
    {
//[che 25.06.2014 #17108]
	define('MAX_LIST_COUNT', 2);

	$arr = explode(',', $field);
	$arr = array_unique($arr);

	if(count($arr)<=MAX_LIST_COUNT)
		return implode(';<br>', $arr);		

	$out = array_slice($arr, 0, MAX_LIST_COUNT);
	$out[] = '...';

	return "<span title='".str_replace("'", "`", implode(";\n", $arr))."'>".implode(';<br>', $out)."</span>";

//        return str_replace(',', '<br>', $field); 
//
    }

    //костыль для отображения даты на основе выбрнанной функции
    public function dayMonthYear($name, $date)
    {
        return date("d.m.Y", strtotime($date));
    }
    public function monthYear($name, $date)
    {
        return date("m.Y", strtotime($date));
    }
    public function year($name, $date)
    {
        return date("Y", strtotime($date));
    }
}