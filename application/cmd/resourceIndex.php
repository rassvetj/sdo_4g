<?php
include "cmdBootstraping.php";
/*
 * @TODO Сделать kill-list
 * 
 */
$services = Zend_Registry::get('serviceContainer');
$config   = Zend_Registry::get('config');
$encoding = $config->charset;

$lastResourceId = intval(file_get_contents('lastIndexedResource'));

$resources = $services->getService('Resource')->fetchAll(array(
    'resource_id > ?' => $lastResourceId,
    'parent_id = ?' => 0,
    'status = ?' => HM_Resource_ResourceModel::STATUS_PUBLISHED,
));

/*
 * Чтобы не раздувать память будем выводить все сразу
 */

echo '<?xml version="1.0" encoding="utf-8"?>
<sphinx:docset>

<sphinx:schema>
<sphinx:field name="title"/> 
<sphinx:field name="description"/>
<sphinx:field name="keywords"/>
<sphinx:field name="filename"/>
<sphinx:field name="content"/>
<sphinx:attr name="nId" type="int" bits="32" default="0"/>
<sphinx:attr name="created_by" type="int" bits="32" default="0"/>
<sphinx:attr name="subject_id" type="int" bits="32" default="0"/>
<sphinx:attr name="status" type="int" bits="32" default="0"/>
<sphinx:attr name="location" type="int" bits="32" default="0"/>
<sphinx:attr name="index" type="int" bits="8" default="0"/>
</sphinx:schema>';

foreach($resources as $resource){

    ob_start();
    $services->getService('Resource')->printContent($resource);
    $content = ob_get_clean();

    echo '<sphinx:document id="' . ($resource->resource_id * 10 + HM_Search_Sphinx::TYPE_RESOURCE) . '">
<content><![CDATA[ ' . $content . ' ]]></content>
<title><![CDATA[ ' . iconv($encoding,'UTF-8',$resource->title) . ' ]]></title>
<description><![CDATA[ ' . iconv($encoding,'UTF-8',$resource->description) . ' ]]></description>
<keywords><![CDATA[ ' . iconv($encoding,'UTF-8',$resource->keywords) . ' ]]></keywords>
<filename><![CDATA[ ' . iconv($encoding,'UTF-8',$resource->filename) . ' ]]></filename>
<nId>' . $resource->resource_id . '</nId>
<created_by>' . $resource->created_by . '</created_by>
<subject_id>' . $resource->subject_id . '</subject_id>
<status>' . $resource->status . '</status>
<location>' . $resource->location . '</location>
<index>' . HM_Search_Sphinx::TYPE_RESOURCE . '</index>
</sphinx:document>';
}
echo'</sphinx:docset>';