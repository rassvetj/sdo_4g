<?php
class Storage_IndexController extends HM_Controller_Action_Activity
{
    protected $_subjectName;
    protected $_subjectId;
    protected $_isModerator;
    
    /**
     * Command result to send to client
     *
     * @var array
     **/
    private $_result = array();
    
    /**
     * mapping $_GET['cmd]/$_POST['cmd] to class methods
     *
     * @var array
     **/
    private $_commands = array(
        'open'      => '_open',
        'reload'    => '_reload',
        'mkdir'     => '_mkdir',
        'rename'    => '_rename',
        'paste'     => '_paste',
        'upload'    => '_upload',
        'rm'        => '_rm',
        'ping'      => '_ping',
        'comment'      => '_comment'
        );

    /**
     * object options
     *
     * @var array
     **/
    private $_options = array(
        'disabled'     => array(
            'duplicate',
            'read',
            'edit',
            'archive',
            'extract'
        ),      // list of not allowed commands
        'dotFiles' => true,
        'dirSize'      => true,         // count total directories sizes
        'URL'          => '/',           // root directory URL
        'dateFormat'   => 'j M Y H:i',  // file modification date format
        'mimeDetect'   => 'auto',       // files mimetypes detection method (finfo, mime_content_type, linux (file -ib), bsd (file -Ib), internal (by extensions))
        'uploadAllow'  => array('all'),      // mimetypes which allowed to upload
        'uploadDeny'   => array(
            'application/xml',
            'application/javascript',
            'application/octet-stream',
            'application/x-dosexec',
            'text/x-php',
            'text/html',
            'text/javascript',
            'text/xml',
            'unknown',
        ), // mimetypes which not allowed to upload
        'uploadOrder'  => 'allow,deny', // order to proccess uploadAllow and uploadAllow options
        'fileURL'      => true,         // display file URL in "get info"
    );
    
    /**
     * extensions/mimetypes for _mimetypeDetect = 'internal' 
     *
     * @var array
     **/
    private $_mimeTypes = array(
        //applications
        'ai'    => 'application/postscript',
        'eps'   => 'application/postscript',
        'exe'   => 'application/octet-stream',
        'doc'   => 'application/vnd.ms-word',
        'xls'   => 'application/vnd.ms-excel',
        'ppt'   => 'application/vnd.ms-powerpoint',
        'pps'   => 'application/vnd.ms-powerpoint',
        'pdf'   => 'application/pdf',
        'xml'   => 'application/xml',
        'odt'   => 'application/vnd.oasis.opendocument.text',
        'swf'   => 'application/x-shockwave-flash',
        // archives
        'gz'    => 'application/x-gzip',
        'tgz'   => 'application/x-gzip',
        'bz'    => 'application/x-bzip2',
        'bz2'   => 'application/x-bzip2',
        'tbz'   => 'application/x-bzip2',
        'zip'   => 'application/zip',
        'rar'   => 'application/x-rar',
        'tar'   => 'application/x-tar',
        '7z'    => 'application/x-7z-compressed',
        // texts
        'txt'   => 'text/plain',
        'php'   => 'text/x-php',
        'html'  => 'text/html',
        'htm'   => 'text/html',
        'js'    => 'text/javascript',
        'css'   => 'text/css',
        'rtf'   => 'text/rtf',
        'rtfd'  => 'text/rtfd',
        'py'    => 'text/x-python',
        'java'  => 'text/x-java-source',
        'rb'    => 'text/x-ruby',
        'sh'    => 'text/x-shellscript',
        'pl'    => 'text/x-perl',
        'sql'   => 'text/x-sql',
        // images
        'bmp'   => 'image/x-ms-bmp',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'png'   => 'image/png',
        'tif'   => 'image/tiff',
        'tiff'  => 'image/tiff',
        'tga'   => 'image/x-targa',
        'psd'   => 'image/vnd.adobe.photoshop',
        //audio
        'mp3'   => 'audio/mpeg',
        'mid'   => 'audio/midi',
        'ogg'   => 'audio/ogg',
        'mp4a'  => 'audio/mp4',
        'wav'   => 'audio/wav',
        'wma'   => 'audio/x-ms-wma',
        // video
        'avi'   => 'video/x-msvideo',
        'dv'    => 'video/x-dv',
        'mp4'   => 'video/mp4',
        'mpeg'  => 'video/mpeg',
        'mpg'   => 'video/mpeg',
        'mov'   => 'video/quicktime',
        'wm'    => 'video/x-ms-wmv',
        'flv'   => 'video/x-flv',
        'mkv'   => 'video/x-matroska'
        );
    
    public function preDispatch()
    {
			// откатил все правки по задаче #13020 - неверная постановка задачи + само решение с багами
            parent::preDispatch();
        
        $this->_subjectName = $this->_getParam('subject', '');
        if(empty($this->_subjectName)) {
            $this->_subjectName = null;
        }
        $this->_subjectId = (int) $this->_getParam('subject_id', 0);
        
        // if(!$this->_subjectName || !$this->_subjectId) {
            // $this->_flashMessenger->addMessage(array('message' => _('Не указан виртуальный кабинет'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            // $this->_redirector->gotoSimple('index', 'index', 'index', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId));
        // }
        // $this->_checkPermissions();
        $this->view->subjectName = $this->_subjectName;
        $this->view->subjectId = $this->_subjectId;
        $this->view->isModerator = $this->_isModerator = $this->getService('StorageFileSystem')->isCurrentUserActivityModerator();
    }

    // private function _checkPermissions()
    // {
        // if (!$this->getService('StorageFileSystem')->isCurrentUserActivityModerator()) {
            // $this->_flashMessenger->addMessage(array('message' => _('Вы не являетесь модератором данного вида взаимодействия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            // $this->_redirector->gotoSimple('index', 'index', 'index', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId));
        // }
    // }

    public function indexAction()
    {
        //$this->overrideCheckPermissions();
        //TODO - move to Unit tests it
        // $fsService = $this->getService('StorageFileSystem');
        // $items = $fsService->fetchAll();
        // foreach($items as $item) {
            // $p = $fsService->getPath($item);
            // if(!file_exists($p)) {
                // print_r($item);
                // print $p;
                // exit;
            // }
        // }
    }
    
    public function elfinderAction()
    {
        $config = Zend_Registry::get('config');
        $rootPath = APPLICATION_PATH.'/../public/'. $config->src->upload->files;
        if (!is_dir($rootPath) || !is_writable($rootPath)) {
            exit(json_encode(array('error' => 'Invalid backend configuration')));
        }
        
        $cmd = $_REQUEST['cmd'];
        if (!$cmd && $_SERVER["REQUEST_METHOD"] == 'POST') {
            header("Content-Type: text/html");
            $this->_result['error'] = 'Data exceeds the maximum allowed size';
            exit(json_encode($this->_result));
        }
        
        if ($cmd && (empty($this->_commands[$cmd]) || !method_exists($this, $this->_commands[$cmd]))) {
            exit(json_encode(array('error' => 'Unknown command')));
        }
        
        if (isset($_GET['init'])) {
            $this->_init();
        }
        
        try {
            if ($cmd) {
                $this->{$this->_commands[$cmd]}();
            } else {
                $this->_open();
            }
        } catch(Exception $ex) {
            $this->_result['error'] = $ex->getMessage();
        }
        header("Content-Type: ".($cmd == 'upload' ? 'text/html' : 'application/json'));
        header("Connection: close");
        echo Zend_Json::encode($this->_result);
        exit();
    }
    

    /************************************************************/
    /**                   elFinder commands                    **/
    /************************************************************/
    private function _init()
    {
        $ts = $this->_utime();
        $this->_result['disabled'] = $this->_options['disabled'];
        
        $this->_result['params'] = array(
            'dotFiles'   => $this->_options['dotFiles'],
            'uplMaxSize' => ini_get('upload_max_filesize'),
            'archives'   => array(),
            'extract'    => array(),
            'url'        => $this->_options['fileURL'] ? $this->_options['URL'] : ''
            );
    }
    
    /**
     * Return current dir content to client or output file content to browser
     *
     * @return void
     **/
    private function _open()
    {
        if (isset($_GET['current'])) { // read file
            //TODO add ACL checking
            $item = $this->_getTarget();
            $file = $this->getService('StorageFileSystem')->getPath($item);
            if(!file_exists($file)) {
                header('HTTP/1.x 404 Not Found');
                exit(sprintf(_('Файл %s не найден'), $file));
            }
            $mime  = $this->_mimetype($file);
            $parts = explode('/', $mime);
            // $disp  = $parts[0] == 'image' || $parts[0] == 'text' ? 'inline' : 'attachments';
            $disp  = 'attachments';
            
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private',false);
            
            header('Content-Type: application/force-download');
            header('Content-Type: application/octet-stream');
            header('Content-Type: application/download');
            
            header("Content-Disposition: ".$disp."; filename=".basename($file));
            header("Content-Location: ".$this->getService('StorageFileSystem')->getUrl($item));
            header('Last-Modified: ' . date('D, d M Y H:i:s \G\M\T' , strtotime($item->changed)));
            header('Content-Transfer-Encoding: binary');
            header("Content-Length: ".filesize($file));
            header("Connection: close");
            readfile($file);
            exit();
            
        }
        // enter directory
        $item = $this->_getItem($this->_getParam('target', 0));
        if(!$item) {
            $item = $this->getService('StorageFileSystem')->getRoot((!$this->_subjectId  && !$this->_isModerator)? $this->getService('User')->getCurrentUser() : null);
        }
        $this->_content($item, isset($_GET['tree']));
    }

    private function _getItem($param)
    {
        $item = null;
        if(strstr($param, 'MID') !== false) {
            $param = (int)substr($param, 3);
            $item = $this->getService('User')->find($param)->current();
            $item->user_id = $item->MID;
        } elseif ((int)$param) {
            $item = $this->getService('StorageFileSystem')->find((int)$param)->current();
        }
        if(!$item || (!$item->MID && !$item->id)) {
            $item = null;
        }
        return $item;
    }
    
    private function _getTarget()
    {
        $target = $this->_getParam('target', 0);
        $item = $this->_getItem($target);
        if(!$item) {
            header('HTTP/1.x 404 Not Found');
            exit('Target '.$target.' not found');
        }
        return $item;
    }
    
    /**
     * Rename file/folder
     *
     * @return void
     **/
    private function _rename()
    {
        $item = $this->_getTarget();
        if (!$item) {
            return $this->_result['error'] = 'Invalid parameters';
        }
        if($item instanceof HM_User_UserModel || !$this->_isAllowed($item->user_id, 'write', $item)) {
            return $this->_result['error'] = 'Access denied';
        }
		
        $name = iconv("UTF-8", Zend_Registry::get('config')->charset, trim($_GET['name']));
		
		# фильтрует строку от недопустимых символов в имени
		$name = $this->filterName($name);
		
        if($this->getService('StorageFileSystem')->isExists($item, $name, false)) {
            $this->_result['error'] = 'File or folder with the same name already exists';
            return;
        }
        if (!$this->getService('StorageFileSystem')->rename($name, $item)) {
            $this->_result['error'] = 'Unable to rename file';
        } else {
            // $this->_rmTmb($target); TODO
            $this->_result['select']   = array($item->id);
            $current = $this->_getItem($this->_getParam('current', 0));
            $this->_content($current, !$current->is_file);
        }
    }


    /**
     * Create new folder
     *
     * @return void
     **/
    private function _mkdir()
    {
        $fsService = $this->getService('StorageFileSystem');
        $item = $this->_getItem($_GET['current']);
        if (!$item) {
            return $this->_result['error'] = 'Invalid parameters';
        }
        if($item instanceof HM_User_UserModel) {
            $item = $fsService->getRoot($item);
        }
        if (!$this->_isAllowed($item->user_id, 'write', $item)) {
            return $this->_result['error'] = 'Access denied';
        }
        $name = iconv("UTF-8", Zend_Registry::get('config')->charset, trim($_GET['name']));
		
		
		# фильтрует строку от недопустимых символов в имени
		$name = $this->filterName($name);
		
        if($fsService->isExists($item, $name, false)) {
            $this->_result['error'] = 'File or folder with the same name already exists';
            return;
        }
        $res = $fsService->createDir($name, $item);
        if($res && $res->id) {
            $this->_result['select']  = array($res->id);
            $this->_content($res, true);
        } else {
            $this->_result['error'] = 'Unable to create folder';
        }
    }

    /**
     * Remove files/folders
     *
     * @return void
     **/
    private function _rm()
    {
        if (empty($_GET['targets']) || !is_array($_GET['targets'])) {
            return $this->_result['error'] = 'Invalid parameters';
        }
        $item = $this->_getItem($_GET['current']);
        if (!$item) {
            return $this->_result['error'] = 'Invalid parameters';
        }

        foreach ($_GET['targets'] as $id) {
            $itm = $this->getService('StorageFileSystem')->find((int)$id)->current();
            if (!$this->_isAllowed($itm->user_id, 'rm', $item)) {
                return $this->_result['error'] = 'Access denied';
            }
            $res = $this->getService('StorageFileSystem')->remove($itm);
            if($res === false) {
                return $this->_result['error'] = 'Unable to remove file';
            }
        }
        $this->_content($item, true);
    }

    /**
     * Upload files
     *
     * @return void
     **/
    private function _upload()
    {
        $dir = $this->_getItem($_POST['current']);
        if (!$dir) {
            return $this->_result['error'] = 'Invalid parameters';
        }
        $fsService = $this->getService('StorageFileSystem');
        if($dir instanceof HM_User_UserModel) {
            $dir = $fsService->getRoot($dir);
        }
        if (!$this->_isAllowed($dir->user_id, 'write', $dir)) {
            return $this->_result['error'] = 'Access denied';
        }
        if (empty($_FILES['upload'])) {
            return $this->_result['error'] = 'No file to upload';
        }
        $this->_result['select'] = array();
        $total = 0;
        for ($i=0, $s = count($_FILES['upload']['name']); $i < $s; $i++) { 
            if (!empty($_FILES['upload']['name'][$i])) {
                $total++;
                $name = trim($_FILES['upload']['name'][$i]);
				
				# фильтрует строку от недопустимых символов в имени
				$name = $this->filterName($name);
				
                if ($_FILES['upload']['error'][$i] > 0) {
                    $error = 'Unable to upload file';
                    switch ($_FILES['upload']['error'][$i]) {
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $error = 'File exceeds the maximum allowed filesize';
                            break;
                        case UPLOAD_ERR_EXTENSION:
                            $error = 'Not allowed file type';
                            break;
                    }
                    $this->_errorData($_FILES['upload']['name'][$i], $error);
                } elseif ($fsService->isExists($dir, $name, true)) {
                    $this->_errorData($_FILES['upload']['name'][$i], 'File or folder with the same name already exists');
                } elseif (!$this->_isUploadAllow($_FILES['upload']['name'][$i], $_FILES['upload']['tmp_name'][$i])) {
                    $this->_errorData($_FILES['upload']['name'][$i], 'Not allowed file type');
                } else {
                    $res = $fsService->saveFile($_FILES['upload']['tmp_name'][$i], $name, $dir);
                    if ($res === false) {
                        $this->_errorData($_FILES['upload']['name'][$i], 'Unable to save uploaded file');	
                    }
                }
            }
        }
        
        $errCnt = !empty($this->_result['errorData']) ? count($this->_result['errorData']) : 0;
        
        if ($errCnt == $total) {
            $this->_result['error'] = 'Unable to upload files';
        } else {
            if ($errCnt>0) {
                $this->_result['error'] = 'Some files was not uploaded';	
            }
            $this->_content($dir);
        }
    }

    /**
     * Copy/move files/folders
     *
     * @return void
     **/
    private function _paste()
    {
        $cut = !empty($_GET['cut']);
        $current = $this->_getItem($_GET['current']);
        $dst = $this->_getItem($_GET['dst']);
        $src = $this->_getItem($_GET['src']);
        if(!$current || !$dst || !$src) {
            return $this->_result['error'] = 'Invalid parameters';
        }
        
        if (!$this->_isAllowed($dst->user_id, 'write', $dst)) {
            return $this->_result['error'] = 'Access denied';
        }
        
        $fsService = $this->getService('StorageFileSystem');
        foreach ($_GET['targets'] as $hash) {
            $item = $fsService->find((int)$hash)->current();
            if(!$item || !$item->id) {
                return $this->_result['error'] = sprintf(_('Файл %s не найден'), $hash) && $this->_content($current, true);
            }

            if ($item->id == $dst->id) {
                return $this->_result['error'] = 'Unable to copy into itself' && $this->_content($current, true);
            } elseif ($fsService->isExists($dst, $item->name, $item->is_file)) {
                return $this->_result['error'] = 'File or folder with the same name already exists' && $this->_content($current, true);
            } elseif ($cut && !$this->_isAllowed($item->user_id, 'rm', $item)) {
                return $this->_result['error'] = 'Access denied' && $this->_content($current, true);
            }
            if ($cut) {
                if (!$fsService->move($item, $dst)) {
                    return $this->_result['error'] = 'Unable to move files' && $this->_content($current, true);
                }
                // elseif (!is_dir($f)) {
                    // $this->_rmTmb($f);
                // }
            } elseif (!$fsService->copy($item, $dst)) {
                return $this->_result['error'] = 'Unable to copy files' && $this->_content($current, true);
            }
        }
        $this->_content($current, true);
    }
    
    /**
     * Send header Connection: close. Required by safari to fix bug http://www.webmasterworld.com/macintosh_webmaster/3300569.htm
     *
     * @return void
     **/
    private function _ping()
    {
        exit(header("Connection: close"));
    }
    
    private function _comment()
    {
        $item = $this->_getTarget();
        if (!$item || !isset($_POST['content'])) {
            return $this->_result['error'] = 'Invalid parameters';
        }
        $comment = strip_tags(trim($_POST['content']));
        $comment = iconv("UTF-8", Zend_Registry::get('config')->charset, $comment);
        $comment = substr($comment, 0, 255);
        $this->getService('StorageFileSystem')->update(array(
            'id' => $item->id,
            'description' => $comment
        ));
    }

    /************************************************************/
    /**                    "content" methods                   **/
    /************************************************************/
    /**
     * Set current dir info, content and [dirs tree]
     *
     * @param  HM_Storage_StorageFileSystemModel $dir  current dir
     * @param  bool                              $tree  set dirs tree?
     * @return void
     **/
    private function _content($dir, $tree = false)
    {
        $this->_cwd($dir);
        $this->_cdc($dir);
        if ($tree) {
            /** @var $fsService HM_Storage_StorageFileSystemService  */
            $fsService = $this->getService('StorageFileSystem');
            
            //select root folders tree
            $root = $fsService->getRoot();
            $treeInfo = $this->_info($root);
            $treeInfo['dirs'] = array();
            $dirs = $fsService->getChilds($root, true);

            foreach($dirs as $dir) {
                if($dir->alias != 'personal-folders') {
                    $treeInfo['dirs'][] = $fsService->getChildsTree($dir, true);
                }
            }

            //select root folders
            $uRoot = $fsService->getUsersRoot();
            $treeRootInfo = $this->_info($uRoot);
            $treeRootInfo['dirs'] = array();
            $dirs = $fsService->getUsersRootContent(true);
            foreach($dirs as $dir) {
                $treeRootInfo['dirs'][] = $fsService->getChildsTree($dir);
            }
            $cabinet = $fsService->getCabinet();
            /**
             * должнен быть фильтр всегда для модераторов, независимо от включения сервиса
             * @author Artem Smirnov <tonakai.personal@gmail.com>
             * @date 24.01.2013
             */
            $onlyModer = true;
            if($cabinet  && in_array(ucfirst($cabinet->subject_name), array('Subject', 'Resourse', 'Course'))){
                $subject = $this->getService(ucfirst($cabinet->subject_name))->find($cabinet->subject_id)->current();
                
                if($subject){
                    if(!($subject->services & HM_Activity_ActivityModel::ACTIVITY_LIBRARY)){
                        $onlyModer = true;
                    }

                    //#8051 для модулей в разработке отображать только личные папки Модераторов
                    if ( $cabinet->subject_name == 'course' && $subject->Status == HM_Course_CourseModel::STATUS_DEVELOPED) {
                        $onlyModer = true;
                    }
                }
            }
            else{
                $activity = unserialize($this->getService('Option')->getOption('activity'));
                if(!isset($activity[HM_Activity_ActivityModel::ACTIVITY_LIBRARY])){
                    $onlyModer = true;
                }
            }
            $users = $fsService->getActivityUsers($onlyModer, (!$this->_isModerator && !$this->_subjectId)? true : false);
            foreach($users as $user) {
                $itemInfo = $this->_info($user);
                $itemInfo['dirs'] = array();
                $root = $fsService->getRoot($user);
                if(!$root) {
                    $this->_result['error'] = "Can't create root folder for user ".$user->getName()." (".$user->MID.")";
                    return;
                }
                $dirs = $fsService->getChilds($root);
                foreach($dirs as $dir) {
                    $itemInfo['dirs'][] = $fsService->getChildsTree($dir);
                }
                $treeRootInfo['dirs'][] = $itemInfo;
            }
            
            $treeInfo['dirs'][] = $treeRootInfo;
            $this->_result['tree'] = $treeInfo;
        }
    }

    /**
     * Set current dir info
     *
     * @param  string  $path  current dir path
     * @return void
     **/
    private function _cwd($dir)
    {
        $this->_result['cwd'] = $this->_info($dir);
        $this->_result['cwd']['rel'] = $this->getService('StorageFileSystem')->getUrl($dir);
    }


    /**
     * Set current dir content
     *
     * @param  string  $path  current dir path
     * @return void
     **/
    private function _cdc($item)
    {
        $content = array();
        /** @var $fsService HM_Storage_StorageFileSystemService */
        $fsService = $this->getService('StorageFileSystem');
        if ($item instanceof HM_Storage_StorageFileSystemModel && $item->alias == 'personal-folders') {
            $cabinet = $fsService->getCabinet();
            /**
             * должнен быть фильтр всегда для модераторов, независимо от включения сервиса
             * @author Artem Smirnov <tonakai.personal@gmail.com>
             * @date 24.01.2013
             */
            $onlyModer = true;
            if($cabinet  && in_array(ucfirst($cabinet->subject_name), array('Subject', 'Resourse', 'Course'))){
                $subject = $this->getService(ucfirst($cabinet->subject_name))->find($cabinet->subject_id)->current();
                
                if($subject){
                    if(!($subject->services & HM_Activity_ActivityModel::ACTIVITY_LIBRARY)){
                        $onlyModer = true;
                    }
                }
            }else{
                $activity = unserialize($this->getService('Option')->getOption('activity'));
                if(!isset($activity[HM_Activity_ActivityModel::ACTIVITY_LIBRARY])){
                    $onlyModer = true;
                }
            }
            
            //$items = $fsService->getActivityUsers($onlyModer);
            $items = $fsService->getActivityUsers($onlyModer, (!$this->_isModerator && !$this->_subjectId)? true : false);
            $content = $this->_toArrays($items);
            $tmp = $this->_toArrays($fsService->getUsersRootContent());
            $content[0] = array_merge($content[0], $tmp[0]);
            $content[1] = array_merge($content[1], $tmp[1]);
        } elseif ($item instanceof HM_Storage_StorageFileSystemModel) {
            $items = $fsService->getContent($item->id);
            $content = $this->_toArrays($items, $item);
        } elseif ($item instanceof HM_User_UserModel) {
            $root = $fsService->getRoot($item);
            if(!$root) {
                return $this->_result['error'] = "Can't create root folder for user ".$item->getName()." (".$item->MID.")";
            }
            $items = $fsService->getContent($root->id);
            $content = $this->_toArrays($items);
        }
        
        $this->_result['cdc'] = array_merge($content[0], $content[1]);
    }
    
    private function _toArrays($items, $parent = false)
    {
        $dirs = $files = array();
        foreach($items as $item) {
            if ($item->alias != 'personal-folders' && $parent && !$parent->parent_id && (($item->subject_id != $this->_subjectId) || ($item->subject_name != $this->_subjectName))) {
                continue;
            }
            $info = $this->_info($item);
            if ($info['mime'] == 'directory') {
                $dirs[] = $info;
            } else {
                $files[] = $info;
            }
        }
        return array($dirs, $files);
    }

    /**
     * Return file/folder info
     *
     * @param  string  $path  file path
     * @return array
     **/
    private function _info($item = null)
    {
        $info = array();
        $fsService = $this->getService('StorageFileSystem');
        if ($item instanceof HM_Storage_StorageFileSystemModel) {
            $path = $fsService->getPath($item);
            $info['hash'] = $item->id;
            if(is_null($item->parent_id)) {
                $cabinet = $fsService->getCabinet();
                if ($cabinet->getActivitySubjectId()) {
                    if ($cabinet->subject_name) {
                        $subject = $this->getService(ucfirst($cabinet->subject_name))
                                        ->find($cabinet->subject_id)
                                        ->current();
                    }

                    switch ( $cabinet->subject_name ) {
                        case 'subject' :
                            $info['name'] = ($subject && $subject->name)? $subject->name : APPLICATION_TITLE;
                            break;
                        case 'course' :
                            $info['name'] = ($subject && $subject->Title)? $subject->Title : APPLICATION_TITLE;
                            break;
                        default :
                            $info['name'] = APPLICATION_TITLE;
                    }
                } else {
                    $info['name'] = APPLICATION_TITLE;
                }
            } else {
            $info['name'] = htmlspecialchars($item->getName());
            }
            $info['mime'] = ($item->is_file) ? $this->_mimetype($path) : 'directory';
            $info['date'] = date($this->_options['dateFormat'], strtotime($item->changed));
            $info['size'] = ($item->is_file) ? $this->_fileSeize($path) : 0;//$this->_dirSize($path)
            $info['comment'] = iconv(Zend_Registry::get('config')->charset, "UTF-8", $item->description.'');
            $info['read'] = true;
            $info['write'] = $this->_isAllowed($item->user_id, 'write', $item);
            $info['rm'] = $this->_isAllowed($item->user_id, 'rm', $item);
            if($item->is_file) {
                $info['url'] = $fsService->getUrl($item);
                $pathInfo = pathinfo($info['url']);
                if (in_array($pathInfo['extension'], array('jpg', 'jpeg', 'gif', 'bmp', 'png'))) {
                    $info['tmb'] = $info['url'];
                }
            }
        } elseif ($item instanceof HM_User_UserModel) {
            $item->user_id = $item->MID;
            $path = $fsService->getRootPath();
            $uRoot = $fsService->getUsersRoot();
            $path .= $uRoot->alias .'/';
            $path .= $item->MID .'/';
            if(!file_exists($path)) {
                if(!@mkdir($path, 0777, true)) {
                    return $this->_result['error'] = 'Invalid backend configuration';
                }
            }
            $stat = stat($path);
            $info['hash'] = 'MID'.$item->MID;
            $info['name'] = iconv(Zend_Registry::get('config')->charset, "UTF-8", $item->getName());
            $info['mime'] = 'directory';
            $info['date'] = date($this->_options['dateFormat'], $stat['mtime']);
            $info['size'] = 0;//$this->_dirSize($path)
            $info['read'] = true;
            $info['write'] = $this->_isAllowed($item->MID, 'write', $item);
            $info['rm']    = false;
                }
        return $info;
    }

    /************************************************************/
    /**                          utilites                      **/
    /************************************************************/
    /**
     * Return file mimetype
     *
     * @param  string  $path  file path
     * @return string
     **/
    private function _mimetype($path)
    {
        if (empty($this->_options['mimeDetect']) || $this->_options['mimeDetect'] == 'auto') {
            $this->_options['mimeDetect'] = $this->_getMimeDetect();
        }
        
        switch ($this->_options['mimeDetect']) {
            case 'finfo':
                if (empty($this->_finfo)) {
                    $this->_finfo = finfo_open(FILEINFO_MIME);
                }
                $type = @finfo_file($this->_finfo, $path);
                break;
            case 'php':   
                $type = mime_content_type($path);
                break;
            case 'linux':  
                $type = exec('file -ib '.escapeshellarg($path));
                break;
            case 'bsd':   
                $type = exec('file -Ib '.escapeshellarg($path));
                break;
            default:
                $pinfo = pathinfo($path); 
                $ext = isset($pinfo['extension']) ? strtolower($pinfo['extension']) : '';
                $type = isset($this->_mimeTypes[$ext]) ? $this->_mimeTypes[$ext] : 'unknown;';
        }
        $type = explode(';', $type); 
        
        if ($this->_options['mimeDetect'] != 'internal' && $type[0] == 'application/octet-stream') {
            $pinfo = pathinfo($path); 
            $ext = isset($pinfo['extension']) ? strtolower($pinfo['extension']) : '';
            if (!empty($ext) && !empty($this->_mimeTypes[$ext])) {
                $type[0] = $this->_mimeTypes[$ext];
            }
        }
        
        return $type[0];
    }
    
    /**
     * Return mimetype detect method name
     *
     * @return string
     **/
    private function _getMimeDetect()
    {
        if (class_exists('finfo')) {
            return 'finfo';
        } elseif (function_exists('mime_content_type') && (mime_content_type(__FILE__) == 'text/x-php' || mime_content_type(__FILE__) == 'text/x-c++')) {
            return 'mime_content_type';
        } elseif (function_exists('exec')) {
            $type = exec('file -ib '.escapeshellarg(__FILE__));
            if (0 === strpos($type, 'text/x-php') || 0 === strpos($type, 'text/x-c++'))
            {
                return 'linux';
            }
            $type = exec('file -Ib '.escapeshellarg(__FILE__));
            if (0 === strpos($type, 'text/x-php') || 0 === strpos($type, 'text/x-c++'))
            {
                return 'bsd';
            }
        }
        return 'internal';
    }

    /**
     * Paack error message in $this->_result['errorData']
     *
     * @param string  $path  path to file
     * @param string  $msg   error message
     * @return bool always false
     **/
    private function _errorData($path, $msg)
    {
        $path = preg_replace('|^'.preg_quote($this->_options['root']).'|', $this->_fakeRoot, $path);
        if (!isset($this->_result['errorData'])) {
            $this->_result['errorData'] = array();
        }
        $this->_result['errorData'][$path] = $msg;
        return false;
    }	

    private function _utime()
    {
        $time = explode(" ", microtime());
        return (double)$time[1] + (double)$time[0];
    }

    /**
     * Return true if file's mimetype is allowed for upload
     *
     * @param  string  $name    file name
     * @param  string  $tmpName uploaded file tmp name
     * @return bool
     **/
    private function _isUploadAllow($name, $tmpName)
    {
        $mime  = $this->_mimetype($this->_options['mimeDetect'] != 'internal' ? $tmpName : $name);
        $allow = false;
        $deny  = false;

        if (in_array('all', $this->_options['uploadAllow'])) {
            $allow = true;
        } else {
            foreach ($this->_options['uploadAllow'] as $type) {
                if (0 === strpos($mime, $type)) {
                    $allow = true;
                    break;
                }
            }
        }
        
        if (in_array('all', $this->_options['uploadDeny'])) {
            $deny = true;
        } else {
            foreach ($this->_options['uploadDeny'] as $type) {
                if (0 === strpos($mime, $type)) {
                    $deny = true;
                    break;
                }
            }
        }
        return 0 === strpos($this->_options['uploadOrder'], 'allow') ? $allow && !$deny : $allow || !$deny;
    }
    
    /**
     * Return true if requeired action allowed to file/folder on file system
     *
     * @param  string  $path    file/folder path
     * @param  string  $action  action name (read/write/rm)
     * @return void
     **/
    private function _isAllowedByFs($path, $action) {
        
        switch ($action) {
            case 'read':
                if (!is_readable($path)) {
                    return false;
                }
                break;
            case 'write':
                if (!is_writable($path)) {
                    return false;
                }
                break;
            case 'rm':
                if (!is_writable(dirname($path))) {
                    return false;
                }
                break;
        }

        $path = substr($path, strlen($this->_options['root'])+1);
        foreach ($this->_options['perms'] as $regex => $rules) {
            
            if (preg_match($regex, $path)) {
                if (isset($rules[$action])) {
                    return $rules[$action];
                }
            }
        }
        return isset($this->_options['defaults'][$action]) ? $this->_options['defaults'][$action] : false;
    }
    
    /**
     * Return true if requeired action allowed to file/folder
     *
     * @param  string  $path    file/folder path
     * @param  string  $action  action name (read/write/rm)
     * @return void
     **/
    private function _isAllowed($userId, $action, $item = null)
    {
        $fsService = $this->getService('StorageFileSystem');
        switch ($action) {
            case 'read':
                    return true;
            case 'write':
                if (null !== $item) {
                    return ((!$item->user_id && $fsService->isCurrentUserActivityModerator()) ||
                        ($item->user_id && $userId == $this->getService('User')->getCurrentUserId()));

                } else {
                    return ($fsService->isCurrentUserActivityModerator() ||
                        $userId == $this->getService('User')->getCurrentUserId());
                }
            case 'rm':
                if (null !== $item) {
                    return ((!$item->user_id && $fsService->isCurrentUserActivityModerator()) ||
                        ($item->user_id && $userId == $this->getService('User')->getCurrentUserId()));
                } else {
                    return ($fsService->isCurrentUserActivityModerator() ||
                        $userId == $this->getService('User')->getCurrentUserId());
                }
            default:
                return false;
        }
    }
    
    /**
     * Count total directory size if this allowed in options
     *
     * @param  string  $path  directory path
     * @return int
     **/
    // private function _dirSize($path)
    // {
        // $size = 0;
        // if (!$this->_options['dirSize'] || !$this->_isAllowedByFs($path, 'read')) {
            // return filesize($path);
        // } 
        // if (!isset($this->_options['du'])) {
            // $this->_options['du'] = function_exists('exec')
                // ? exec('du -h '.escapeshellarg(__FILE__), $o, $s) > 0 && $s == 0
                // : false;
        // }
        // if ($this->_options['du']) {
            // $size = intval(exec('du -k '.escapeshellarg($path)))*1024;
        // } else {
            // $ls = scandir($path);
            // for ($i=0; $i < count($ls); $i++) { 
                // if ($this->_isAccepted($ls[$i])) {
                    // $p = $path.DIRECTORY_SEPARATOR.$ls[$i];
                    // $size += filetype($p) == 'dir' && $this->_   isAllowedByFs($p, 'read') ? $this->_dirSize($p) : filesize($p);
                // }
            // }
        // }
        // return $size;
    // }
    
    private function _fileSeize($path)
    {
        if(is_dir($path)) {
            throw new Exception('It is not file');
        }
        @$stat = stat($path);
        return $stat['size'];
    }
	
	
	
	/*
		Удаляет из строки недопустимые для имени папки или файла символы, такие, как:
		#, %,&,*,|,\,:,",<,>,?,/,пробел в конце или начале,nul,aux,con,com1,,lpt1		
	*/
	private function filterName($name){
		$name = trim($name, '.');
		
		return preg_replace(
			array('/[^a-zA-ZА-Яа-я0-9 -.]/u', '/[-]+/', '/^-|-$/'),
			array('', '-', ''),
			$name
		); 		
	}
	
	
	
}