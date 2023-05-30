<?php
class HM_User_UserService extends HM_Service_Abstract
{

    const NEW_LOGIN_PREFIX = 'user_';

    const COOKIE_NAME_LANG = 'hmlang';

    const DEFAULT_LANG = 'rus';

	protected $_tutorList 		= array(); //--ключ - MID, значение ФИО. использцется в разделе "Сессии" и "Учебные курсы".
	protected $_tutorGroupList 	= array(); //-- CID -> GID -> MID,. использцется в разделе "Сессии" и "Учебные курсы".
	protected $_teacherList 	= array(); //--ключ - MID, значение ФИО. использцется в разделе "Сессии" и "Учебные курсы".
	const CACHE_NAME = 'HM_User_UserService';
	
    public function getCurrentUser() {   
		
        try {
            /* один черт при вызове из анменеджда сервисных методов
            при любом вызове UserService->getCurrentUserЧто-Нибудь() все обернуто  в трайкетчи
            лучше уж тут тогда поставить а не плодить их везде где поНядобятся*/
            $currentUser = Library::getAuth('default')->getIdentity();

        } catch (Zend_Session_Exception $e) {   
            //Zend_Auth::getInstance()->setStorage($_SESSION['default']['storage']);
            $currentUser = $this->getById($GLOBALS['s']['mid']);
        }
		
        return $currentUser;
    }

    /**
     * Создание простой ссылки для "быстрой" авторизации пользователя на сайте
     *
     * @param (int)    $user_id      - идентификатор пользователя
     * @param (string) $url          - ссылка на страницу, куда должен перейти пользователь после авторизации
     * @param (int)    $valid_before - метка времени timestamp, до которой пользователь может авторизоваться по ссылке
     */
    public function createSimpleAuthLink($user_id, $url, $valid_before = null)
    {
        $guid = strtolower(preg_replace('/[\{\}\-]/', '', com_create_guid()));

        if ($valid_before === null) {
            $valid_before = mktime() + 60*60*24; // действительно в течении 24 часов
        }

        $db = $this->getMapper()->getTable()->getAdapter();
        $result = $db->insert('simple_auth', array(
            'user_id'      => $user_id,
            'auth_key'     => $guid,
            'link'         => $url,
            'valid_before' => date('Y-m-d H:i:s', $valid_before)
        ));

        if ($result) {
            return 'http://'.$_SERVER['HTTP_HOST'].'/index/simple-authorization/key/'.$guid;
        }

        return false;
    }

    /**
     * Используется при авторизации через ссылку, полученную методом createSimpleAuthLink
     *
     * @param $key
     */
    public function authorizeSimple($key)
    {
        $select = $this->getSelect();
        $select->from('simple_auth', array(
            'user_id',
            'link',
            'valid_before'
        ));
        $select->where('auth_key = ?', $key);

        $result = $select->query()->fetch();

        if ($result) {
            $user_id      = $result['user_id'];
            $valid_before = DateTime::createFromFormat('Y-m-d H:i:s', $result['valid_before']);
            $valid_before = $valid_before->getTimestamp();

            if ($valid_before > mktime()) {

                $user = $this->getOne($this->find($user_id));

                if ($user && !$user->blocked) {

                    $this->_initRole($user);
                    $this->_initUnmanaged($user);
                    $this->_init($user);

                    header('Location: '. $result['link']);
                    die;
                }

            }
        }

        header('Location: /');
        die;

    }

    public function getCurrentUserRole($withUnion = false)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return HM_Role_RoleModelAbstract::ROLE_GUEST;
        } elseif ($withUnion && $this->getService('Acl')->inheritsRole($user->role, HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
            return HM_Role_RoleModelAbstract::ROLE_ENDUSER;
        }
        return $user->role;
    }

    public function getCurrentUserId()
    {
        try {
            // не работает из unmanaged
        $user = $this->getCurrentUser();
            $userId = $user->MID;

        } catch (Zend_Session_Exception $e) {
            $userId = $GLOBALS['s']['mid'];
    }

        return $userId;
    }

    public function getCurrentLang()
    {
        $languages = Zend_Registry::get('config')->languages->toArray();

        if ($this->getCurrentUserId() > 0) {
            $user = $this->getCurrentUser();
            $lang = $user->lang;

            if (isset($languages[$lang])) {
                return $languages[$lang];
            }
        }

        if (isset($_COOKIE[self::COOKIE_NAME_LANG])) {
            $lang = $_COOKIE[self::COOKIE_NAME_LANG];
            if (isset($languages[$lang])) {
                return $languages[$lang];
            }
        }

        $accepted = Zend_Locale::getBrowser();
        if (is_array($accepted) && count($accepted)) {
            foreach($accepted as $locale => $weight) {
                foreach($languages as $lang) {
                    if (strtolower($locale) == strtolower($lang['locale'])) {
                        return $lang;
                    }
                }
            }
        }

        return false;

    }

    public function getCurrentLangId()
    {
        $lang = $this->getCurrentLang();

        if ($lang) {
            $lang = $lang['id'];
        } else {
            $lang = self::DEFAULT_LANG;
        }

        return $lang;
    }


    public function assignRole($userId, $role)
    {
        $result = true;
        $roleNames = array();
        $roles = HM_Role_RoleModelAbstract::getBasicRoles(false);
        $userId = intval($userId);

        if ( $userId <= 0 ) {
            return false;
        }

        $ar_roles = (is_array($role))? $role : (array) $role;

        # снятие с пользователя ненужных ролей (не переданных в запросе)
        /*foreach ( $roles as $k => $v) {
            if ( $this->isRoleExists($userId, $k) && !in_array($k, $ar_roles)) {
                $this->removalRole($userId, $k);
            }
        }*/

        foreach ( $ar_roles as $role) {

            if(!isset($roles[$role])){
                continue;
            }

            //Так как таблицы все разные, то придется делать свич и там создавать массив на добавление
            switch($role){
                case HM_Role_RoleModelAbstract::ROLE_SUPERVISOR:
                    $find = $this->getService('Supervisor')->find($userId);
                    if (count($find) > 0) {
                        $result = false;
                        break;
                    }

                    $this->getService('Supervisor')->insert(array('user_id' => $userId));
                    break;
                case 'student':

                    $find = $this->getService('Student')->fetchAll(array('MID = ?' => $userId));

                    if(count($find) > 0){
                        $result = false;
                        break;
                    }

                    $this->getService('Student')->insert(
                        array('MID' => $userId,
                              'CID' => 0)
                    );

                break;
                case 'teacher':

                    $find = $this->getService('Teacher')->fetchAll(array('MID = ?' => $userId));

                    if(count($find) > 0){
                        $result = false;
                        break;
                    }

                    $this->getService('Teacher')->insert(
                        array('MID' => $userId,
                              'CID' => 0)
                    );					
					$this->setDefaultNotifies($userId);

                break;
                case 'tutor':

                    $find = $this->getService('Tutor')->fetchAll(array('MID = ?' => $userId));

                    if(count($find) > 0){
                        $result = false;
                        break;
                    }

                    $this->getService('Tutor')->insert(
                        array('MID' => $userId,
                            'CID' => 0)
                    );
					$this->setDefaultNotifies($userId);

                    break;
                case 'developer':
                     $find = $this->getService('Developer')->fetchAll(array('mid = ?' => $userId));

                    if(count($find) > 0){
                        $result = false;
                        break;
                    }

                    $this->getService('Developer')->insert(
                        array('mid' => $userId)
                    );


                break;
                case 'manager':
                    $find = $this->getService('Manager')->fetchAll(array('mid = ?' => $userId));
                    if(count($find) > 0){
                        $result = false;
                        break;
                    }
                    $this->getService('Manager')->insert(
                        array('mid' => $userId)
                    );

                break;
                case 'admin':
                    $find = $this->getService('Admin')->fetchAll(array('MID = ?' => $userId));

                    if(count($find) > 0){
                        $result = false;
                        break;
                    }

                    $this->getService('Admin')->insert(
                        array('MID' => $userId)
                    );

                break;
                case 'dean':
                    $find = $this->getService('Dean')->fetchAll(array('MID = ?' => $userId));

                    if(count($find) > 0){
                        $result = false;
                        break;
                    }

                    $this->getService('Dean')->insert(
                        array('MID' => $userId)
                    );

                break;
            }

            if (!$this->getService('Acl')->inheritsRole($role, HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
                $roleNames[] = $roles[$role];
            }
        }

        if ( !$result ) {
            return false;
        }

        if (count($roleNames)) {
        $messenger = $this->getService('Messenger');
        $messenger->setOptions(
            HM_Messenger::TEMPLATE_ASSIGN_ROLE,
                array('role' => implode(', ', $roleNames), 'url_manual' => '')
        );
        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $userId);
        }

        return true;
    }



    /**
     *
     * Сохраняем изображение в папку
     * @param unknown_type $userId
     * @param unknown_type $postId
     * @return string|string|string
     */
    public function addPhoto($userId, $postId = 'photo'){
        $config = Zend_Registry::get('config');

        $upload = new Zend_File_Transfer();

        $upload->setAdapter('Http');
        $files = $upload->getFileInfo();
        //print_r($files);
        $photo = $files[$postId];

        if (!$upload->isUploaded()) {
            return false;
        }

        $image = getimagesize($photo['tmp_name']);
        $ext ='';
        if($image[2] && IMAGE_JPG){
            $ext ='jpg';
        }elseif($image[2] && IMAGE_PNG){
            $ext ='png';
        }elseif($image[2] && IMAGE_GIF){
            $ext ='gif';
        }else{
            return false;

        }
        $getpath = $this->getPath($config->path->upload->photo,$userId);

        if(!$upload->isValid()){
            return false;
        }

        $glob = glob($getpath . $userId .'.*');

        foreach($glob as $value){
            unlink($value);
        }

        $upload->receive();
        $img = PhpThumb_Factory::create($getpath . $userId . '.' .$ext);
        $img->resize(HM_User_UserModel::PHOTO_WIDTH, HM_User_UserModel::PHOTO_HEIGHT);
        $img->save($getpath . $userId . '.' .$ext);

        return true;
    }


    /**
     * Потом вынести функцию в общий класс, который будет
     * выдавать каталог
     *
     *
     */
    public function getPath($filePath, $id){
        $config = Zend_Registry::get('config');
        $filePath = realpath($filePath);

        if(!is_dir($filePath)){
            return false;
        }
        $maxFilesCount = (int) $config->path->upload->maxfilescount;
        $path = floor($id / $maxFilesCount);
        if(!is_dir($filePath . DIRECTORY_SEPARATOR . $path)){
            $old_umask = umask(0);
            mkdir($filePath . DIRECTORY_SEPARATOR . $path, 0777);
            chmod($filePath . DIRECTORY_SEPARATOR . $path, 0777);
            umask($old_umask);
        }
        return  $filePath . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR;
    }


    public function getImageSrc($userId){
        $config = Zend_Registry::get('config');
        $getpath = $this->getPath($config->path->upload->photo, $userId);
        $maxFilesCount = (int) $config->path->upload->maxfilescount;
        $glob = glob($getpath . $userId .'.*');
        foreach($glob as $value){
            return floor($userId / $maxFilesCount) . '/' . basename($value);
        }
        return false;

    }

    public function getRandomString($maxLength = null)
    {
        $passwordOptions = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_PASSWORDS);
        if($maxLength == null){
            $maxLength = $passwordOptions['passwordMinLength'] > 0 ? $passwordOptions['passwordMinLength'] : HM_User_UserModel::PASSWORD_LENGTH;
        }
        $str ='';
        if($passwordOptions['passwordCheckDifficult'] != 1){
            $array = array_merge(range('a','z'), range('A','Z'), range('0','9'));
            $amount = count($array)-1;
            for($i = 0; $i < $maxLength; $i++){
                $str.=$array[mt_rand(0, $amount)];
            }
        }else{
            $alpha = range('a','z'); //array_merge(range('a','z'), range('а', 'я'), array('ё'));
            $alphaBig = range('A','Z'); //array_merge(range('A','Z'), range('А', 'Я'), array('Ё'));            
            $numeric = range(0, 9);
            $symbol = array('$', '#', '!', '%');

            $result = array();

            for($i = 0; $i <= $maxLength; $i++){
                $arr = array();
                if($i % 4 == 0){
                    $arr = $symbol;
                }elseif($i % 4 == 1){
                    $arr = $numeric;
                }elseif($i % 4 == 2){
                    $arr = $alphaBig;
                }elseif($i % 4 == 3){
                    $arr = $alpha;
                }
                $result[] = $arr[array_rand($arr)];
            }

            shuffle($result);
            $str = implode('', $result);

        }
        return $str;
    }

    private function _initRole($user)
    {
        $role = HM_Role_RoleModelAbstract::ROLE_GUEST;

        if ($this->isRoleExists($user->MID, HM_Role_RoleModelAbstract::ROLE_USER)) {
            $role = HM_Role_RoleModelAbstract::ROLE_USER;
        }

        if ($this->isRoleExists($user->MID, HM_Role_RoleModelAbstract::ROLE_SUPERVISOR)) {
            $role = HM_Role_RoleModelAbstract::ROLE_SUPERVISOR;
        }

        if ($this->isRoleExists($user->MID, HM_Role_RoleModelAbstract::ROLE_STUDENT)) {
            $role = HM_Role_RoleModelAbstract::ROLE_STUDENT;
        }

        if ($this->isRoleExists($user->MID, HM_Role_RoleModelAbstract::ROLE_TEACHER)) {
            $role = HM_Role_RoleModelAbstract::ROLE_TEACHER;
        }

        if ($this->isRoleExists($user->MID, HM_Role_RoleModelAbstract::ROLE_DEAN)) {
            $role = HM_Role_RoleModelAbstract::ROLE_DEAN;
        }

        if ($this->isRoleExists($user->MID, HM_Role_RoleModelAbstract::ROLE_DEVELOPER)) {
            $role = HM_Role_RoleModelAbstract::ROLE_DEVELOPER;
        }

        if ($this->isRoleExists($user->MID, HM_Role_RoleModelAbstract::ROLE_MANAGER)) {
            $role = HM_Role_RoleModelAbstract::ROLE_MANAGER;
        }

        if ($this->isRoleExists($user->MID, HM_Role_RoleModelAbstract::ROLE_ADMIN)) {
            $role = HM_Role_RoleModelAbstract::ROLE_ADMIN;

            $this->getService('Log')->log(
                $this->getCurrentUserId(),
                'Admin login',
                'Success',
                Zend_Log::NOTICE
            );
        }

        $user->role = $role;

        if ($role !== HM_Role_RoleModelAbstract::ROLE_GUEST) {
            $this->getService('Guest')->setNotGuest();
        }

        return $role;
    }

    public function initUserIdentity($user)
    {
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session('default'));
        $auth->getStorage()->write($user);
    }

    private function _init($user)
    {
        $this->getService('Captcha')->delete($user->Login);
        $this->getService('Captcha')->purge();

        $user->invalid_login = 0;
        $user->countlogin++;
        $this->update($user->getValues(null, array('role', 'Password', 'email_backup')));

        $this->initUserIdentity($user);

        Zend_View_Helper_Navigation_HelperAbstract::setDefaultRole($user->role);

        if ($user->lang != $this->getCurrentLangId()) {
            $user = $this->update(array('lang' => $this->getCurrentLangId(), 'MID' => $user->MID));
        }

        setcookie('hmexit', 'true', time() - 3600, '/');
        setcookie(HM_User_UserService::COOKIE_NAME_LANG, $user->lang, 0, '/');
    }

    private function _initUnmanaged($user, $systemInfo = false)
    {
        $s = new Zend_Session_Namespace('s');
        $s->mid   = $user->MID;
        $s->login = $user->Login;
        $s->perm  = $this->getService('Unmanaged')->getPermission($user->role);
        $s->user['lname'] = $user->LastName;
        $s->user['fname'] = $user->FirstName;
        $s->user['patronymic'] = $user->Patronymic;
		$s->user['group'] = $this->getService('StudyGroupUsers')->getUserGroupName($user->MID);

        $sessionData = array(
            'mid'   => $user->MID,
            'start' => $this->getDateTime(),
            'stop'  => $this->getDateTime(),
            'ip'    => $_SERVER["REMOTE_ADDR"]
        );

        // если была передана информация о системе, то сохраняем её
        if ($systemInfo) {
            $systemData = array(
                'browser_name'           => !empty($systemInfo['browser']['name']) ? $systemInfo['browser']['name'] : null,
                'browser_version'        => !empty($systemInfo['browser']['value']) ? $systemInfo['browser']['value'] : null,
                'flash_version'          => !empty($systemInfo['flash']['value']) ? $systemInfo['flash']['value'] : null,
                'os'                     => !empty($systemInfo['os']['value']) ? $systemInfo['os']['value'] : null,
                'screen'                 => !empty($systemInfo['screen']['value']) ? $systemInfo['screen']['value'] : null,
                'cookie'                 => (int) !empty($systemInfo['cookie']['value']),
                'js'                     => (int) !empty($systemInfo['js']['value']),
                'java_version'           => !empty($systemInfo['java']['value']) ? $systemInfo['java']['value'] : null,
                'silverlight_version'    => !empty($systemInfo['silverlight']['value']) ? $systemInfo['silverlight']['value'] : null,
                'acrobat_reader_version' => !empty($systemInfo['acrobat_reader']['value']) ? $systemInfo['acrobat_reader']['value'] : null,
                'msxml_version'          => !empty($systemInfo['msxml']['value']) ? $systemInfo['msxml']['value'] : null
            );
            $sessionData = array_merge($sessionData, $systemData);
        }

        $session = $this->getService('Session')->insert($sessionData);

        $s->sessid = $session->sessid;

    }

    public function authorizeByKey($key)
    {
        $session = $this->getOne(
            $this->getService('Session')->fetchAll(
                $this->quoteInto(
                    'sesskey = ?', $key
                )
            )
        );

        if ($session && ($session->ip ==  $_SERVER["REMOTE_ADDR"])) {
            $user = $this->getOne(
                $this->find($session->mid)
            );

            if (!$user->blocked) {
                $this->_initRole($user);
                $this->_initUnmanaged($user);
                $this->_init($user);

                $this->getService('Session')->setAuthorizerKey();

                return $user;
            }
        }

        setcookie('hmkey', '', time() - 3600, '/');

        return false;
    }

    public function authorizeByLogin($login)
    {
        $users = $this->fetchAll(
            $this->quoteInto(
                array('Login = ?'),
                array($login)
            )
        );

        if (count($users) == 1) {
            $user = $users->current();
            if (!$user->blocked) {
                $this->_initRole($user);
                $this->_initUnmanaged($user);
                $this->_init($user);

                return $user;
            }
        }

        return false;
    }

    public function authorizeByLdap($login, $password)
    {
        if (Zend_Registry::get('config')->ldap->authorization) {
            $ldapOptions = Zend_Registry::get('config')->ldap->options->toArray();
            
            foreach($ldapOptions['baseDn'] as $key => $option){
                $ldapOptions['accountDomainName']      = $ldapOptions['accountDomainName'][$key];
                $ldapOptions['accountDomainNameShort'] = $ldapOptions['accountDomainNameShort'][$key];
                $ldapOptions['baseDn']                 = $ldapOptions['baseDn'][$key];
                $ldapOptions['host']                   = $ldapOptions['host'][$key];
                
                $adapter = new HM_Auth_Adapter_Ldap(array('server' => $ldapOptions), $login, $password);
                if ($result = $adapter->authenticate()) {
					if ($result->getCode() == Zend_Auth_Result::FAILURE_USER_BLOCKED) {
						throw new HM_Exception_Auth(_('Ваша учетная запись заблокирована. Обратитесь в деканат для выяснения причин.'),0);
					} elseif($result->getCode() == Zend_Auth_Result::FAILURE_TEACHERS_BLOCKED){
						throw new HM_Exception_Auth(_('Ваша учетная запись заблокирована. Обратитесь к руководителю для выяснения причин.'),0);
					} elseif($result->getCode() == Zend_Auth_Result::FAILURE){
						throw new HM_Exception_Auth(_('Учетные данные не верны, проверьте корректность ввода.'),0);
					} elseif($result->getCode() == Zend_Auth_Result::PASSWORD_EXPIRED){
						throw new HM_Exception_Auth(_('Срок действия пароля истек.'),0);
					} elseif ($result->getCode() == Zend_Auth_Result::SUCCESS) {
						return true;
                    }
                }
            }
        }

        return false;
    }

    public function authorizeOnBehalf($userId)
    {
        $default = new Zend_Session_Namespace('default');
        $default->userRestore = $this->getCurrentUser();

        $users = $this->find($userId);
        if (count($users) == 1) {
            $user = $users->current();

            $this->_initRole($user);
            $this->_initUnmanaged($user);
            $this->_init($user);

            return $user;
        }

        return false;
    }

    public function restore()
    {
        $default = new Zend_Session_Namespace('default');
        if (isset($default->userRestore)) {
            $user = clone $default->userRestore;

            //$this->_initRole($user);
            $this->_initUnmanaged($user);
            $this->_init($user);
            unset($default->userRestore);
        }
    }

    /**
     * @throws HM_Exception_Auth
     * @param  string $login
     * @param  string $password
     * @return bool|HM_Model_Abstract
     */
    public function authorize($login, $password, $authorizedByLdap = false, $authorizedBySomethingElse = false, $systemInfo = false)
    {
		
		
		if ($authorizedBySomethingElse) {
            $collection = $this->fetchAll(
                $this->quoteInto(array('Login = ?'), array($login))
            );			
        } else {
            $collection = $this->fetchAll(
                $this->quoteInto(array('Login = ?', ' AND Password = PASSWORD(?)'), array($login, $password))
            );
			//throw new HM_Exception_Auth(_('Вы неверно ввели имя пользователя или пароль.'.count($collection)),0);			
        }

        if (count($collection) > 1) {
            throw new HM_Exception_Auth(_('Больше одного пользователя с таким логином.'), 0);
        }
		
		
		//if (count($collection) < 1) {
            //throw new HM_Exception_Auth(_('Вы неверно ввели имя пользователя или пароль.'),0);
        //}
		
        $user = $this->getOne($collection);
		
		

        $passwordOptions = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_PASSWORDS);

        if ($user && (!$user->isImportedFromAD() || ($user->isImportedFromAD() && $authorizedByLdap))) {
			
			
           
			//$user = $this->getOne($collection);
            if ($user->blocked) {
                $message = _('Ваш аккаунт заблокирован.');
                if (strlen($user->block_message)) {
                    $message .= sprintf(_(' Администратор указал следующую причину: %s'), $user->block_message);
                }
                throw new HM_Exception_Auth($message, 0);
            }

            setcookie('hmcaptcha', '', time() - 3600, '/');

            if($passwordOptions['passwordMaxPeriod'] > 0){
                $lastPasswordChangeDate = $this->getService('UserPassword')->getChangePasswordLastDate($user->MID);

                if((time() - strtotime($lastPasswordChangeDate)) > (3600 * 24 * $passwordOptions['passwordMaxPeriod']) || strtotime($lastPasswordChangeDate) == 0){
                    $user->force_password = 1;
                    $user = $this->update($user->getValues(), false);
                }
            }

            $this->_initRole($user);
            $this->_initUnmanaged($user, $systemInfo);
            $this->_init($user);

            return $user;

        } else {
			
            $user = $this->getOne(
                $this->fetchAll(
                    $this->quoteInto('Login = ?', $login)
                )
            );
			
            if ($user) {
					

			   if ($this->authorizeByLdap($login, $password)) {					                    
					$user->Password = new Zend_Db_Expr("PASSWORD(".$this->quoteInto('?', $password).")");
                    $user = $this->update($user->getValues());
                    return $this->authorize($login, $password, true, false, $systemInfo);
                }

                $user->invalid_login++;
                $this->update($user->getValues(), false);
            } 
			

            $this->getService('Captcha')->attempt($login);

            $captcha = $this->getService('Captcha')->getOne($this->getService('Captcha')->find($login));
            if($captcha && $user){

                if($captcha->attempts >= $passwordOptions['passwordMaxFailedTry'] && $passwordOptions['passwordRestriction'] == HM_User_Password_PasswordModel::RESTRICTION_WITH && $passwordOptions['passwordFailedActions'] == HM_User_Password_PasswordModel::TYPE_BLOCK){
                    $user->blocked = 1;
                    $user = $this->update($user->getValues());
                }

            }

        }

        throw new HM_Exception_Auth(_('Вы неверно ввели имя пользователя или пароль.'),0);

    }

    public function logout()
    {
        $user = $this->getCurrentUser();

        $this->getService('UserLoginLog')->logout($user->Login, _('Пользователь успешно вышел из системы.'), HM_User_Loginlog_LoginlogModel::STATUS_OK);

        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();
        $s = new Zend_Session_Namespace('s');
        $s->unsetAll();
        setcookie('hmkey', '', time() - 3600, '/');
        setcookie('hmexit', 'true', 0, '/');

        // помечаем пользователя как гостя
        $this->getService('Guest')->setSession(0);
    }

    public function isRoleExists($userId, $role)
    {
        switch($role) {
            case HM_Role_RoleModelAbstract::ROLE_ADMIN:
                $collection = $this->getService('Admin')->fetchAll($this->quoteInto('MID = ?', $userId));
                if (count($collection)) return true;
                break;
            case HM_Role_RoleModelAbstract::ROLE_MANAGER:
                $collection = $this->getService('Manager')->fetchAll($this->quoteInto('mid = ?', $userId));
                if (count($collection)) return true;
                break;
            case HM_Role_RoleModelAbstract::ROLE_DEVELOPER:
                $collection = $this->getService('Developer')->fetchAll($this->quoteInto('mid = ?', $userId));
                if (count($collection)) return true;
                break;
            case HM_Role_RoleModelAbstract::ROLE_DEAN:
                $collection = $this->getService('Dean')->fetchAll($this->quoteInto('MID = ?', $userId));
                if (count($collection)) return true;
                break;
            case HM_Role_RoleModelAbstract::ROLE_TEACHER:
                $collection = $this->getService('Teacher')->fetchAll($this->quoteInto('MID = ?', $userId));
                if (count($collection)) return true;
                break;
            case HM_Role_RoleModelAbstract::ROLE_TUTOR:
                $collection = $this->getService('Tutor')->fetchAll($this->quoteInto('MID = ?', $userId));
                if (count($collection)) return true;
                break;
            case HM_Role_RoleModelAbstract::ROLE_STUDENT:
                $collection = $this->getService('Student')->fetchAll($this->quoteInto('MID = ?', $userId));
                if (count($collection)) return true;
                break;
            case HM_Role_RoleModelAbstract::ROLE_SUPERVISOR:
                $collection = $this->getService('Supervisor')->fetchAll($this->quoteInto('user_id = ?', $userId));
                if (count($collection)) return true;
                break;
            case HM_Role_RoleModelAbstract::ROLE_EMPLOYEE:
                $collection = $this->getService('Employee')->fetchAll($this->quoteInto('user_id = ?', $userId));
                if (count($collection)) return true;
                break;
            case HM_Role_RoleModelAbstract::ROLE_USER:
                $collection = $this->find($userId);
                if (count($collection)) return true;
                break;

        }

        $customRoles = $this->getService('RoleCustom')->getList();
        if (isset($customRoles[$role])) {
            $roleId = str_replace(HM_Role_Custom_CustomModel::PREFIX, '', $role);
            return (boolean) $this->getOne($this->getService('RoleCustomAssign')->find($roleId, $userId));

        }

        return false;
    }

    /**
     * Возвращает массив ролей назначеных пользователю с ИД $userId
     * в данный массив не попадает роль user усли у пользователя имеется любая другая роль.
     * @param int $userId
     * @return array
     */
    public function getUserRoles($userId)
    {
        $result = array();
        if ( $userId ) {
            $select = $this->getSelect()->from('roles')->where('MID=?',$userId);
            $r = $select->query()->fetch();

            if ( $r ) {
                $result = explode(',', $r['role']);
            }

// роль user добавляется автоматически всем незаблокированным посредством view
//             $collection = $this->find($userId);
//             if ( count($collection) ) $result[] = HM_Role_RoleModelAbstract::ROLE_USER;
        }
        return $result;

    }


    /**
     * Удаляет роль $role для пользователя $userId
     * @param int $userId
     * @param string $role
     * @return boolean
     */
    public function removalRole($userId, $role)
    {
        switch($role) {
            case HM_Role_RoleModelAbstract::ROLE_ADMIN:
                $this->getService('Admin')->deleteBy($this->quoteInto('MID = ?', $userId));
                return true;
                break;
            case HM_Role_RoleModelAbstract::ROLE_MANAGER:
                $this->getService('Manager')->deleteBy($this->quoteInto('mid = ?', $userId));
                return true;
                break;
            case HM_Role_RoleModelAbstract::ROLE_DEVELOPER:
                $this->getService('Developer')->deleteBy($this->quoteInto('mid = ?', $userId));
                return true;
                break;
            case HM_Role_RoleModelAbstract::ROLE_DEAN:
                $this->getService('Dean')->deleteBy($this->quoteInto('MID = ?', $userId));
                return true;
                break;
            case HM_Role_RoleModelAbstract::ROLE_TEACHER:
                $this->getService('Teacher')->deleteBy($this->quoteInto('MID = ?', $userId));
                return true;
                break;
            case HM_Role_RoleModelAbstract::ROLE_TUTOR:
                $this->getService('Tutor')->deleteBy($this->quoteInto('MID = ?', $userId));
                return true;
                break;
            case HM_Role_RoleModelAbstract::ROLE_STUDENT:
                $this->getService('Student')->deleteBy($this->quoteInto('MID = ?', $userId));
                return true;
                break;
            case HM_Role_RoleModelAbstract::ROLE_SUPERVISOR:
                $this->getService('Supervisor')->deleteBy($this->quoteInto('user_id = ?', $userId));
                return true;
                break;
        }
        return false;
    }

    public function switchRole($role) {
        $roles = HM_Role_RoleModelAbstract::getBasicRoles();
        $customRoles = $this->getService('RoleCustom')->getList();
        if ($role && (isset($roles[$role]) || isset($customRoles[$role])) && $this->isRoleExists($this->getCurrentUserId(), $role)) {
            $custom = false;
            if (isset($customRoles[$role])) {
                // unmanaged custom roles hack
                $role = $this->getService('RoleCustom')->getBasicRole(str_replace(HM_Role_Custom_CustomModel::PREFIX, '', $role));
                $custom = true;
            }
            if($role == HM_Role_RoleModelAbstract::ROLE_ADMIN){
                $this->getService('Log')->log(
                    $this->getCurrentUserId(),
                    'Switch role to admin',
                    'Success',
                    Zend_Log::NOTICE
                );
            }
            $s = new Zend_Session_Namespace('s');
            $s->perm  = $this->getService('Unmanaged')->getPermission($role);
            $auth = Zend_Auth::getInstance();
            $auth->setStorage(new Zend_Auth_Storage_Session('default'));
            $user = $auth->getIdentity();
            $user->role = $role;
            $auth->getStorage()->write($user);

            if ($custom) {
                // unmanaged custom roles hack
                $this->getService('Unmanaged')->getController()->setUser();
            }
        }else{
            if($role == HM_Role_RoleModelAbstract::ROLE_ADMIN){
                $this->getService('Log')->log(
                    $this->getCurrentUserId(),
                    'Switch role to admin',
                    'Fail',
                    Zend_Log::WARN
                );
            }
        }
    }

    public function delete($id){
        $id = (int) $id;

        $this->getService('Developer')->deleteBy(array('mid = ?' => $id));
        $this->getService('Dean')->deleteBy(array('MID = ?' => $id));
        $this->getService('Student')->deleteBy(array('MID = ?' => $id));
        $this->getService('Admin')->deleteBy(array('MID = ?' => $id));
        $this->getService('Manager')->deleteBy(array('mid = ?' => $id));
        $this->getService('Teacher')->deleteBy(array('MID = ?' => $id));
        $this->getService('Supervisor')->deleteBy(array('user_id = ?' => $id));
        $this->getService('GroupAssign')->deleteBy(array('mid = ?' => $id));
        $this->getService('Claimant')->deleteBy(array('MID = ?' => $id));
        $this->getService('LessonAssign')->deleteBy(array('MID = ?' => $id));
        $this->getService('RoleCustomAssign')->deleteBy(array('mid = ?' => $id));

        // Очистка поля mid в оргструктуре
        $this->getService('Orgstructure')->updateWhere(
            array('MID' => NULL),
            array('MID = ? ' => $id)
        );
        
        $this->getService('Orgstructure')->deleteBy(array('mid = ?' => $id));
        $this->getService('StudyGroupCustom')->deleteBy(array('user_id = ?' => $id));
        $this->getService('ProgrammUser')->deleteBy(array('user_id = ?' => $id));

        parent::delete($id);
    }

    /**
     * @param  int $number
     * @param  string $prefix
     * @param  string $password
     * @param  string $role
     * @return HM_Collection
     */
    public function generate($number, $prefix, $password, $role)
    {
        $users = new HM_Collection(array(), 'HM_User_UserModel');
        $count = $i = 0;
        while($count < $number) {
            $login = $prefix.str_pad((string) $i, 3, "0", STR_PAD_LEFT);
            $collection = $this->fetchAll($this->quoteInto('Login = ?', $login));
            if (count($collection)) {
                $i++;
                continue;
            } else {
                $user = $this->insert(
                    array(
                        'Login' => $login,
                        'Password' => new Zend_Db_Expr(sprintf("PASSWORD('%s')", $password)),
                        'need_edit' => 1
                    )
                );
                if ($user) {
                    $this->assignRole($user->MID, $role);
                }
                $users[count($users)-1] = $user;
                $count++;
            }
        }
        return $users;
    }

    public function generateLogin()
    {
        $i = 0;

        $users = $this->fetchAll(
            array(
            	"Login LIKE ?" => self::NEW_LOGIN_PREFIX . "%",
                //"MAX(ABS(REPLACE(Login, ?, '')))" => self::NEW_LOGIN_PREFIX
            ),
            'Login DESC', 500);

            // Last $user save!!!
        foreach($users as $user){
            if(preg_match("/" . self::NEW_LOGIN_PREFIX . "([0-9]{4,})/i", $user->Login, $match)){
                break;
            }

        }

		if ($user) {
			$i = (int) substr($user->Login, strlen(self::NEW_LOGIN_PREFIX));
		}

        while(true) {
            $login = self::NEW_LOGIN_PREFIX.str_pad((string) $i, 4, "0", STR_PAD_LEFT);
            $collection = $this->fetchAll($this->quoteInto('Login = ?', $login));
            if (count($collection)) {
                $i++;
                continue;
            } else {
                return $login;
            }
        }
    }

    public function getMetadataArrayFromForm(Zend_Form $form)
    {
        return array(
// #11006 - поля вынесены из мета-кучи
//           'gender' => $form->getValue('gender'),
//           'year_of_birth' => $form->getValue('year_of_birth'),
           'tel' => $form->getValue('tel'),
           'team' => $form->getValue('team'),
           'additional_info' => $form->getValue('additional_info')
        );
    }

    public function isLoginExists($login)
    {
        return $this->getOne($this->fetchAll($this->quoteInto('Login = ?', $login)));
    }

    public function getUsersOnline(array $usersList = array())
    {
        $config = Zend_Registry::get('config');
        $select = $this->getSelect();
        $select->from('sessions', array('mid'))
               ->where('stop >= ?',  date('Y-m-d H:i:s', time() - (int)$config->user->onlinetimeout));
        if (count($usersList)) {
            $select->where('mid IN ('. implode(',', $usersList) .')');
        }
        $query = $select->query();
        $usersOnline = array();
        $res = $query->fetchAll();
        foreach($res as $item) {
            $usersOnline []= $item['mid'];
        }
        return $usersOnline;
    }

    public function insert($data)
    {
        if($data['MID'] != '') {
        $this->getService('UserPassword')->insert(
            array(
            	'user_id' => $data['MID'],
                'password' => md5($data['Password']),
                'change_date' => date('Y-m-d H:i:s')
            )
        );
        }
        $data['Registered'] = date('Y-m-d H:i:s');
        return parent::insert($data);

    }

    public function update($data, $updatePasswordHistory = true)
    {
        static $updateCount = 0;

        if($updatePasswordHistory && $data['Password']!= "" && $updateCount == 0){
            $this->getService('UserPassword')->insert(
                array(
                	'user_id' => $data['MID'],
                    'password' => md5($data['Password']),
                    'change_date' => date('Y-m-d H:i:s')
                )
            );

        }
        $updateCount++;
        return parent::update($data);
    }

    /**
     * Notifies users that they are unblocked
     *
     * @param array $data contains 'id' of user(or users) for unblock, and 'placeholders' key
     * @param integer $template_id is template id
     * @param integer $channel_id  is channel_id
     * @see \library\HM\HM_Messenger.php
     */
    public function notifyUserUnblock($data, $template_id = HM_Messenger::TEMPLATE_UNBLOCK , $channel_id = HM_Messenger::SYSTEM_USER_ID) {
        $data = (array)$data + array (
            'id' => null,
            'placeholders' => array(),
        );
        $messenger = $this->getService('Messenger');
        foreach ((array)$data['id'] as $id) {
            $messenger->addMessageToChannel(
                $channel_id,
                $id,
                $template_id,
                $data['placeholders']
            );
        }
        $messenger->sendAllFromChannels();
    }

    public function checkResponsibility($select)
    {
        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)) {
            $select = $this->getService('DeanResponsibility')->checkUsers($select);
        }
        return $select;
    }

    public function getUnitInfo($user_id)
    {
        $units = $this->getService('Orgstructure')->fetchAll($this->getService('Orgstructure')->quoteInto('MID = ?', $user_id));
        $info = array();
        foreach($units as $unit){
            $info[] = $this->getService('OrgstructureUnit')->getInfo($unit);
        }
        return $info;
    }

    public function pluralFormRolesCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('роль plural', '%s роль', $count), $count);
    }

    public function getSubjects($userId = null)
    {
        if (null === $userId) {
            $userId = $this->getCurrentUserId();
        }

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
            $collections = $this->getService('Student')->getSubjects($userId);
        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)) {
            $collections = $this->getService('Teacher')->getSubjects($userId);
        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)) {
            $collections = $this->getService('Dean')->getSubjects($userId); # Выводится в breadcrumb.tpl. На текущий момент не используется там. Возможно, этот список используется еще где-то, поэтому остался отбор сессий.
        } else {
            $collections = new HM_Collection();
        }


        // не отображаем прошедшие курсы
//        foreach ($collections as $offset => $subject) {
//            if ($subject->period == HM_Subject_SubjectModel::PERIOD_DATES && $subject->end) {
//                $end  = new HM_Date($subject->end);
//                $curr = new HM_Date();
//                if ($end->getTimestamp() < $curr->getTimestamp()) {
//                    $collections->offsetUnset($offset);
//                }
//            }
//        }
        return $collections;
    }


    public function getGroups($userId = null)
    {
        if (null === $userId) {
            $userId = $this->getCurrentUserId();
        }

        $groups = new HM_Collection(array());

        if ($userId) {
            $collection = $this->getService('GroupAssign')->fetchAll($this->quoteInto('mid = ?', $userId));
            if (count($collection)) {
                $groups = $this->getService('Group')->fetchAll(
                    $this->quoteInto('gid IN (?)', $collection->getList('gid', 'gid'))
                );
            }
        }

        return $groups;
    }

    public function deleteDublicate($midDub)
    {
       // echo "дуб=".$midDub;
     //   echo "уникл=".$midUnic;
      //  exit;

        if (null !== $midDub)
        {
            //пытаемся сразу удалить дубликат
            $resultDelete = $this->deleteBy(array('MID = ?' => $midDub));
            if (null !== $resultDelete)
                return true;
            else
                return false;
        }
        else
            return false;
    }

    public static function getEmailConfirmationHash($userId)
    {
        return md5($userId . Zend_Registry::get('config')->privateKey);
    }

    public function checkEmailConfirmationHash($hash, $userId)
    {
        if (!empty($userId) && !empty($hash) && count($user = $this->fetchAll(array('MID = ?' => $userId, 'email_confirmed = ?' => 0)))) {
            return (md5($userId . Zend_Registry::get('config')->privateKey) === $hash) ? $user->current() : false;
        }
        return false;
    }

    public function getUsersByIds($ids = array())
    {
        if (count($ids)) {
            return $this->fetchAll($this->quoteInto('MID IN (?)', $ids));
        }
    }

    public function getById($id)
    {
        return $this->getOne($this->fetchAll($this->quoteInto('MID = ?', $id)));
    }
    
    /**
     * 
     * @param string $where
     * @return array of user ids
     */
    
    public function getIds($where = null)
    {
        $select = $this->getSelect();
        $select->from('People', array('MID'));
        if($where <> null){
            $select->where($where);
        }
        $stmt = $select->query();
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $ids = array();
        foreach ($rows as $value) {
            $ids[] = intval($value['MID']);
        }
        return $ids;
    }
	
	
	/**
	 * устанавливает настройку уведомлений /es/index/notifies в заданной конфигурации
	 *@return boolean
	*/
	public function setDefaultNotifies($userId) {		 
		if(!$userId){
			return false;
		}
		$userId = (int)$userId;
		
		$notify_type_id_email = Es_Entity_NotifyType::NOTIFY_TYPE_EMAIL; //1; // - Уведомления по почте
		$is_active = 1; //--ВКЛ
				
		$event_types_email = array(
			Es_Entity_AbstractEvent::EVENT_TYPE_FORUM_INTERNAL_ADD_MESSAGE, //5, //Добавление сообщения в форум на уровне курса			
			Es_Entity_AbstractEvent::EVENT_TYPE_COURSE_TASK_ACTION,			//12,//Выполнение задания студентом
			Es_Entity_AbstractEvent::EVENT_TYPE_PERSONALL_MESSAGE_SEND,		//16,//Получение персональных сообщений
		);
        	
		foreach($event_types_email as $event_type_id){			
			
			/*@var $eventType Es_Entity_AbstractEventType */
			$eventType = $this->getService('ESFactory')->newEventType();
			$eventType->setId($event_type_id);
			
			/*@var $notifyType Es_Entity_AbstractNotifyType */
			$notifyType = $this->getService('ESFactory')->newNotifyType();
			$notifyType->setId($notify_type_id_email);
			
			/*@var $notify Es_Entity_AbstractNotify */
			$notify = $this->getService('ESFactory')->newNotify();
			$notify->setEventType($eventType);
			$notify->setNotifyType($notifyType);
			$notify->setIsActive($is_active);
			$notify->setUserId($userId);
			$ev = $this->getService('EventServerDispatcher')->trigger(
				Es_Service_Dispatcher::EVENT_UPDATE_NOTIFY,
				$this,
				array('notify' => $notify)
			);
			$result = $ev->getReturnValue();
		}      
		return true;
    }
	
	
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'teacherList' 		=> $this->_teacherList,                 
                 'tutorList' 		=> $this->_tutorList,                 
                 'tutorGroupList' 	=> $this->_tutorGroupList,                 
            ),
            self::CACHE_NAME
        );
    }

    public function clearCache()
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME);
    }

    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_teacherList 	= $actions['teacherList'];            
            $this->_tutorList 		= $actions['tutorList'];            
            $this->_tutorGroupList 	= $actions['tutorGroupList'];            
            return true;
        }
        return false;
    }
	
	/**
	 * @return array('MID' => FIO)
	*/
	public function getTutorList(){		
		if(count($this->_tutorList)){			
			return $this->_tutorList;
		}
		$this->restoreFromCache();
		if(count($this->_tutorList)){			
			return $this->_tutorList;
		}
		
		$select = $this->getSelect();
        $select->from(array('p' => 'People'), array(
            'MID' 			=> 'p.MID',
            'LastName' 		=> 'p.LastName',
            'FirstName' 	=> 'p.FirstName',
            'Patronymic' 	=> 'p.Patronymic',
        ));
		$select->join(array('t' => 'Tutors'), 't.MID = p.MID', array());		 
        $select->where('t.CID > 0');
        $result = $select->query()->fetchAll();
		if(!count($result)){			
			return false;
		}
		$data = array();
		foreach($result as $i){
			$data[$i['MID']] = $i['LastName'].' '.$i['FirstName'].' '.$i['Patronymic'];
		}
		$this->_tutorList = $data;
		$this->saveToCache();		
		return $this->_tutorList;
	}
	
	/**
	 * @return array('MID' => FIO)
	*/
	public function getTeacherList(){		
		if(count($this->_teacherList)){			
			return $this->_teacherList;
		}
		$this->restoreFromCache();
		if(count($this->_teacherList)){			
			return $this->_teacherList;
		}
		
		$select = $this->getSelect();
        $select->from(array('p' => 'People'), array(
            'MID' 			=> 'p.MID',
            'LastName' 		=> 'p.LastName',
            'FirstName' 	=> 'p.FirstName',
            'Patronymic' 	=> 'p.Patronymic',
        ));
		$select->join(array('t' => 'Teachers'), 't.MID = p.MID', array());		 
        $select->where('t.CID > 0');
        $result = $select->query()->fetchAll();
		if(!count($result)){			
			return false;
		}
		$data = array();
		foreach($result as $i){
			$data[$i['MID']] = $i['LastName'].' '.$i['FirstName'].' '.$i['Patronymic'];
		}
		$this->_teacherList = $data;
		$this->saveToCache();		
		return $this->_teacherList;
	}
	
	/**
	 * принадлежность к главному офису РГСУ
	*/
	public function isMainOrganization(){
		$user = $this->getCurrentUser();
        if (!$user) { return false;}
        
		if($user->organization == HM_User_UserModel::MAIN_ORGANIZATION || empty($user->organization)){
			return true;
		}		
		return false;
	}
	
	
	/**
	 * Список групп, на которые назначены тьюторы с группировкой по сессии. 
	 * @return array('CID' => 'GID' => 'MID')
	*/
	public function getTutorGroupList(){		
		if(count($this->_tutorGroupList)){			
			return $this->_tutorGroupList;
		}
		$this->restoreFromCache();
		if(count($this->_tutorGroupList)){			
			return $this->_tutorGroupList;
		}
		
		$select = $this->getSelect();
        $select->from(array('tg' => 'Tutors_groups'), array(
            'MID', 
			'CID',
            'GID',
        ));		
		$select->join(array('t' => 'Tutors'), 't.CID = tg.CID AND t.MID = tg.MID', array());			
        $select->where('tg.CID > 0');
        $select->where('tg.MID > 0');
        $select->where('tg.GID > 0');
        $result = $select->query()->fetchAll();
		
		if(!count($result)){			
			return false;
		}
		$data = array();
		foreach($result as $i){
			$data[$i['CID']][$i['GID']][$i['MID']] = $i['MID'];
		}
		$this->_tutorGroupList = $data;
		$this->saveToCache();		
		return $this->_tutorGroupList;
	}
	
	
	public function getPeopleInfoList(){
		
		$select = $this->getSelect();
		$select->from('People', array(
			'MID', 'mid_external', 'FirstName', 'LastName', 'Patronymic',
		));
		$select->where("mid_external IS NOT NULL AND  mid_external != ''");
		$res = $select->query()->fetchAll();
		if(!$res){ return false; }
		$data = array();
		foreach($res as $i){
			$data[$i['MID']] = array(
				'mid_external' 	=> $i['mid_external'],
				'fio' 			=> $i['LastName'].' '.$i['FirstName'].' '.$i['Patronymic'],				
			);
		}
		return $data;		
	}	
	
	public function getUserIDsByName($fio){
		if(empty($fio)){ return false; }
		
		$select = $this->getSelect();
		$select->from('People', array('MID'));		 
		$select->where($this->quoteInto("CONCAT(CONCAT(CONCAT(CONCAT(LastName, ' '), FirstName), ' '), Patronymic) LIKE ?", '%'.$fio.'%'));
		$res = $select->query()->fetchAll();
		if(empty($res)){ return false; }
		$data = array();
		foreach($res as $i){
			$data[$i['MID']] = $i['MID'];
		}
		return $data;
	}
	
	public function isExtramural($user_id = false)
	{
		$user_id = (int)$user_id;
		
		if(!empty($user_id)){
			$user = $this->getById($user_id);
		} else {
			$user = $this->getCurrentUser();
		}
		if(empty($user)){ return false; }
		
		$info = $this->getService('RecordCard')->getRecordbookInfo($user->mid_external);
		if(empty($info)){ return false; }
		
		$study_form = mb_strtolower(trim($info->study_form));
		if($study_form == 'заочная'){
			return true;
		}
		return false;
	}
	
	/**
	 * @return bool
	 * Проверяет, является ли пользователь студентом. Проверяет по назначенным сессиям. Т.к. по ролям проверять неверно, почти всем новым польователям назначается роль "студент".
	 * Завершенные сессии также учитываем. Т.к. это может быть студент, вышедший на ГОСы.
	 */
	public function isStudent($user_id = false)
	{
		
		$user_id = (int)$user_id;
		
		if(!empty($user_id)){
			$user = $this->getById($user_id);
		} else {
			$user = $this->getCurrentUser();
		}
		$find = $this->getService('Student')->fetchAll(array('MID = ?' => $user->MID));
		
		if(count($find) < 1){ 
			$find = $this->getService('Graduated')->fetchAll(array('MID = ?' => $user->MID));
		}
		
		if(count($find) > 0){ return true; }
		return false;
	}
	
	
	
	public function saveEmail($email, $is_update_profile = true)
	{		
		if(!$email) {
			return false;
		}
		
		$validator = new Zend_Validate_EmailAddress();
		if (!$validator->isValid($email)) {
			return false;
		}
		
		$user 		= $this->getCurrentUser();
		$user_id 	= $user->mid_external;
		if(!$user_id) {
			return false;
		}
		
		
		//--Если есть e-mail в профиле и он валидный, то этот мы не сохраняем
		$validator = new Zend_Validate_EmailAddress();
		if ($validator->isValid($user->EMail)) {
			return false;
		}
		
		$db = $this->getMapper()->getTable()->getAdapter();
		
		//--удаляем cтарые записи студента.
		$where = $db->quoteInto('mid_external = (?)', $user_id);
		$db->delete('student_ext_emails', $where); 
		
		$result = $db->insert('student_ext_emails', array(
			'mid_external'  => $user_id,
			'email'     	=> $email,            
		));
		
		if($result) {

			if($is_update_profile){
				$data = array(
					'MID' 	=> $user->MID,
					'EMail' => $email,                    
				);
						
				$result = $this->update($data);
			} else {
				return true;
			}
			
			if($result) {
				return true;
			}
			
			return false;
		}
		
		return false;
	}
	
	
	public function getByIdExternal($id_externals = array())
	{
		if(empty($id_externals)){ return false; }
		if(!is_array($id_externals)){
			$id_externals = array($id_externals);
		}
		return $this->fetchAll($this->quoteInto('mid_external IN (?)',  $id_externals), array('LastName', 'FirstName', 'Patronymic'));
		
	}
	
	# вошел от имени другого пользователя
	public function isLoginAs()
	{
		$default	= new Zend_Session_Namespace('default');
        $user_base	= $default->userRestore;
		
		if(isset($user_base)){
			return true;
		}
		return false;
	}
	
	
}