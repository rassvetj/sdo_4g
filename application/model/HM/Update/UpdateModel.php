<?php
class HM_Update_UpdateModel extends HM_Model_Abstract
{
    const DB_UPDATE_FILENAME = 'db/update.sql';
    const DB_ROLLBACK_FILENAME = 'db/rollback.sql';
    const UPDATE_XML = 'update.xml';
    const FILES_DIR = 'files';
    const BACKUP_DIR = 'backup';

    public function isUpdateInstalled()
    {
        if (strlen($this->servers)) {
            $servers = unserialize($this->servers);
            return isset($servers[Zend_Registry::get('serviceContainer')->getService('Update')->getServerAddr()]);
        }
        return false;
    }

}