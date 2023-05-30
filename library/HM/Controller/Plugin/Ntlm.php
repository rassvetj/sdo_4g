<?php
class HM_Controller_Plugin_Ntlm extends Zend_Controller_Plugin_Abstract
{

    const SERVER_VAR_NAME = 'AUTH_USER';

    private $_ldap;
    private $_idField;

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $url = $_SERVER['REQUEST_URI'];

        if (false !== strstr($url, 'index.php')) {
            $url = Zend_Registry::get('view')->serverUrl('/');
        }

        //$_SERVER['AUTH_USER'] = 'test2013';
        $serviceContainer = Zend_Registry::get('serviceContainer');

        if (!isset($_COOKIE['hmexit']) && !$serviceContainer->getService('User')->getCurrentUser() && isset($_SERVER[self::SERVER_VAR_NAME])) {

            $this->_idField = Zend_Registry::get('config')->ldap->user->uniqueIdField;

            $login = trim($_SERVER[self::SERVER_VAR_NAME]);
            if (false !== strstr($login, '\\')) {
                list($domain, $login) = explode('\\', $login);
            }

            // todo: check for domain
            if (strlen($login)) {
                try {
                    $authorized = $serviceContainer->getService('User')->authorizeByLogin($login);

                    if (!$authorized && Zend_Registry::get('config')->ntlm->createIfNotExists) {

                        // create if not exists user using ldap
                        if (!$serviceContainer->getService('User')->isLoginExists($login)) {

                            $entry = $serviceContainer->getService('Ldap')->findUserByLogin($login);

                            if ($entry && isset($entry[$this->_idField][0])) {
                                $values = array('Login' => $login, 'isAD' => 1);

                                $mapping = Zend_Registry::get('config')->ldap->mapping->user->toArray();
                                foreach($mapping as $field => $value) {
                                    if (isset($entry[$field][0])) {
                                        $values[$value] = $entry[$field][0];
                                    }
                                }
                                //$values['LastName']     = $entry['sn'][0];
                                //$values['FirstName']    = $entry['givenname'][0];
                                //$values['Patronymic']   = $entry['initials'][0];
                                $values['mid_external'] = $entry[$this->_idField][0];

                                if (strlen($values['mid_external'])) {
                                    $collection = $serviceContainer->getService('User')->fetchAll(
                                        $serviceContainer->getService('User')->quoteInto('mid_external LIKE ?', $values['mid_external'])
                                    );

                                    if (count($collection)) {
                                        $exists = $collection->current();
                                        if ($exists) {
                                            $values['MID'] = $exists->MID;
                                        }
                                    }
                                }

                                if (isset($values['MID'])) {
                                    $user = $serviceContainer->getService('User')->update($values);
                                } else {
                                    $user = $serviceContainer->getService('User')->insert($values);
                                }

                                $authorized = $serviceContainer->getService('User')->authorizeByLogin($login);

                            }
                        }
                    }
                } catch (Zend_Db_Exception $e) {
                    Zend_Registry::get('log_system')->debug(
                        $e->getMessage() . "\n" . $e->getTraceAsString()
                    );
                    throw $e;
                }

                if ($authorized) {
                    $serviceContainer->getService('UserLoginLog')->login($login, _('Пользователь успешно авторизован.'), HM_User_Loginlog_LoginlogModel::STATUS_OK);
                    header('Location: '.$url);
                    exit();
                }else{
                    $serviceContainer->getService('UserLoginLog')->login($login, _('Вы неверно ввели имя пользователя или пароль.'), HM_User_Loginlog_LoginlogModel::STATUS_FAIL);
                }
            }
        }
    }
}
