<?php
class HM_Files_Videoblock_VideoblockService extends HM_Service_Abstract
{
    private $videoListCache=null;

    public function getVideoList(){
        if ($this->videoListCache===null){
            $this->updateVideoListCache();
        }
        return $this->videoListCache;
    }

    private function updateVideoListCache(){
        $this->videoListCache=array();
        //$res=$this->fetchAllDependence('Files',1);
        $select=$this->getSelect();
        $select->from(array('v'=>'videoblock'))
            ->joinLeft(array('f' => 'files'),
                'v.file_id = f.file_id',array(
                    'file_id' => 'f.file_id',
                    'filename' => 'f.name',
                    'path' =>'f.path',
                    'name' => 'v.name'));
        $rows=$select->query()->fetchAll();
        foreach($rows as $item){
            $this->videoListCache += array($item['file_id'] => array(
                'name' => $item['name'],
                'filename' => $item['filename'],
                'path' =>$item['path']));
        }
    }

    public function getFilePath($id){
        if ($this->videoListCache===null){
            $this->updateVideoListCache();
        }
        if (isset($this->videoListCache[$id])) return $this->videoListCache[$id]['path'];
    }
    public function delete($id){
        $res=parent::delete($id);
        if($res>0) $this->updateVideoListCache();
        return $res;
    }
    public function insert($data){
        $res=parent::insert($data);
        if($res) $this->updateVideoListCache();
        return $res;
    }
    public function update($data){
        $res=parent::update($data);
        if($res) $this->updateVideoListCache();
        return $res;
    }
}