<?php
class HM_Update_UpdateService extends HM_Service_Abstract
{

    public function getServerAddr()
    {
        if (isset($_SERVER['SERVER_ADDR'])) {
            return $_SERVER['SERVER_ADDR'];
        }
        return $_SERVER['SERVER_NAME'];
    }

    private function _getUpdateXml($filename)
    {
        $zip = new ZipArchive();
        if (!$zip->open($filename)) {
            throw new HM_Exception(sprintf(_('Невозможно открыть архив: %s'), $filename));
        }

        $fp = $zip->getStream('update.xml');
        if (!$fp) {
            throw new HM_Exception(_('Невозможно прочитать файл update.xml'));
        }

        $updateXml = '';
        while(!feof($fp)) {
            $updateXml .= fread($fp, 1024);
        }
        fclose($fp);

        $zip->close();

        return $updateXml;
    }

    private function _isUpdateExists($number)
    {
        $collection = $this->fetchAll(
            $this->quoteInto('update_id = ?', $number)
        );

        if (count($collection)) {
            return $collection->current();
        }

        return false;
    }

    private function _isUpdateNumberCorrect($number)
    {
        $count = 1;
        $collection = $this->fetchAll(null, 'update_id DESC');
        if (count($collection)) {
            foreach($collection as $update) {
                if ($update->isUpdateInstalled()) {
                    $count = $update->update_id + 1;
                    break;
                }
            }
        }

        return ($count == $number);
    }

    private function _extract($filename, $destination)
    {
        $zip = new ZipArchive();
        if (!$zip->open($filename)) {
            throw new HM_Exception(sprintf(_('Невозможно открыть архив: %s'), $filename));
        }

        if (!$zip->extractTo($destination)) {
            throw new HM_Exception(sprintf(_('Невозможно распаковать архив обновления %s'), $filename));
        }
        $zip->close();
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    private function _getDbAdapter()
    {
        if (isset(Zend_Registry::get('config')->update->db)) {
            return Zend_Db::factory(Zend_Registry::get('config')->update->db->adapter, Zend_Registry::get('config')->update->db->params);
        }
        return $this->getSelect()->getAdapter();
    }

    private function _updateDb($updatePath, $filename)
    {
        $filename = $updatePath.'/'.$filename;

        if (!file_exists($filename)) {
            throw new HM_Exception(sprintf(_('Отсутствует файл %s'), $filename));
        }

        if (!is_readable($filename)) {
            throw new HM_Exception(sprintf(_('Невозможно прочитать файл %s'), $filename));
        }

        $sql = file_get_contents($filename);
        if (strlen($sql)) {
            $queries = explode("\n", $sql);
            if (count($queries)) {
                $adapter = $this->_getDbAdapter();
                $adapter->beginTransaction();
                foreach($queries as $query) {
                    $query = trim($query);
                    if (!strlen($query)) continue;

                    try {
                        $adapter->query($query);
                    } catch (Zend_Db_Exception $e) {
                        $adapter->rollBack();
                        throw new HM_Exception(sprintf(_('Ошибка выполнения SQL запроса %s: %s'), $query, $e->getMessage()));
                    }
                }
                $adapter->commit();
            }
        }
    }

    private function _getPath($dir)
    {
        $dir = str_replace(realpath(Zend_Registry::get('config')->path->upload->update), '', $dir);
        $parts = explode_by_slashes($dir);
        unset($parts[0]);
        unset($parts[1]);
        unset($parts[2]);
        $dir = join('/', $parts);
        return realpath(APPLICATION_PATH.'/../').'/'.$dir;

    }

    private function _getBackupFileName($filename)
    {
        $filename = str_replace('/files/', '/backup/', $filename);
        $filename = str_replace('\\files\\', '\\backup\\', $filename);
        return $filename;
    }

    private function _makeDirs($filename)
    {
        $parts = pathinfo($filename);
        if (isset($parts['dirname'])) {
            if (!file_exists($parts['dirname'])) {
                if (!mkdir($parts['dirname'], null, true)) {
                    throw new HM_Exception(sprintf(_('Невозможно создать каталог %s'), $parts['dirname']));
                }
            }
        }
        return true;
    }

    private function _updateDir($dir)
    {
        $dir = $this->_getPath($dir);
        if (!file_exists($dir) || !is_dir($dir)) {
            if (!mkdir($dir)) {
                throw new HM_Exception(sprintf(_('Невозможно создать каталог %s'), $dir));
            }
            return $dir;
        }
        return true;
    }

    private function _updateFile($filename, $rollback = false)
    {
        $destFileName = $this->_getPath($filename);
        if (!$rollback && file_exists($destFileName) && is_file($destFileName)) {
            $backupFileName = $this->_getBackupFileName($filename);
            $this->_makeDirs($backupFileName);
            if (!copy($destFileName, $backupFileName)) {
                throw new HM_Exception(sprintf(_('Невозможно создать резервную копию из файла %s в %s'), $destFileName, $backupFileName));
            }
        }
        if (!copy($filename, $destFileName)) {
            throw new HM_Exception(sprintf(_('Невозможно скопировать файл %s в %s'), $filename, $destFileName));
        }

    }

    private function __updateFiles($source, $rollback = false)
    {
        if ($dh = opendir($source)) {
            while (($file = readdir($dh)) !== false) {
                if (($file == '.' || $file == '..')) continue;
                $filename = realpath($source.'/'.$file);
                if (is_dir($filename)) {
                    $this->_updateDir($filename);
                    $this->__updateFiles($filename, $rollback);
                    continue;
                } elseif (is_file($filename)) {
                    $this->_updateFile($filename, $rollback);
                }
            }
            closedir($dh);
        }
    }

    private function _updateFiles($source, $rollback = false)
    {
        if (!is_dir($source)) {
            throw new HM_Exception(sprintf(_('Не найден каталог файлов обновления %s'), $source));
        }

        if (!is_readable($source)) {
            throw new HM_Exception(sprintf(_('Каталог файлов %s не доступен для чтения'), $source));
        }

        $this->__updateFiles($source, $rollback);
    }

    private function _rollback($source, $update = null)
    {
        $servers = array();
        if (null !== $update) {
            if (strlen($update->servers)) {
                $servers = unserialize($update->servers);
                unset($servers[$this->getServerAddr()]);
            }
        }
        if (empty($servers)) {
            $this->_updateDb(realpath($source.'/../'), HM_Update_UpdateModel::DB_ROLLBACK_FILENAME);
        }
        $this->_updateFiles($source, true);

        if (null !== $update) {
            if (empty($servers)) {
                $this->delete($update->update_id);
            } else {
                $update->servers = serialize($servers);
                $this->update($update->getValues());
            }
        }
    }

    public function install($filename)
    {
        if (is_readable($filename)) {
            $updateXml = $this->_getUpdateXml($filename);
            if (!strlen($updateXml)) {
                throw new HM_Exception(sprintf(_('Пустой файл: %s'), 'update.xml'));
            }

            $xml = new SimpleXMLElement($updateXml);
            if (!$xml) {
                throw new HM_Exception(sprintf(_('Неверный формат файлы xml: %s'), 'update.xml'));
            }

            $number = (int) $xml->number;

            if (!$this->_isUpdateNumberCorrect($number)) {
                throw new HM_Exception(sprintf(_('Неверный номер обновления: %s'), $number));
            }

            $destination = Zend_Registry::get('config')->path->upload->update.'/'.$number;

            if (!is_dir($destination)) {
                if (!mkdir($destination)) {
                    throw new HM_Exception(sprintf(_('Невозможно создать каталог %s'), $destination));
                }
            }

            $this->_extract($filename, $destination);

            if (!is_dir($destination.'/'.HM_Update_UpdateModel::BACKUP_DIR) && !mkdir($destination.'/'.HM_Update_UpdateModel::BACKUP_DIR)) {
                throw new HM_Exception(sprintf(_('Невозможно создать каталог %s'), $destination.'/'.HM_Update_UpdateModel::BACKUP_DIR));
            }

            if ($update = $this->_isUpdateExists((int) $number)) {
                if ($update->isUpdateInstalled()) {
                    throw new HM_Exception(sprintf(_('Обновление уже установлено на сервер %s'), $this->getServerAddr()));
                }

                try {
                    $this->_updateFiles($destination.'/'.HM_Update_UpdateModel::FILES_DIR);
                } catch(HM_Exception $e) {
                    $this->_rollback($destination.'/'.HM_Update_UpdateModel::BACKUP_DIR, $update);
                    throw $e;
                }

                $servers = array();
                if (strlen($update->servers)) {
                    $servers = unserialize($update->servers);
                }
                $servers[$this->getServerAddr()] = $this->getDateTime();

                $update->servers = serialize($servers);

                $this->update($update->getValues());

            } else {
                $this->_updateDb($destination, HM_Update_UpdateModel::DB_UPDATE_FILENAME);

                try {
                    $this->_updateFiles($destination.'/'.HM_Update_UpdateModel::FILES_DIR);
                } catch(HM_Exception $e) {
                    $this->_rollback($destination.'/'.HM_Update_UpdateModel::BACKUP_DIR);
                    throw $e;
                }

                $this->insert(
                    array(
                        'update_id' => $number,
                        'version' => (string) $xml->version,
                        'created' => $this->getDateTime(),
                        'created_by' => $this->getService('User')->getCurrentUserId(),
                        'updated' => $this->getDateTime(),
                        'organization' => (string) $xml->organization,
                        'description' => (string) $xml->description,
                        'servers' => serialize(array($this->getServerAddr() => $this->getDateTime()))
                    )
                );
            }


        }

        @unlink($filename);
    }

    public function uninstall($updateId)
    {
        $update = $this->getOne($this->find($updateId));
        if (!$update) {
            throw new HM_Exception(sprintf(_('Обновление %s не найдено'), $updateId));
        }

        if (!$this->_isUpdateNumberCorrect($updateId+1)) {
            throw new HM_Exception(sprintf(_('Данное обновление не является последним в системе %s'), $this->getServerAddr()));
        }

        $destination = Zend_Registry::get('config')->path->upload->update.'/'.$updateId;

        if (!file_exists($destination) || !is_dir($destination)) {
            throw new HM_Exception(sprintf(_('Не найден каталог обновления %s'), $destination));
        }

        $destination = realpath($destination);

        $this->_rollback($destination.'/'.HM_Update_UpdateModel::BACKUP_DIR, $update);

        $this->getService('Unmanaged')->deleteFolder($destination);
    }
}