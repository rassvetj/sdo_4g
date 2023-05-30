<?php
include "cmdBootstraping.php";

$services = Zend_Registry::get('serviceContainer');
$config   = Zend_Registry::get('config');
$encoding = $config->charset;

$opts = new Zend_Console_Getopt(
  array(
    'new|n=s'    => 'new Service Layer name like "Resource"',
    'extends|e-s' => 'Extended service layer like "Subject"',
    'only-model|om'   => 'Create model class without service Layer',
  	'table-name|tn-s'   => 'Table name',
    'help|h' => 'Help'
  )
);


try{
    $opts->parse();
}
catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage();
    exit;
}

$newService = $opts->getOption('new');
$extends = $opts->getOption('extends');
$tableName = $opts->getOption('table-name');
$onlyModel = $opts->getOption('only-model');

if($extends !=""){
    $service = $services->getService($extends);   
    if($service){
        $extendedClass = get_class($service);
    }else{
        echo 'Wrong extended class passed.';
        exit;
    }
}

$modelPath = '../model/';

if($extendedClass == ""){
    $extendedClass = 'HM_AbstractService';
}

$explode = explode('_', $extendedClass);
array_pop($explode);
$prefix = implode('_', $explode);
$path = $modelPath . '/' . implode('/', $explode). '/' . $newService . '/';
mkdir($path, null, true);

$serviceContent = <<<SERVICE
class {prefix}_{className}_{className}Service extends {extends}
{


}
SERVICE;

$modelContent = <<<SERVICE
class {prefix}_{className}_{className}Model extends {extends}
{


}
SERVICE;

$tableContent = <<<SERVICE
class {prefix}_{className}_{className}Table extends HM_Db_Table
{
	\$name = "{tableName}";
	\$primary = "";

}
SERVICE;

$mapperContent = <<<SERVICE
class {prefix}_{className}_{className}Mapper extends HM_Mapper_Abstract
{
    
}
SERVICE;

if($extendedClass == 'HM_AbstractService'){
    $extendedClass = 'HM_Service_Abstract';
}

if($extendedClass == 'HM_Service_Abstract'){
    $extendedModel = 'HM_Model_Abstract';
}else{
    $extendedModel = $prefix . '_' . array_pop($explode). 'Model';
}

if($onlyModel != true){
    file_put_contents($path . $newService . 'Table.php', str_replace(array('{prefix}', '{className}'), array($prefix, $newService), $tableContent));
    file_put_contents($path . $newService . 'Mapper.php', str_replace(array('{prefix}', '{className}'), array($prefix, $newService), $mapperContent));
    file_put_contents($path . $newService . 'Service.php', str_replace(array('{prefix}', '{className}', '{extends}'), array($prefix, $newService, $extendedClass), $serviceContent));
}
file_put_contents($path . $newService . 'Model.php', str_replace(array('{prefix}', '{className}', '{extends}'), array($prefix, $newService, $extendedModel), $modelContent));





