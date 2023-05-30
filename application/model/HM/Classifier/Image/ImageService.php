<?php
class HM_Classifier_Image_ImageService extends HM_Service_Abstract
{

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
        //$getpath = $this->getPath($config->path->upload->classifier, $userId);
        $maxFilesCount = (int) $config->path->upload->maxfilescount;

        return '/'. $config->src->upload->classifier . '/' . floor($userId / $maxFilesCount) . '/' . $userId . '.jpg';

        //$glob = glob($getpath . $userId .'.*');
        //foreach($glob as $value){
        //    return floor($userId / $maxFilesCount) . '/' . basename($value);
        //}
        return false;

    }




}