<?php
class HM_File_Transfer_Adapter_Flash extends Zend_File_Transfer_Adapter_Abstract
{
    protected $_populatedFiles = array();
    protected $_deletedFiles = array();

    public function __construct($options = array())
    {
        if (ini_get('file_uploads') == false) {
            require_once 'Zend/File/Transfer/Exception.php';
            throw new Zend_File_Transfer_Exception('File uploads are not allowed in your php config!');
        }
        $this->setOptions($options);
        $this->_prepareFiles();
        $this->addPrefixPath('HM_Validate_File', 'HM/Validate/File', 'validate');
        $this->addValidator('FlashUpload', false, $this->_files);

    }

    public function setValidators(array $validators, $files = null)
    {
        $this->clearValidators();
        $this->addValidator('FlashUpload', false, $this->_files);
        return $this->addValidators($validators, $files);
    }

    public function removeValidator($name)
    {
        if ($name == 'FlashUpload') {
            return $this;
        }
        return parent::removeValidator($name);
    }

    public function clearValidators()
    {
        parent::clearValidators();
        $this->addValidator('FlashUpload', false, $this->_files);
        return $this;
    }

    public function send($options = null)
    {
        require_once 'Zend/File/Transfer/Exception.php';
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    public function isValid($files = null)
    {
        if (empty($this->_files) && $this->_options['ignoreNoFile']) {
            return true;
        }

        if (empty($this->_files)) {
            if (is_array($files)) {
                $files = current($files);
            }

            $temp = array($files => array(
                'name'  => $files,
                'error' => 4));
            $validator = $this->_validators['HM_Validate_File_FlashUpload'];
            $validator->setTranslator($this->getTranslator());
            $validator->setFiles($temp)
                      ->isValid($files, null);
            $this->_messages += $validator->getMessages();
            return false;
        }

        $return = parent::isValid($files);

        return $return;
    }

    public function updatePopulated($populatedFiles = array(), $doUnlink)
    {
        foreach ($populatedFiles as $populatedFile) {
            if (!in_array($populatedFile->getId(), $this->_populatedFiles)) {
                if (file_exists($path = $populatedFile->getPath())) {
                    if ($doUnlink) unlink($path);
                    $this->_deletedFiles[$populatedFile->getId()] = $populatedFile;
                }
            }
        }
        return $this->_deletedFiles;
    }

    public function receive($files = null)
    {
        if (!$this->isValid($files)) {
            return false;
        }

        $check = $this->_getFiles($files);
        foreach ($check as $file => $content) {
            if (!$content['received']) {
                if (!is_file($content['tmp_name'])) {
                     if ($content['options']['ignoreNoFile']) {
                        $this->_files[$file]['received'] = true;
                        $this->_files[$file]['filtered'] = true;
                        continue;
                    }

                    $this->_files[$file]['received'] = false;
                    return false;
                }

                $this->_files[$file]['received'] = true;
                
                /*
                $directory   = '';
                $destination = $this->getDestination($file);
                if ($destination !== null) {
                    $directory = $destination . DIRECTORY_SEPARATOR;
                }

                $filename = $directory . $content['name'];
                $rename   = $this->getFilter('Rename');
                if ($rename !== null) {
                    $tmp = $rename->getNewName($content['tmp_name']);
                    if ($tmp != $content['tmp_name']) {
                        $filename = $tmp;
                    }

                    if (dirname($filename) == '.') {
                        $filename = $directory . $filename;
                    }

                    $key = array_search(get_class($rename), $this->_files[$file]['filters']);
                    unset($this->_files[$file]['filters'][$key]);
                }
                
                //if (!rename($content['tmp_name'], $filename)) {
                if (!is_file($content['tmp_name'])) {
                    if ($content['options']['ignoreNoFile']) {
                        $this->_files[$file]['received'] = true;
                        $this->_files[$file]['filtered'] = true;
                        continue;
                    }

                    $this->_files[$file]['received'] = false;
                    return false;
                }

                if ($rename !== null) {
                    $this->_files[$file]['destination'] = dirname($filename);
                    $this->_files[$file]['name']        = basename($filename);
                }

                $this->_files[$file]['tmp_name'] = $filename;
                $this->_files[$file]['received'] = true;
                */
            }

            if (!$content['filtered']) {
                if (!$this->_filter($file)) {
                    $this->_files[$file]['filtered'] = false;
                    return false;
                }

                $this->_files[$file]['filtered'] = true;
            }
        }

        return true;
    }

    public function isSent($files = null)
    {
        require_once 'Zend/File/Transfer/Exception.php';
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    public function isReceived($files = null)
    {
        $files = $this->_getFiles($files, false, true);
        if (empty($files)) {
            return false;
        }

        foreach ($files as $content) {
            if ($content['received'] !== true) {
                return false;
            }
        }

        return true;
    }

    public function isFiltered($files = null)
    {
        $files = $this->_getFiles($files, false, true);
        if (empty($files)) {
            return false;
        }

        foreach ($files as $content) {
            if ($content['filtered'] !== true) {
                return false;
            }
        }

        return true;
    }

    public function isUploaded($files = null)
    {
        $files = $this->_getFiles($files, false, true);
        if (empty($files)) {
            return false;
        }

        foreach ($files as $file) {
            if (empty($file['name'])) {
                return false;
            }
        }

        return true;
    }

    public function getFileName($file = null, $path = true)
    {
        $files     = $this->_getFiles($file, true, true);
        $result    = array();
        $directory = "";
        foreach($files as $file) {
            if (empty($this->_files[$file]['name'])) {
                continue;
            }

            $result[$file] = $this->_files[$file]['tmp_name'];
            if ($path !== true) {
                $result[$file] = basename($this->_files[$file]['tmp_name']);
            }

        }

        if (count($result) == 1) {
            return current($result);
        }

        return $result;
    }

    protected function _prepareFiles()
    {
        $this->_files = array();

        $name = $this->_options['name'];
        if (strlen($name) && strlen($_REQUEST[$name])) {
            
            // этот массив нужен, чтобы в receive() не только получить новые файлы, но и удалить старые если их удалил юзер 
            if (is_array($populatedFiles = $_REQUEST["{$name}-populate"])) {
                foreach ($populatedFiles as $populatedFile) {
                    if (!empty($populatedFile)) {
                        $this->_populatedFiles[] = $populatedFile;
                    }
                }
            }
            
            $session = new Zend_Session_Namespace('upload');
            if (isset($session->{$_REQUEST[$name]})) {
                if (count($session->{$_REQUEST[$name]}) > 1) {
                    foreach($session->{$_REQUEST[$name]} as $number => $file) {
                        $_FILES[$name]['name'][$number] = $file['name'];
                        $_FILES[$name]['tmp_name'][$number] = $file['tmp_name'];
                        $_FILES[$name]['error'][$number] = $file['error'];
                        $_FILES[$name]['size'][$number] = $file['size'];

                        $mimetype = $this->_detectMimeType($file);
                        $_FILES[$name]['type'][$number] = $mimetype;

                        $filesize = $this->_detectFileSize($file);
                        $_FILES[$name]['size'][$number] = $filesize;

                        $this->_files[$name]['name'] = $name;
                        $this->_files[$name]['multifiles'][$number] = $name.'_'.$number.'_';

                        $this->_files[$name.'_'.$number.'_'] = $file;
                        $this->_files[$name.'_'.$number.'_']['type'] = $mimetype;
                        $this->_files[$name.'_'.$number.'_']['size'] = $filesize;
                        $this->_files[$name.'_'.$number.'_']['options']   = $this->_options;
                        $this->_files[$name.'_'.$number.'_']['validated'] = false;
                        $this->_files[$name.'_'.$number.'_']['received']  = false;
                        $this->_files[$name.'_'.$number.'_']['filtered']  = false;
                            
                    }
                } else {
                    $this->_files[$name] = $_FILES[$name] = $session->{$_REQUEST[$name]}[0];
                    $this->_files[$name]['options']   = $this->_options;
                    $this->_files[$name]['validated'] = false;
                    $this->_files[$name]['received']  = false;
                    $this->_files[$name]['filtered']  = false;

                    $mimetype = $this->_detectMimeType($this->_files[$name]);
                    $this->_files[$name]['type'] = $_FILES[$name]['type'] = $mimetype;

                    $filesize = $this->_detectFileSize($this->_files[$name]);
                    $this->_files[$name]['size'] = $_FILES[$name]['size'] = $filesize;

                }
            } else {
                /*
                $this->_files[$name] = $_FILES[$name] = array(
                    'name' => '',
                    'type' => '',
                    'tmp_name' => '',
                    'size' => '',
                    'error' => 4
                );
                $this->_files[$name]['options']   = $this->_options;
                $this->_files[$name]['validated'] = false;
                $this->_files[$name]['received']  = false;
                $this->_files[$name]['filtered']  = false;
                */

            }
        }

        return $this;
    }

    protected function _getTmpDir()
    {
        if (null === $this->_tmpDir) {
            $tmpdir = array();

            if (function_exists('sys_get_temp_dir')) {
                $tmpdir[] = sys_get_temp_dir();
            }

            if (!empty($_ENV['TMP'])) {
                $tmpdir[] = realpath($_ENV['TMP']);
            }

            if (!empty($_ENV['TMPDIR'])) {
                $tmpdir[] = realpath($_ENV['TMPDIR']);
            }

            if (!empty($_ENV['TEMP'])) {
                $tmpdir[] = realpath($_ENV['TEMP']);
            }

            $upload = ini_get('upload_tmp_dir');
            if ($upload) {
                $tmpdir[] = realpath($upload);
            }

            $tmpdir[] = realpath(APPLICATION_PATH . '/../public/unmanaged/temp/');             

            foreach($tmpdir as $directory) {
                if ($this->_isPathWriteable($directory)) {
                    $this->_tmpDir = $directory;
                }
            }

            if (empty($this->_tmpDir)) {
                // Attemp to detect by creating a temporary file
                $tempFile = tempnam(md5(uniqid(rand(), TRUE)), '');
                if ($tempFile) {
                    $this->_tmpDir = realpath(dirname($tempFile));
                    unlink($tempFile);
                } else {
                    require_once 'Zend/File/Transfer/Exception.php';
                    throw new Zend_File_Transfer_Exception('Could not determine a temporary directory');
                }
            }

            $this->_tmpDir = rtrim($this->_tmpDir, "/\\");
        }
        return $this->_tmpDir;
    }

    public function setDestination($destination, $files = null)
    {
        $orig = $files;
        $destination = rtrim($destination, "/\\");
        if (!is_dir($destination)) {
            require_once 'Zend/File/Transfer/Exception.php';
            throw new Zend_File_Transfer_Exception('The given destination is not a directory or does not exist');
        }

        if (!is_writable($destination)) {
            require_once 'Zend/File/Transfer/Exception.php';
            throw new Zend_File_Transfer_Exception('The given destination is not writeable');
        }

        if ($files === null) {
            foreach ($this->_files as $file => $content) {
                if (isset($this->_files[$file]))
                $this->_files[$file]['destination'] = $destination;
            }
        } else {
            $files = $this->_getFiles($files, true, true);
            if (empty($files) and is_string($orig)) {
                if (isset($this->_files[$orig]))
                $this->_files[$orig]['destination'] = $destination;
            }

            foreach ($files as $file) {
                if (isset($this->_files[$file]))
                $this->_files[$file]['destination'] = $destination;
            }
        }

        return $this;
    }


}