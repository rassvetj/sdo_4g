<?php

class HM_User_UserModel extends HM_Model_Abstract
{
    const PASSWORD_LENGTH = 7;
    const PHOTO_WIDTH = 114;
    const PHOTO_HEIGHT = 152;

    const EMAIL_NOT_CONFIRMED = 0;
    const EMAIL_CONFIRMED = 1;

    const NEED_EDIT_AFTER_FIRST_LOGIN = 1;
    
    const ROLE_1C_STUDENT = 1;
    const ROLE_1C_TUTOR   = 2;

    /**
     * @var HM_User_Metadata_MetadatModel
     */
    private $_metadata = null;
    
    protected $_primaryName = 'MID';

    public function __construct($data)
    {
        parent::__construct($data);

        if (isset($data['Information'])) {
            if (null == $this->_metadata)
            {
                $this->_metadata = new HM_User_Metadata_MetadataModel(array());
            }

            $this->_metadata->parseString($data['Information']);
        }
    }

    public function getCardFields()
    {
        $return = array (
            //'LastName' => _('Фамилия'),
            //'FirstName' => _('Имя'),
            //'Patronymic' => _('Отчетство'),
            'Login' => _('Логин'),
            //'BirthDate'      => _('Год рождения'),
        );

        $this->getService()->getService('Activity')->initializeActivityCabinet('', '', 0);
        $isModerator = $this->getService()->getService('Activity')->isUserActivityPotentialModerator(
            $this->getService()->getService('User')->getCurrentUserId()
        );

        if ($isModerator || !$this->getService()->getService('Option')->getOption('disable_personal_info')) {
            $return['getMetadataValue(year_of_birth)'] = _('Год рождения');
            $return['EMail'] = _('Email');
            $return['Phone'] = _('Рабочий телефон');
            $return['CellularNumber'] = _('Мобильный телефон');
        }
        return $return;
    }

    public function setMetadataValue($name, $value)
    {
        if (null == $this->_metadata)
        {
            $this->_metadata = new HM_User_Metadata_MetadataModel(array());
        }

        $this->_metadata->{$name} = $value;

        $this->Information = $this->_metadata->getString();
    }

    public function setMetadataValues($values)
    {
        if (is_array($values) && count($values)) {
            foreach($values as $name => $value) {
                $this->setMetadataValue($name, $value);
            }
        }
    }

    public function getMetadataValues()
    {
        $ret = array();
        if (null != $this->_metadata)
        {
            $ret = $this->_metadata->getValues();
        }
//         эти проверки должны быть в контроллере и использовать ($isModerator || !$this->getService()->getService('Option')->getOption('disable_personal_info'))
//         if (!in_array(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),array(HM_Role_RoleModelAbstract::ROLE_ADMIN,HM_Role_RoleModelAbstract::ROLE_DEAN)))
//         {
//             $this->_metadata->setValue('tel','');
//         }
        return $ret;
    }

    public function getMetadataValue($key)
    {
        if (($key == 'tel') && !empty($this->Phone)) {
            return $this->Phone; // плавная миграция Phone из метаданных в поле Phone
        }
        if (null != $this->_metadata)
        {
            return $this->_metadata->{$key};
        }

        return false;
    }

    public function getName()
    {
        if ($this->getService()->getService('Lang')->countLanguages() > 1) {
            if ($this->getService()->getCurrentLangId() != HM_User_UserService::DEFAULT_LANG) {
                if ($nameLat = $this->getNameLat()) {
                    return $nameLat;
                }
            }
        }
        return $this->getNameCyr();
    }

    public function getNameCyr()
    {
        if(empty($this->LastName) && empty($this->FirstName)) return $this->Login;

        $name = array();
        if(!empty($this->LastName)) $name[] = $this->LastName;
        if(!empty($this->FirstName)) $name[] = $this->FirstName;
        if(!empty($this->Patronymic)) $name[] = $this->Patronymic;

        return implode(' ', $name);
    }

    public function getNameLat()
    {
        if(empty($this->LastNameLat) && empty($this->FirstNameLat)) return false;

        $name = array();
        if(!empty($this->LastNameLat)) $name[] = $this->LastNameLat;
        if(!empty($this->FirstNameLat)) $name[] = $this->FirstNameLat;

        return implode(' ', $name);
    }

    public function getRoles()
    {
        $result = array();

        if (isset($this->roles)) {
            $result = $this->getValue('roles');
        }

        return $result;
    }

    public function getGroups()
    {
        $result = array();
        if (isset($this->groups)) {
            $result = $this->getValue('groups');
        }

        return $result;
    }

    public function isStudent()
    {
        $roles = $this->getRoles();
        if (count($roles)) {
            foreach($roles as $role) {
                if ($role instanceof HM_Role_StudentModel) {
                    return true;
                }
            }
        }
        return false;
    }

    public function isGroupUser($groupId)
    {
        $groups = $this->getGroups();
        if (count($groups)) {
            foreach($groups as $group) {
                if ($group->gid == $groupId) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getPath()
    {
        $config = Zend_Registry::get('config');
        $filePath = $config->path->upload->photo;
        $filePath = realpath($filePath);

        if(!is_dir($filePath)){
            return false;
        }
        $maxFilesCount = (int) $config->path->upload->maxfilescount;
        $path = floor($this->MID / $maxFilesCount);
        if(!is_dir($filePath . DIRECTORY_SEPARATOR . $path)){
            $old_umask = umask(0);
            mkdir($filePath . DIRECTORY_SEPARATOR . $path, 0777);
            chmod($filePath . DIRECTORY_SEPARATOR . $path, 0777);
            umask($old_umask);
        }
        return  $filePath . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR;
    }

    public function getPhoto()
    {
        $config = Zend_Registry::get('config');
        $path = $this->getPath();
        $maxFilesCount = (int) $config->path->upload->maxfilescount;
        $glob = glob($path . $this->MID .'.*');
        foreach($glob as $value) {
            $fn = $config->src->upload->photo.floor($this->MID / $maxFilesCount) . '/' . basename($value);
            return $fn.'?_='.@filemtime($fn);
        }
        return $config->src->default->photo;
    }

    public function generateKey()
    {
        return md5(md5(sprintf('%s|%s', $this->MID, $this->Login)).'salt');
    }

    public function getServiceName()
    {
        return 'User';
    }

    public function isImportedFromAD()
    {
        return $this->isAD || $this->role_1c;
    }

    public function getLdapDisabledFormFields()
    {
        return array();
        /*
        $fields = array();
        $config = Zend_Registry::get('config');
        if (isset($config->ldap->mapping->user)) {
            foreach($config->ldap->mapping->user->toArray() as $ldapName => $fieldName) {
                switch($fieldName) {
                    case 'LastName':
                    case 'FirstName':
                    case 'Patronymic':
                        $fields[] = strtolower($fieldName);
                        break;
                    case 'LastNameLat':
                        $fields[] = 'lastnameLat';
                        break;
                    case 'FirstNameLat':
                        $fields[] = 'firstnameLat';
                        break;
                    case 'Login':
                        $fields[] = 'userlogin';
                        break;
                    default:
                        $fields[] = $fieldName;
                }
            }
        }
        return $fields;
         */
    }

    public function prepareFormLdap(Zend_Form $form, HM_Controller_Action $controller)
    {
        //$form->removeElement('userlogin');
        $form->removeElement('userpassword');
        $form->removeElement('userpasswordrepeat');
        $form->removeElement('generatepassword');

        //$form->removeDisplayGroup('Users1');

        $disabledFields = $this->getLdapDisabledFormFields();
        if (count($disabledFields)) {
            foreach($disabledFields as $disabledField) {
                if ($element = $form->getElement($disabledField)) {
                    /*if ($controller->getRequest()->isPost()) {
                        $form->removeElement($disabledField);
                    } else {*/
                        $element->setAttrib('disabled', true);
                        $element->setRequired(false);
                    //}
                }
            }
        }

    }

    /**
     * Получить время последнего логина в формате timestamp
     *
     * Существование метода связанно с некорректным хранением данных о последнем логине в базе:
     * хранится целочисленное значение приведённое к таковому из
     * строкового представления timestamp с вырезанными символами
     * отличными от числовых.
     * Т.е. вместо: timestamp "2000-12-31 23:59:59", хранится: int "20001231235959"
     *
     * @return string
     */
    public function getLastLoginTimestamp()
    {
        return preg_replace('/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '$1-$2-$3 $4:$5:$6', $this->last);
    }
}