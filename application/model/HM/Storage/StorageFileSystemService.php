<?php

class HM_Storage_StorageFileSystemService extends HM_Activity_ActivityService
{
    private $_cache = array();
    
    public function isExists($parent, $name, $isFile)
    {
        if($parent instanceof HM_User_UserModel) {
            $parent = $this->getRoot($parent);
        }
        $where = array();
        $where['is_file = ?'] = $isFile;
        $where['parent_id = ?'] = $parent->id;
        $where['name LIKE ?'] = $name;
        return ($this->fetchAll($where)->count() > 0);
    }
    
    public function rename($name, HM_Storage_StorageFileSystemModel $item)
    {
        $alias = $this->getAlias($name);
        $newPath = $this->getPath($this->getParent($item)) .'/'. $alias;
        if(@rename($this->getPath($item), $newPath) === false) {
            throw new Exception(
                sprintf('Cannot rename %s to %s', $this->getPath($item), $newPath)
            );
        }
        $data = array();
        $data['id'] = $item->id;
        $data['name'] = $name;
        $data['alias'] = $alias;
        return $this->update($data);
    }
    
    public function createDir($name, $parent)
    {
        $alias = $this->getAlias($name);
        if(@mkdir($this->getPath($parent).'/'.$alias, 0777) === false) {
            throw new Exception(
                sprintf('Cannot create dir %s', $this->getPath($parent).'/'.$alias)
            );
        }
        $data = array();
        $data['parent_id'] = $parent->id;
        $data['subject_id'] = $this->_cabinet->getActivitySubjectId();
        $data['subject_name'] = $this->_cabinet->getActivitySubjectName();
        $data['name'] = $name;
        $data['alias'] = $alias;
        $data['is_file'] = 0;
        if($parent->user_id) {
        $data['user_id'] = $parent->user_id;
        }
        return $this->insert($data);
    }

    public function createRoot($user)
    {
        if($user) {
            $uRoot = $this->getUsersRoot();
            }
        $rootPath = $this->getRootPath();
        if($user) {
            $rootPath .= $uRoot->alias .'/';
            $rootPath .= $item->user_id .'/';
        }
        if(!file_exists($rootPath)) {
            $oldUmask = umask(0);
            if(@mkdir($rootPath, 0777, true) === false) {
                throw new Exception(sprintf('Cannot create dir %s', $rootPath));
            }
            umask($oldUmask);
        }
        $data = array();
        $data['subject_id'] = $this->_cabinet->getActivitySubjectId();
        $data['subject_name'] = $this->_cabinet->getActivitySubjectName();
        $data['is_file'] = 0;
        if($user && $user->MID) {
            $data['user_id'] = $user->MID;
            $data['parent_id'] = $uRoot->id;
        }
        return $this->insert($data);
    }
    
    public function saveFile($tmpFile, $name, $parent)
    {
        $alias = $this->getAlias($name);
        $file = $this->getPath($parent).'/'.$alias;
        if (!@move_uploaded_file($tmpFile, $file)) {
            return false;
        }
        $data = array();
        $data['parent_id'] = $parent->id;
        $data['subject_id'] = $this->_cabinet->getActivitySubjectId();
        $data['subject_name'] = $this->_cabinet->getActivitySubjectName();
        $data['name'] = $name;
        $data['alias'] = $alias;
        $data['is_file'] = 1;
        if($parent->user_id) {
        $data['user_id'] = $parent->user_id;
        }
        return $this->insert($data);
    }
    
    /**
     * Return or create if not exists root item. If $user not null - for $user
     */
    public function getRoot($user = null)
    {
        $key = $user ? $user->MID : -1;
        if (isset($this->_cache['roots'][$key])) {
            return $this->_cache['roots'][$key];
        }
        
        $where = array();
        $where['is_file = ?'] = 0;
        //$where['subject_id = ?'] = $this->_cabinet->getActivitySubjectId();
        //$subjectName = $this->_cabinet->getActivitySubjectName();
        //if ($subjectName) {
        //    $where['subject_name = ?'] = $subjectName;
        //} else {
        //    $where['subject_name IS NULL'] = null;
        //}
        if($user) {
            $where['user_id = ?'] = $user->MID;
            $where['parent_id = ?'] = $this->getUsersRoot()->id;
        } else {
            $where['user_id IS NULL'] = null;
            $where['parent_id IS NULL'] = null;
        }
        $root = $this->fetchAll($where, '', 1)->current();
        if(!$root || !$root->id) {
            $root = $this->createRoot($user);
        }
        return $this->_cache['roots'][$key] = $root;
    }
    
    public function getUsersRoot()
    {
        if (isset($this->_cache['usersRoot'])) {
            return $this->_cache['usersRoot'];
        }        
        
        $root = $this->getRoot();
        $where = array();
        $where['parent_id = ?'] = $root->id;
        $where['alias = ?'] = 'personal-folders';
        $uRoot = $this->fetchAll($where, '', 1)->current();
        if(!$uRoot || !$uRoot->id) {
            $data = array();
            $data['parent_id'] = $root->id;
            $data['subject_id'] = $this->_cabinet->getActivitySubjectId();
            $data['subject_name'] = $this->_cabinet->getActivitySubjectName();
            $data['name'] = _('Личные папки');
            $data['alias'] = 'personal-folders';
            $data['is_file'] = 0;
            $uRoot = $this->insert($data);
        }
        return $this->_cache['usersRoot'] = $uRoot;
    }
    
    public function getUsersRootContent($onlyFolders = false)
    {
        if (isset($this->_cache['usersRootContent'][$onlyFolders])) {
            return $this->_cache['usersRootContent'][$onlyFolders];
        }        
        
        $where = array();
        if($onlyFolders) {
            $where['is_file = ?'] = 0;
        }
        $where['parent_id = ?'] = $this->getUsersRoot()->id;
        $where['user_id IS NULL'] = null;
        return $this->_cache['usersRootContent'][$onlyFolders] = $this->fetchAll($where);
    }
    
    public function getContent($id)
    {
        if (isset($this->_cache['contents'][$id])) {
            return $this->_cache['contents'][$id];
        }
        $where = array();
        $where['parent_id = ?'] = $id;
        return $this->_cache['contents'][$id] = $this->fetchAll($where);
    }
    
    public function getChilds($item, $checkForCabinet = false)
    {
        if (isset($this->_cache['children'][$item->id])) {
            return $this->_cache['children'][$item->id];
        }
        $where = array();
//        if ($checkForCabinet) {
//            $where['subject_id = ?'] = (int) $this->_cabinet->getActivitySubjectId();
//            if ($this->_cabinet->getActivitySubjectName()) {
//                $where['subject_name = ?'] = (string) $this->_cabinet->getActivitySubjectName();
//            } else {
//                $where['subject_name = ? OR subject_name IS NULL'] = (string) $this->_cabinet->getActivitySubjectName();
//            }
//        }
        $where['is_file = ?'] = 0;
        $where['parent_id = ?'] = $item->id;
        return $this->_cache['children'][$item->id] = $this->fetchAll($where);
    }
    
    public function getParent($item)
    {
        if (is_a($item, 'HM_User_UserModel')) {
            $key = $item->MID; 
        } elseif (is_a($item, 'HM_Storage_StorageFileSystemModel')) {
            $key = $item->id;
        }
        if (isset($this->_cache['parents'][$key])) {
            return $this->_cache['parents'][$key];
        }
        return $this->_cache['parents'][$key] = $this->find($item->parent_id)->current();
    }
    
    public function insert($data)
    {
        $data['created'] = $this->getDateTime();
        $data['changed'] = $this->getDateTime();
        $item = parent::insert($data);
        return $item;
    }
    
    public function update($data)
    {
        $data['changed'] = $this->getDateTime();
        $item = parent::update($data);
        return $item;
    }
    
    public function remove($item)
    {
        $childs = $this->getContent($item->id);
        foreach($childs as $child) {
            $this->remove($child);
        }
        if($item->is_file) {
            $res = @unlink($this->getPath($item));
        } else {
            $res = @rmdir($this->getPath($item));
        }
        if($res === false) {
            return $res;
        }
        $this->delete($item->id);
        return true;
    }
    
    private function _copyItem(HM_Storage_StorageFileSystemModel $src, HM_Storage_StorageFileSystemModel $trg)
    {
        $data = $src->getValues();
        $data['parent_id'] = $trg->id;
        if($trg->user_id) {
        $data['user_id'] = $trg->user_id;
        }
        unset($data['id']);
        return $this->insert($data);
    }
    
    public function copy(HM_Storage_StorageFileSystemModel $src, $trg)
    {
        if($trg instanceof HM_User_UserModel) {
            $trg = $this->getRoot($trg);
        }
        
        if($src->is_file) {
            if (!copy($this->getPath($src), $this->getPath($trg).'/'.$src->alias)) {
                throw new Exception(
                    sprintf('Cannot copy file %s to %s', $this->getPath($src), $this->getPath($trg).'/'.$src->alias)
                );
            }
            $this->_copyItem($src, $trg);
        } else {
            if (!@mkdir($this->getPath($trg).'/'.$src->alias, 0777)) {
                throw new Exception(
                    sprintf('Cannot create dir %s', $this->getPath($trg).'/'.$src->alias)
                );
            }
            $newTrg = $this->_copyItem($src, $trg);
            $items = $this->getContent($src);
            foreach($items as $item) {
                $this->copy($item, $newTrg);
            }
        }
        return true;
    }
    
    public function move(HM_Storage_StorageFileSystemModel $src, $trg)
    {
        if($trg instanceof HM_User_UserModel) {
            $trg = $this->getRoot($trg);
        }
        if (!@rename($this->getPath($src), $this->getPath($trg).'/'.$src->alias)) {
            return false;
        }
        $data = array(
            'parent_id' => $trg->id,
            'id' => $src->id
        );
        if($trg->user_id) {
            $data['user_id'] = $trg->user_id;
        }
        $this->update($data);
        return true;
    }
    
    public function getChildsTree($item, $checkForCabinet = false)
    {
        $dirInfo = $item->getInfo();
        $dirs = $this->getChilds($item, $checkForCabinet);
        foreach($dirs as $dir) {
            $dirInfo['dirs'][] = $this->getChildsTree($dir, $checkForCabinet);
        }
        return $dirInfo;
    }
    
    public function getParents($item, $dirs = array())
    {
        $parent = $this->getParent($item);
        if($parent->id && $parent->parent_id) {
            $dirs []= $parent;
            $dirs = $this->getParents($parent, $dirs);
        }
        return $dirs;
    }
    
    public function getUrl($item)
    {
        $path = array();
        if($item->user_id) {
            $path []= rtrim($this->getRootUrl(), '/');
            $path []= $this->getUsersRoot()->alias;
            $path []= $item->user_id;
        } else {
            $path []= $this->getRootUrl();
    }
        return implode('/', $path) . $this->_getPathParts($item);
    }
    
    private function _getPathParts($item)
    {
        $path = array();
        $parents = $this->getParents($item);
        $parents = array_reverse($parents);
        foreach($parents as $itm) {
            if($item->user_id) {
                if($itm->user_id) {
                    $path []= $itm->alias;
        }
            } else {
                $path []= $itm->alias;
            }
        }
        $path []= $item->alias;
        return implode('/', $path);
    }
    
    public function getPath($item)
    {
        $path = array();
        if($item->user_id) {
            $path []= rtrim($this->getRootPath(), '/');
            $path []= $this->getUsersRoot()->alias;
            $path []= $item->user_id;
        } else {
            $path []= $this->getRootPath();
    }
        return implode('/', $path) . $this->_getPathParts($item);
    }
    
    public function getRootPath()
    {
        $config = Zend_Registry::get('config');
        $path = APPLICATION_PATH.'/../public/'. $config->src->upload->files;
        //$subjectName = $this->_cabinet->getActivitySubjectName();
        //if($subjectName) {
            //$path .= $subjectName.'/';
        //}
        //$path .= $this->_cabinet->getActivitySubjectId().'/';
        return $path;
    }
    
    public function getRootUrl()
    {
        $config = Zend_Registry::get('config');
        // не уверен что на эту переменную можно положиться; точно работает в апаче на солярке
        $path = ($_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $config->url->base . $config->src->upload->files;
        //$subjectName = $this->_cabinet->getActivitySubjectName();
        //if($subjectName) {
        //    $path .= $subjectName.'/';
        //}
        //$path .= $this->_cabinet->getActivitySubjectId().'/';
        return $path;
    }
    
    public function getAlias($str)
    {
        $str = strip_tags(trim($str));
        $str = str_replace(
           array('\\', '/', ':', '*', '?', '"', '<', '>', '|'),
           '',
            $str);
        $str = str_replace(
           array('і', 'ї', 'є', 'І', 'Ї', 'Є'),
           array('i', 'i', 'e', 'I', 'I', 'E'),
            $str);//uk symbols
        return $this->getService('Unmanaged')->translit($str);
    }
}
