<?

class ATUser extends User {

    function isSlave($mid) {
        $sql = "SELECT mid FROM structure_of_organ WHERE mid = '".(int) $mid."' AND `type` = 0";
        $res = sql($sql);

        return sqlrows($res);
    }

    function isBoss($mid) {
        $sql = "SELECT mid FROM structure_of_organ WHERE mid = '".(int) $mid."' AND `type` = 1";
        $res = sql($sql);

        return sqlrows($res);
    }

    function isManager($mid) {
        $sql = "SELECT id FROM managers WHERE mid = '".(int) $mid."'";
        $res = sql($sql);

        return sqlrows($res);
    }

    function isOnlyATUser($mid) {
        $sql = "SELECT MID FROM Students WHERE MID ='".(int) $mid."' LIMIT 1";
        $res = sql($sql);
        if (sqlrows($res)) return false;

        $sql = "SELECT MID FROM claimants WHERE MID ='".(int) $mid."' LIMIT 1";
        $res = sql($sql);
        if (sqlrows($res)) return false;

        $sql = "SELECT MID FROM graduated WHERE MID ='".(int) $mid."' LIMIT 1";
        $res = sql($sql);
        if (sqlrows($res)) return false;

        $sql = "SELECT MID FROM Teachers WHERE MID ='".(int) $mid."' LIMIT 1";
        $res = sql($sql);
        if (sqlrows($res)) return false;

        $sql = "SELECT MID FROM deans WHERE MID ='".(int) $mid."' LIMIT 1";
        $res = sql($sql);
        if (sqlrows($res)) return false;

        $sql = "SELECT MID FROM admins WHERE MID ='".(int) $mid."' LIMIT 1";
        $res = sql($sql);
        if (sqlrows($res)) return false;

        return true;
    }

}

class CMSUser extends User {

    function isCourseReviewer($mid, $cid) {
        $sql = "SELECT mid FROM reviewers WHERE mid = '".(int) $mid."' AND cid = '".(int) $cid."'";
        $res = sql($sql);

        return sqlrows($res);
    }

    function isReviewer($mid) {
        $sql = "SELECT mid FROM reviewers WHERE mid = '".(int) $mid."'";
        $res = sql($sql);

        return sqlrows($res);
    }

    function isDeveloper($mid) {
        $sql = "SELECT mid FROM developers WHERE mid = '".(int) $mid."'";
        $res = sql($sql);

        return sqlrows($res);
    }

    function isMethodologist($mid) {
        $sql = "SELECT mid FROM methodologist WHERE mid = '".(int) $mid."'";
        $res = sql($sql);

        return sqlrows($res);
    }

    function isOnlyCMSUser($mid) {
        $sql = "SELECT MID FROM Students WHERE MID ='".(int) $mid."' LIMIT 1";
        $res = sql($sql);
        if (sqlrows($res)) return false;

        $sql = "SELECT MID FROM claimants WHERE MID ='".(int) $mid."' LIMIT 1";
        $res = sql($sql);
        if (sqlrows($res)) return false;

        $sql = "SELECT MID FROM graduated WHERE MID ='".(int) $mid."' LIMIT 1";
        $res = sql($sql);
        if (sqlrows($res)) return false;

        $sql = "SELECT MID FROM Teachers WHERE MID ='".(int) $mid."' LIMIT 1";
        $res = sql($sql);
        if (sqlrows($res)) return false;

        $sql = "SELECT MID FROM deans WHERE MID ='".(int) $mid."' LIMIT 1";
        $res = sql($sql);
        if (sqlrows($res)) return false;

        $sql = "SELECT MID FROM admins WHERE MID ='".(int) $mid."' LIMIT 1";
        $res = sql($sql);
        if (sqlrows($res)) return false;

        return true;
    }
}

class Developer extends CMSUser {
    function getCoursesId($mid) {
        $ret = array();
        $sql = "SELECT DISTINCT cid FROM developers WHERE mid = '".(int) $mid."'";
        $res = sql($sql);

        while($row = sqlget($res)) {
            $ret[$row['cid']] = $row['cid'];
        }
        return $ret;
    }
}

class Reviewer extends CMSUser {
    function getCoursesId($mid) {
        $ret = array();
        $sql = "SELECT DISTINCT cid FROM reviewers WHERE mid = '".(int) $mid."'";
        $res = sql($sql);

        while($row = sqlget($res)) {
            $ret[$row['cid']] = $row['cid'];
        }
        return $ret;
    }
}

class User {
	var $id;
	var $profiles;
	var $profile_current;
	var $permissions_temporary;
	var $preferred_lang;
	var $name;

	function User(){}

    function isAdmin($mid) {
        $sql = "SELECT MID FROM admins WHERE MID = '".(int) $mid."'";
        $res = sql($sql);

        return sqlrows($res);
    }

	function isStudent($mid) {
	    $sql = "SELECT MID FROM Students WHERE MID = '".(int) $mid."'";
	    $res = sql($sql);

	    return sqlrows($res);
	}


    function isTeacher($mid) {
        $sql = "SELECT MID FROM Teachers WHERE MID ='".(int) $mid."'";
        $res = sql($sql);

        return sqlrows($res);
    }

	function isDean($mid) {
	    $sql = "SELECT MID FROM deans WHERE MID ='".(int) $mid."'";
	    $res = sql($sql);

	    return sqlrows($res);
	}

    function allowSwitch2LMS() {
        $ret = false;
        if ($_SESSION['s']['mid'] && $_SESSION['s']['login']) {
            if (User::isStudent($_SESSION['s']['mid']) || User::isTeacher($_SESSION['s']['mid']) || User::isDean($_SESSION['s']['mid']) || User::isAdmin($_SESSION['s']['mid'])) return true;
            }
        return false;
    }

	function allowSwitch2CMS() {
	    $ret = false;
	    if ($_SESSION['s']['mid'] && $_SESSION['s']['login']) {
	                if (CMSUser::isReviewer($_SESSION['s']['mid']) || CMSUser::isDeveloper($_SESSION['s']['mid']) || CMSUser::isMethodologist($_SESSION['s']['mid'])) {
	                   $ret = true;
	                }
	    }
	    return $ret;
	}

    function allowSwitch2AT() {
        $ret = false;
        if ($_SESSION['s']['mid'] && $_SESSION['s']['login']) {
            switch($_SESSION['s']['perm']) {
                case 1:
                case 2:
                case 3:
                case 4:
                    if (ATUser::isManager($_SESSION['s']['mid']) || ATUser::isBoss($_SESSION['s']['mid']) || ATUser::isSlave($_SESSION['s']['mid'])) {
                        return true;
                    }
                    break;
            }
        }
        return $ret;
    }

	function allowSwitch2SIS() {
    	if ($_SESSION['s']['mid'] && $_SESSION['s']['login']) {
    		$sql = "SELECT soid FROM structure_of_organ WHERE mid='".(int) $_SESSION['s']['mid']."'";
    		$res = sql($sql);
    		if (sqlrows($res)) return true;
    	}
    	return false;
	}


	function initialize($id){
        if ($id > 0) {
		    $query = "SELECT * FROM People Where MID = '{$id}'";
		    $res = sql($query);
		    if ($row = sqlget($res)) {
			    $this->id = $id;
			    $this->preferred_lang = $row['preferred_lang'];
			    $arr = array($row['FirstName'], $row['LastName'], $row['Login'], _("имя не задано"), 'stop');
			    while (empty($this->name)) $this->name = array_shift($arr);
			    $this->_setProfiles();
            }
		} else {
			$this->_setProfileGuest();
		}
		$this->_setPermissionsTemporary();
	}

	function alterLang(){
		if ($this->preferred_lang && ($GLOBALS['controller']->lang_controller->lang_current->id != $this->preferred_lang)) {
			header("Location: {$GLOBALS['sitepath']}?lang={$this->preferred_lang}");
			exit();
		}
	}

	function _setPermissionsTemporary(){
		$this->permissions_temporary = is_array($tmp = $GLOBALS['controller']->persistent_vars->get('permissions_temporary')) ? $tmp : array();
	}

	function isAuthorized(){
		return ($this->profile_current->name !== PROFILE_GUEST);
	}

	function _setProfileGuest(){
		$profile = new Profile();
		$profile->initialize(PROFILE_GUEST);
        $profile->setActions();
		$this->profile_current = $profile;
		$this->profiles[] = $profile;
	}

	function _setProfiles(){


        function _prepareManyProfiles($profiles){
            $profilesMain = array(/*PROFILE_SUPERVISOR, */PROFILE_EMPLOYEE, PROFILE_STUDENT, PROFILE_USER); //supervisor != enduser
            $arraySlice = array();

            foreach($profilesMain as $key => $prof){
                foreach($profiles as $prof2){
                    if($prof2->name == $prof){
                        $maxRole = $prof;
                        $sliceKey = $key + 1;
                        $arraySlice = array_slice($profilesMain, $sliceKey);
                        break 2;
                    }
                }
            }
            
            $skipRoles = array();
            foreach ($arraySlice as $skipRole) {
                $skipRoles[$skipRole] = $skipRole;
            }

            $skipRolesIsCurrent = false;
            foreach($profiles as $prof){
                if (isset($skipRoles[$prof->name])) {
                    if ($prof->current) {
                        $skipRolesIsCurrent = true;
                        break;
                    }
                }
            }
            
            $res = array();
            foreach($profiles as &$key){
                if(!in_array($key->name, $arraySlice)){
                    $res[] =  $key;
                }

                if($maxRole == $key->name){
                    $key->alias = $GLOBALS['profiles_basic_aliases'][PROFILE_ENDUSER];
                    if ($skipRolesIsCurrent) {
                        $key->current = true;
                    }
                }
            }
            return $res;
        }


		foreach ($GLOBALS['profiles_basic'] as $profile_basic_name){
			$profile = new Profile();
			$profile->initialize($profile_basic_name);
			if ($this->fitsProfile($profile)){
				 $profiles = $this->_getProfilesExtended($profile);
			} else {
				$profiles = array();                
			}

			while($profile = &array_shift($profiles)){
				if ($profile->current) {
					$profile->setActions();
					$this->profile_current = $profile;
				}
				$this->profiles[] = $profile;
			}
		}


    /*    foreach ($this->profiles as $key => $profile){
            if(in_array($profile->name, array(PROFILE_STUDENT, PROFILE_EMPLOYEE, PROFILE_SUPERVISOR, PROFILE_USER))){

            }
        }*/

        $this->profiles = _prepareManyProfiles($this->profiles);
        //pr($this->profiles);       exit;

		if (!count($this->profiles)){
			$this->_setProfileGuest();
		}
	}

	function &_getProfilesExtended(&$profile_basic){
		$return = array();
		$query = "
			SELECT permission_groups.pmid
			FROM
			  permission_groups
			  INNER JOIN permission2mid ON (permission_groups.pmid = permission2mid.pmid)
			WHERE
			  (permission_groups.`type` = '$profile_basic->name') AND
			  (permission2mid.`mid` = '{$this->id}') AND
			  (permission_groups.`application` = '".APPLICATION_ROLE_ALIAS."')
		";
		$res = sql($query);
		while ($row = sqlget($res)) {
			$profile_extended = new Profile();
			$profile_extended->initialize(PREFIX_PROFILE . $row['pmid']);
			$return[] = $profile_extended;
		}
		return (count($return)) ? $return : array($profile_basic);
	}

	function fitsProfile(&$profile_basic){
		switch ($profile_basic->name) {
            case PROFILE_GUEST:
            case PROFILE_ENDUSER:
                return false;
            //@TODO add table and test
            case PROFILE_USER:
                return true; // с учетом последующего объединения в enduser - эта роль должна быть у всех
                $query = "
                    SELECT People.MID
                    FROM
                      People
                      LEFT JOIN supervisors ON (People.`MID` = supervisors.user_id)
					  LEFT JOIN Students ON (People.`MID` = Students.`MID`)
					  LEFT JOIN Teachers ON (People.`MID` = Teachers.`MID`)
					  LEFT JOIN deans ON (People.`MID` = deans.`MID`)
					  LEFT JOIN developers ON (People.`MID` = developers.`mid`)
					  LEFT JOIN managers ON (People.`MID` = managers.`mid`)
					  LEFT JOIN admins ON (People.`MID` = admins.`MID`)
                    WHERE
                      (People.`MID` = '{$this->id}') AND supervisors.user_id IS NULL AND Students.MID IS NULL AND
                      Teachers.MID IS NULL AND deans.MID IS NULL AND developers.mid IS NULL
                      AND managers.mid IS NULL AND admins.MID IS NULL
                ";
                break;
            case PROFILE_SUPERVISOR:
                $query = "
                    SELECT People.MID
                    FROM
                      People
                      INNER JOIN supervisors ON (People.`MID` = supervisors.user_id)
                    WHERE
                      (People.`MID` = '{$this->id}')
                ";
                break;
            case PROFILE_EMPLOYEE:
                $query = "
                        SELECT People.MID
                        FROM
                          People
                          INNER JOIN employee ON (People.`MID` = employee.user_id)
                        WHERE
                          (People.`MID` = '{$this->id}')
                    ";
                break;
			case PROFILE_STUDENT:
				$query = "
					SELECT People.MID
					FROM
					  People
					  INNER JOIN Students ON (People.`MID` = Students.`MID`)
					WHERE
					  (People.`MID` = '{$this->id}')
				";
				break;
			case PROFILE_TEACHER:
				$query = "
					SELECT People.MID
					FROM
					  People
					  INNER JOIN Teachers ON (People.`MID` = Teachers.`MID`)
					WHERE
					  (People.`MID` = '{$this->id}')
				";
				break;
			case PROFILE_DEAN:
				$query = "
					SELECT People.MID
					FROM
					  People
					  INNER JOIN deans ON (People.`MID` = deans.`MID`)
					WHERE
					  (People.`MID` = '{$this->id}')
				";
				break;
            case PROFILE_TUTOR:
                $query = "
					SELECT People.MID
					FROM
					  People
					  INNER JOIN Tutors ON (People.`MID` = Tutors.`MID`)
					WHERE
					  (People.`MID` = '{$this->id}')
				";
                break;
            case PROFILE_DEVELOPER:
                $query = "
					SELECT People.MID
					FROM
					  People
					  INNER JOIN developers ON (People.`MID` = developers.`mid`)
					WHERE
					  (People.`MID` = '{$this->id}')
				";
                break;
            case PROFILE_MANAGER:
                $query = "
					SELECT People.MID
					FROM
					  People
					  INNER JOIN managers ON (People.`MID` = managers.`mid`)
					WHERE
					  (People.`MID` = '{$this->id}')
				";
                break;            
			case PROFILE_ADMIN:
				$query = "
					SELECT People.MID
					FROM
					  People
					  INNER JOIN admins ON (People.`MID` = admins.`MID`)
					WHERE
					  (People.`MID` = '{$this->id}')
				";
				break;
		}
		$res = sql($query);
		return sqlrows($res);
	}

	function getCourses(){
		$return = array();
		switch ($this->profile_current->basic_name) {
			case PROFILE_STUDENT:
				$tmstamp = time();
				$query = "
					SELECT
					  subjects.subid as CID, subjects.name AS Title
					FROM
					  subjects
					  INNER JOIN Students ON (subjects.subid = Students.CID)
					  INNER JOIN People ON (Students.`MID` =People.`MID`)
					WHERE
					  (People.`MID` = '{$this->id}') AND
					  (
						  (UNIX_TIMESTAMP(subjects.begin) <= {$tmstamp} AND UNIX_TIMESTAMP(subjects.end) >= {$tmstamp})
					  )
					ORDER BY subjects.name
				";
				break;
			case PROFILE_TEACHER:
				$query = "
					SELECT
					  subjects.subid as CID, subjects.name AS Title, 0 AS locked
					FROM
					  subjects
					  INNER JOIN Teachers ON (subjects.subid = Teachers.CID)
					  INNER JOIN People ON (Teachers.`MID` = People.`MID`)
					WHERE
					  (People.`MID` = '{$this->id}')
				    ORDER BY subjects.name
				";
				break;
			default:
				$query = "
					SELECT * FROM People WHERE MID='0'
				";
				break;
		}
		$res = sql($query);
		while($row = sqlget($res)){
			$course = new Course();
			$course->initialize($row);
			$return[] = $course;
		}
		return $return;
	}

    function getCoursesAll(){
        $return = array();
        $query = "
            SELECT * FROM Courses ORDER BY Title
        ";
        $res = sql($query);
        while($row = sqlget($res)){
            $course = new Course();
            $course->initialize($row);
            $return[] = $course;
        }
        return $return;
    }
}



class Course {

	var $id;
	var $title;
	var $locked;

	function initialize($arr){
		$this->id = $arr['CID'];
		$this->title = $arr['Title'];
		$this->locked = $arr['locked'];
	}
}

class Profile {

	var $name;
	var $alias;
	var $basic_id;
	var $basic_name;
	var $current;
	var $extended;
	var $actions;

	function Profile(){
	}

	function initialize($name){
		$this->name = $name;
		$this->_setExtended();
		$this->_setBasicName();
		$this->_setBasicId();
		$this->_setAlias();
		$this->_setCurrent();
	}

	function _setExtended(){
		if (strpos($this->name, PREFIX_PROFILE) !== false){
			$id = str_replace(PREFIX_PROFILE, '', $this->name);
			$query = "SELECT * FROM permission_groups WHERE pmid = '{$id}'";
			$res = sql($query);
			if ($row = sqlget($res)) $this->extended = $row;
		}
	}

	function _setBasicName(){
		if ($this->extended){
			$this->basic_name = $this->extended['type'];
		} else {
			if (in_array($this->name, $GLOBALS['profiles_basic'])) $this->basic_name = $this->name;
		}
	}

	function _setAlias(){
		if ($this->extended){
			$this->alias = $this->extended['name'];
		} else {
			if (array_key_exists($this->name, $GLOBALS['profiles_basic_aliases'])) $this->alias = $GLOBALS['profiles_basic_aliases'][$this->name];
		}
	}

	function _setBasicId(){
		$this->basic_id = $GLOBALS['profiles_basic_ids'][$this->basic_name];
	}

	function _setCurrent(){
		if ($profile_name = $GLOBALS['controller']->persistent_vars->get('profile_current')){
			$this->current = ($profile_name == $this->name);
		} else {
			$this->current = ($_SESSION['s']['perm'] == $this->basic_id);
		}
	}

	function setActions(){
		if ($this->name == $this->basic_name) {
            if (isset($GLOBALS['profiles_inheritance'][$this->name])) {
                $this->actions = ActionsUtil::getActionsBasic(array_merge(array($this->name), $GLOBALS['profiles_inheritance'][$this->name]));
            } else {
		    	$this->actions = ActionsUtil::getActionsBasic($this->name);
            }
		} else {
			$this->actions = ActionsUtil::getActionsExtended($this->extended['pmid']);
		}
	}
}

class Action  {

	var $id;
	var $type;
	var $name;
	var $icon;

	function initialize($arr){
		list($this->id, $this->type, $this->name, $this->icon) = $arr;
	}
}

?>