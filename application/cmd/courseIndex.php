<?php
include "cmdBootstraping.php";

$services = Zend_Registry::get('serviceContainer');
$config   = Zend_Registry::get('config');
$encoding = $config->charset;

//$lastResourceId = intval(file_get_contents('lastIndexedResource'));


$resources = $services->getService('Course')->fetchAll(array(
    'status = ?' => HM_Course_CourseModel::STATUS_ACTIVE,
));

/*
 * Чтобы не раздувать память будем выводить все сразу
 */

echo '<?xml version="1.0" encoding="utf-8"?>
<sphinx:docset>

<sphinx:schema>
<sphinx:field name="title"/>
<sphinx:field name="description"/>
<sphinx:attr name="nId" type="int" bits="32" default="0"/>
<sphinx:attr name="status" type="int" bits="32" default="0"/>
<sphinx:attr name="index" type="int" bits="8" default="0"/>
</sphinx:schema>';

foreach($resources as $resource){

    echo '<sphinx:document id="' . ($resource->CID*10 + HM_Search_Sphinx::TYPE_COURSE) . '">
<title><![CDATA[ ' . iconv($encoding,'UTF-8',$resource->Title) . ' ]]></title>
<nId>' . $resource->CID . '</nId>
<description><![CDATA[ ' . iconv($encoding,'UTF-8',$resource->Description) . ' ]]></description>
<status>' . iconv($encoding,'UTF-8',$resource->Status) . '</status>
<index>1</index>
</sphinx:document>';

}
echo'</sphinx:docset>';