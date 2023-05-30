<?php
class HM_Resource_Revision_RevisionService extends HM_Service_Abstract
{
    public function insert($resourceId)
    {
        $resource = $this->getService('Resource')->getOne($this->getService('Resource')->find($resourceId));
        $revisionData = array();
        foreach($data = $resource->getData() as $key => $value) {
            if (!in_array($key, self::getRevisionableAttributes())) continue;
            if ($value === null) continue;
            $revisionData[$key] = $value;
        }

        $revision = parent::insert($revisionData);

        if ($resource->type == HM_Resource_ResourceModel::TYPE_EXTERNAL) {
            $resoursePath = realpath(Zend_Registry::get('config')->path->upload->resource);
            if (is_readable($resourceFile = $resoursePath . '/' . $resource->resource_id)) {
                $revisionFile = $resoursePath . '/revision/' . $revision->revision_id;
                copy($resourceFile, $revisionFile);
            }
            
            // это псевдо-ресурсы, привязанные к текущей версии; их нужно скрыть 
            // файлы удалять/перемещать не нужно, они так и остаются в resources
            $this->getService('Resource')->updateWhere(array(
                'parent_revision_id' => $revision->revision_id,
            ), array(
                'parent_id = ?' => $resourceId,
                'parent_revision_id = ?' => 0,
            ));
        }

        if ($resource->type == HM_Resource_ResourceModel::TYPE_FILESET) {
            $resourcePath = realpath(Zend_Registry::get('config')->path->upload->public_resource) . '/' . $resource->resource_id . '/';
            $revisionPath = realpath(Zend_Registry::get('config')->path->upload->public_resource) . '/revision/' . $revision->revision_id . '/';
            if (!is_dir($revisionPath)) {
                mkdir($revisionPath, 0755);
            }
            try {
                $this->getService('Course')->copyDir($resourcePath, $revisionPath);
                $this->getService('Course')->emptyDir($resourcePath);
            } catch (HM_Exception $e) {
                // oops..
            }
        }

        return $revision;
    }
    
    public function restore($revisionId) 
    {
        if (count($collection = $this->findDependence('Resource', $revisionId))) {
            
            $revision = $collection->current();
            $resource = $revision->resource->current();
            try {
                if ($resource->type == HM_Resource_ResourceModel::TYPE_EXTERNAL) {
                    $resoursePath = realpath(Zend_Registry::get('config')->path->upload->resource);
                    if (is_readable($resourceFile = $resoursePath . '/' . $resource->resource_id)) {
                        $revisionFile = $resoursePath . '/revision/' . $revision->revision_id;
                        copy($revisionFile, $resourceFile);
                    }
                    
                    // это псевдо-ресурсы, привязанные к текущей версии; их нужно тоже скрыть 
                    $this->getService('Resource')->updateWhere(array(
                        'parent_revision_id' => 0,
                    ), array(
                        'parent_id = ?' => $resource->resource_id,
                        'parent_revision_id = ?' => $revision->revision_id,
                    ));
                }
        
                if ($resource->type == HM_Resource_ResourceModel::TYPE_FILESET) {
                    $resourcePath = realpath(Zend_Registry::get('config')->path->upload->public_resource) . '/' . $resource->resource_id . '/';
                    $revisionPath = realpath(Zend_Registry::get('config')->path->upload->public_resource) . '/revision/' . $revision->revision_id . '/';
    
                    $this->getService('Course')->emptyDir($resourcePath);
                    $this->getService('Course')->copyDir($revisionPath, $resourcePath);
                }
                
            } catch (HM_Exception $e) {
                return false;
            }
            
            $data = $revision->getValues();
            $data['resource_id'] = $resource->resource_id;
            unset($data['revision_id']);
            $resource = $this->getService('Resource')->update($data);  
            
            $this->delete($revisionId);
            $revisionPath = realpath(Zend_Registry::get('config')->path->upload->public_resource) . '/revision/' . $revisionId . '/';
            $this->getService('Course')->removeDir($revisionPath);
            
            return $resource;
        }    
    }    

    
    public function delete($revisionId) 
    {
        if (count($collection = $this->findDependence(array('Resource', 'DependentRevision'), $revisionId))) {
            
            $revision = $collection->current();
            $resource = $revision->resource->current();
            try {
                if ($resource->type == HM_Resource_ResourceModel::TYPE_EXTERNAL) {
                    $revisionFile = realpath(Zend_Registry::get('config')->path->upload->resource) . '/revision/' . $revision->revision_id;
                    @unlink($revisionFile);
                    
                    // это псевдо-ресурсы, привязанные к текущей версии; их нужно тоже удалить
                    if (count($revision->dependentRevisions)) {
                        foreach ($revision->dependentRevisions as $dependentRevision) {
                            $this->getService('Resource')->delete($dependentRevision->resource_id);
                        }
                    } 
                }
        
                if ($resource->type == HM_Resource_ResourceModel::TYPE_FILESET) {
                    $revisionPath = realpath(Zend_Registry::get('config')->path->upload->public_resource) . '/revision/' . $revision->revision_id . '/';
                    $this->getService('Course')->removeDir($revisionPath);
                }
                
            } catch (HM_Exception $e) {
                return false;
            }
           
            return parent::delete($revisionId);
        }    
    }    

    static public function getRevisionableAttributes()
    {
        return array(
            'resource_id',
            'url',
            'volume',
            'filename',
            'filetype',
            'content',
            'updated',
            'created_by',
        );
    }
}