<?php
// Connect Pro XML Api Adapter
class ConnectProXMLApiAdapter
{
	const HOST             = 'http://connectpro.hypermethod.com';
	const ADMIN_LOGIN      = "dimak@hypermethod.ru";
	const ADMIN_PASSWORD   = 'please';
	const COOKIE           = 'BREEZESESSION';
	const DEFAULT_PASSWORD = 'pass';
	
	protected $baseUrl;
	protected $login;
	protected $password;
	protected $breezesession;
	protected $cookies = array();
	
	public function __construct($baseUrl, $login, $password, $breezesession = null)
	{
		$this->baseUrl = $baseUrl;
		$this->login = $login;
		$this->password = $password;
		$this->breezesession = $breezesession;
	}
	
	protected function _breezeUrl($action, $queryString = null)
	{
		return $this->baseUrl . '/api/xml?' . 'action=' . $action . ($queryString !== null ? '&' . $queryString : '');
	}
	
	protected function _send($breezeUrl)
	{
		$curl = new cURL();
		$return = $curl->get($breezeUrl);
		$this->cookies = $curl->getCookies();
		return $return;
	}
	
	protected function _login()
	{
		if ($this->breezesession != null) {
			$this->_logout();
		}
		
		$loginUrl = $this->_breezeUrl('login', "login=".$this->login."&password=".$this->password);
		
		$response = $this->_send($loginUrl);
		
		if (isset($this->cookies[ConnectProXMLApiAdapter::COOKIE])) {
			$this->breezesession = $this->cookies[ConnectProXMLApiAdapter::COOKIE];
		}
		if (!strlen($response)) throw new Exception(_('Ответ от сервера Acrobat Connect Pro не был получен'));
		
		try {
		    $xml = new SimpleXMLElement($response);
		    if ((strtolower($xml->status['code']) != 'ok') || (null == $this->breezesession)) {
                throw new Exception(_('Невозможно залогиниться в Acrobat Connect Pro'));
            }       		    
		} catch(Exception $e) {
			throw new Exception(_('Ошибка разбора ответа Acrobat Connect Pro'));
		}
		
	}
	
	protected function _request($action, $queryString = null)
	{
	    if ($this->breezesession == null) $this->_login();
	    
	    $url = $this->_breezeUrl($action, $queryString);

	    $response = $this->_send($url);
	    
	    if (!strlen($response)) throw new Exception(_('Ответ от сервера Acrobat Connect Pro не был получен'));

	    $xml = new SimpleXMLElement($response);
	    
	    if (strtolower($xml->status['code']) != 'ok') {
	    	throw new Exception($xml->status->asXml());
	    }
	    
	    return $xml;
    }
		
	public function getShortcuts()
	{
		$list = array();
		$xml = $this->_request('sco-shortcuts');
		if ($xml) {
			foreach($xml->shortcuts->sco as $sco) {
				$element = new stdClass();
				$element->tree_id = (string) $sco['tree-id'];
				$element->sco_id  = (string) $sco['sco-id'];
				$element->type    = (string) $sco['type'];
				
				$list[] = $element;
			}
		}
		
		return $list;
	}
	
	public function getShortcut($type)
	{
		$list = $this->getShortcuts();
		if (is_array($list) && count($list)) {
			foreach($list as $item) {
				if ($item->type == $type) return $item;
			}
		}
	}
	
	public function _date($date)
	{
		return str_replace(' ', 'T', $date); //('Y-m-dTH:i', strtotime($date));
	}
	
	public function getTemplates($sco_id)
	{
		$list = array();
		if ($sco_id) {
			$xml = $this->_request('sco-contents', 'sco-id='.$sco_id);
			if ($xml) {
				foreach($xml->scos->sco as $e) {
					$item = new stdClass();
					$item->sco_id = (string) $e['sco-id'];
                    $item->source_sco_id = (string) $e['source-sco-id'];
                    $item->folder_id = (string) $e['folder-id'];
                    $item->type   = (string) $e['type'];
                    $item->name   = (string) iconv('UTF-8', $GLOBALS['controller']->lang_controller->lang_current->encoding, $e->name);
                    $item->url_path   = (string) $e->url-path;                    
					
					$list[] = $item;
				}
			}
		}
		
		return $list;
	}
	
	public function getTemplatesList()
	{
		$list = array();
		$shortcut = $this->getShortcut('shared-meeting-templates');
		
		if ($shortcut && $shortcut->sco_id) {
			$list = $this->getTemplates($shortcut->sco_id);
		}
		
		return $list;
	}
			
	
	public function getPrincipals($filter = null)
	{
		$list = array();
		$xml = $this->_request('principal-list', $filter);
		if ($xml) {
			foreach($xml->{'principal-list'}->principal as $e) {
				$item = new stdClass();
				$item->name = (string) $e->name;
				$item->login = (string) $e->login;
				$item->description = (string) $e->description;
				$item->principal_id = (string) $e['principal-id'];
				$item->account_id = (string) $e['account-id'];
				$item->type = (string) $e['type'];
				$item->has_children = (boolean) $e['has-children'];
                $item->is_primary = (boolean) $e['is-primary'];
                $item->is_hidden = (boolean) $e['is-hidden'];
                $list[] = $item;
			}
		}		
		
		return $list;
	}
	
	public function isLoginExists($login)
	{
		$list = $this->getPrincipals('filter-login='.$login);
		return count($list);
	}

	public function getUserInfo($login)
	{
		$list = $this->getPrincipals('filter-login='.$login);
		if (count($list)) {
			return $list[0];
		}
	}
	
	public function getUserId($login)
	{
		if ($info = $this->getUserInfo($login)) {
			return $info->principal_id;
		}
	}
	
	public function getLogins() 
	{
		$ret = array();
		$list = $this->getPrincipals();
		if (count($list)) {
			foreach($list as $item) {
				$ret[$item->login] = $item;
			}
		}
		
		return $ret;
	}
	
	protected function _prepareParam($value)
	{
		return urlencode(iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, 'UTF-8', $value));
	}
	
	protected function _prepareParams($params) 
	{
        if (is_array($params) && count($params)) {
            foreach($params as $key => $value) {
                $params[$key] = $this->_prepareParam($value);
            }
        }
		
        return $params;
	}
	
	public function addUser($params)
	{
		
		if (!strlen($params['firstname'])) {
            $params['firstname'] = _('нет');
        }

        if (!strlen($params['lastname'])) {
            $params['lastname'] = _('нет');
        }
		
		$params['firstname'] = $this->_prepareParam($params['firstname']);
        $params['lastname']  = $this->_prepareParam($params['lastname']);
        $params['login']     = $this->_prepareParam($params['login']);
        $params['email']     = $this->_prepareParam($params['email']);
                
		$xml = $this->_request(
		    'principal-update',
		    "first-name=".$params['firstname']."&last-name=".$params['lastname']."&login=".$params['login']."&password={$params['password']}&type=user&send-email=true&has-children=0&email={$params['email']}"
		);
		
		if ($xml) {
			return (string) $xml->principal['principal-id'];
		}
		
		return false;
	}
	
	/**
	 * params - array: begin, end, title, id
	 * @param array $params
	 * @return boolean
	 */
	public function addMeeting($params)
	{
		
        if (!strlen($params['title'])) $params['title'] = _('Без названия');
		
		$params['title'] = $this->_prepareParam($params['title']);
		
		// todo: is live_admin?
		
		$sco = $this->getShortcut('meetings');
		if ($sco && $sco->sco_id) {
			$xml = $this->_request(
			    'sco-update', 
			    "type=meeting&name=".$params['title']."&folder-id={$sco->sco_id}&date-begin=".$this->_date($params['begin'])."&date-end=".$this->_date($params['end'])."&url-path=meet".$params['id'].((isset($params['source-sco-id']) && $params['source-sco-id']) ? '&source-sco-id='.$params['source-sco-id'] : '')
			);
			
			if ($xml) {
				if (strtolower($xml->status['code']) == 'ok') {
					
					$sco_id = (string) $xml->sco['sco-id'];
					if ($sco_id) {
						$xml = $this->_request(
						    'permissions-update',
						    "acl-id={$sco_id}&principal-id=public-access&permission-id=denied"
						);
						
	                    if (strtolower($xml->status['code']) == 'ok') {
	                    	return $sco_id;
	                    }
					}
					
				}				
			}
						
		}
		
		return false;
	}

	public function updateMeeting($sco_id, $params)
	{
		if (!strlen($params['title'])) $params['title'] = _('Без названия');
        
        $params['title'] = $this->_prepareParam($params['title']);
        
        // todo: is live_admin?
        if ($sco_id) {
        	$xml = $this->_request(
                'sco-update', 
                "type=meeting&name=".$params['title']."&sco-id={$sco_id}&date-begin=".$this->_date($params['begin'])."&date-end=".$this->_date($params['end'])."&url-path=meet".$params['id'].((isset($params['source-sco-id']) && $params['source-sco-id']) ? '&source-sco-id='.$params['source-sco-id'] : '')
            );
            
            if ($xml) return true;
        }
        
        return false;
	}
	
	public function getMeetingTemplate($sco_id) 
	{
		if ($sco_id) {
			$xml = $this->_request('sco-info', "sco-id=".$sco_id);
			if ($xml) {
				if (isset($xml->sco['source-sco-id'])) {
					return (string) $xml->sco['source-sco-id'];
				}
			}
		}
	}
	
	protected function _permissionsUpdate($sco_id, $principal_id, $permission_id)
	{
        $xml = $this->_request(
            'permissions-update',
            "principal-id={$principal_id}&acl-id={$sco_id}&permission-id={$permission_id}"
        );
        if ($xml) {
            return true;
        } 
        
        return false;
		
	}
	
	public function addMeetingUser($sco_id, $principal_id, $permission_id = 'view') 
	{
		return $this->_permissionsUpdate($sco_id, $principal_id, $permission_id);
	}
	
	public function removeMeetingUser($sco_id, $principal_id)
	{
        return $this->_permissionsUpdate($sco_id, $principal_id, 'remove');
	}
	
	public function addMeetingUserList($sco_id, $ids, $permission_id = 'view')
	{
		if (is_array($ids) && count($ids)) {
			array_walk($ids, 'intval');
			
			// Получаем всю инфу
			$logins = $this->getLogins();
			
			$sql = "SELECT * FROM People WHERE MID IN ('".join("','", $ids)."')";
			$res = sql($sql);
			while($row = sqlget($res)) {
				if (!isset($logins[$row['Login']])) {
					$principal_id = $this->addUser(array('lastname' => $row['LastName'], 'firstname' => $row['FirstName'], 'email' => $row['EMail'], 'login' => $row['Login'], 'password' => CONNECT_PRO_DEFAULT_PASSWORD));
				} else {
					$principal_id = $logins[$row['Login']]->principal_id;
				}
				
				if (!$principal_id) {
					throw new Exception(_('Невозможно назначить занятие пользователю'));
				}
				
				$this->addMeetingUser($sco_id, $principal_id, $permission_id);
				
			}
		}
	}

    public function removeMeetingUserList($sco_id, $ids)
    {
        if (is_array($ids) && count($ids)) {
            array_walk($ids, 'intval');
            
            // Получаем всю инфу
            $logins = $this->getLogins();
            
            $sql = "SELECT * FROM People WHERE MID IN ('".join("','", $ids)."')";
            $res = sql($sql);
            while($row = sqlget($res)) {
                if (isset($logins[$row['Login']])) {
                    $principal_id = $logins[$row['Login']]->principal_id;
                    $this->removeMeetingUser($sco_id, $principal_id);                    
                }                                                
            }
        }
    }
    
    public function deleteMeeting($sco_id) 
    {
    	$xml = $this->_request('sco-delete', "sco-id=".$sco_id);
    	if ($xml) {
    		return true;
    	}
    	return false;
    }
	
	protected function _logout()
	{
		$this->_request('logout');
	}
	
	public function enter($id) {
        //@fopen($this->_breezeUrl('login', "login=".$this->login."&password=".$this->password));
        //setcookie(self::COOKIE, $this->breezesession, 0, '/', str_replace('http://', '', self::HOST));
        //var_dump('Set-Cookie: '.self::COOKIE.'='.$this->breezesession.';domain='.str_replace('http://', '', self::HOST).';path=/');
        //die();
        //header('Set-Cookie: '.self::COOKIE.'='.$this->breezesession.';domain='.str_replace('http://', '', self::HOST).';path=/');
		
		$this->_login();
        header('Location: '.CONNECT_PRO_HOST.'/meet'.$id.'/?session='.$this->breezesession);
		exit();
	}
	
}
?>