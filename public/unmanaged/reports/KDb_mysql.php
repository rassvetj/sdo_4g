<?php
/********************************************************************************************
*  Developed by Kovalenko Pavel (kovalenko_pavel@mail.ru)
*  for KEngine library
*  class KDb_mysql - 
*  version 1.6.3
********************************************************************************************/
class KDb_mysql {
  
/* public: connection parameters */
/*
   var $host     = "";
   var $user     = "";
   var $password = "";
   var $database = "";
*/

/* protected: mysql data*/
   var $connectID=0;
   var $requestID=0;
   var $row=0;
   var $rowData=array();
    
function KDb_mysql()
{
}

function &new_instance()
{
   //$this->connect(); 
   $newDB=$this;
   $newDB->requestID=0;
   $newDB->row=0;
   $newDB->rowData=array();
   return $newDB;
}

function connect($host="",$user="",$password="",$database="")
{
   if($this->connectID!=0)
      return;
/*
   if($host=="")
      $host=$this->host;
   else
      $this->host=$host;
   if($database=="")
      $database=$this->database;
   else
      $this->database=$database;
   if($user=="")
      $user=$this->user;
   else
      $this->user=$user;
   if($password=="")
      $password=$this->password;
   else
      $this->password=$password;
*/

   if(!@$this->connectID=mysql_connect($host,$user,$password)){
      $this->halt("Can't connect to data base");
      die;
   }
   @mysql_select_db($database,$this->connectID) or $this->haltSql();   
}

function close()
{
   @mysql_close($this->connectID) or $this->halt("Can't close connection of data base");
   $this->connectID=0;
}

function q($sql)
{
    $this->connect();
    @$this->requestID=mysql_query($sql,$this->connectID) or $this->haltSql($sql);
    $this->row=0;
    $this->rowData=array();
}

function q_no_error($sql)
{
    $this->connect();
    @$this->requestID=mysql_query($sql,$this->connectID);
    if(!$this->requestID)
    	return false;
    $this->row=0;
    $this->rowData=array();
    return true;
}


function countColuns()
{
    return mysql_num_fields($this->requestID);
}

function columnName($num)
{
    return mysql_field_name($this->requestID,$num);
}

function halt($msg)
{
	bugReport(" | MySQL | ERROR: ".$msg,$this);
}

function haltSql($sql='') {
	$this->error = @mysql_error($this->connectID);
	$this->errno = @mysql_errno($this->connectID);
	bugReport(" | MySQL | ERROR($this->errno): $this->error | ".$sql,$this);
}

function getLastInsertID()
{
   $resID=@mysql_query("select LAST_INSERT_ID() as id",$this->connectID) or $this->haltSql();
   $res=mysql_fetch_array($resID);
   mysql_free_result($resID); 
   return $res['id'];
}

function free() {
   @mysql_free_result($this->requestID);
   $this->requestID = 0;
}

function next_record()
{
   $this->rowData=mysql_fetch_array($this->requestID);   
   return $this->rowData!==FALSE;
}

function nr()
{
   $this->rowData=mysql_fetch_array($this->requestID);
   return $this->rowData!==FALSE;
}


function &f($name)
{
   return $this->rowData[$name];
}

function getNextAI($table)
{
   $this->q('SHOW TABLE STATUS FROM '.$this->database);
   while($this->nr()){
      if($this->f('Name')==$table)
         return $this->f('Auto_increment');
   }
   $this->halt("getNextAI: Unknwon table '$table' from db '$this->database'");
   return -1;
}

function seek($num) {
    @mysql_data_seek($this->requestID,$num);
    $this->rowData=mysql_fetch_array($this->requestID); 
    return $this->rowData!==FALSE;
}

function getValue($vName, $vTable, $vParam, $vParamVal) {
    $sql="SELECT ".$vName." FROM `".$vTable."` WHERE `".$vParam."`=".$vParamVal."";
    $this->q($sql);
    if ($this->nr()) return $this->f($vName);
}

function getValueEx($vSQL,$vName, $vTable, $vParam, $vParamVal) {
    $sql="SELECT ".$vSQL." as ".$vName." FROM `".$vTable."` WHERE `".$vParam."`=".$vParamVal."";
    $this->q($sql);
    if ($this->nr()) return $this->f($vName);
}

function num() {
    return mysql_num_rows($this->requestID);
}

function assign(&$t)
{
    $t->set($this->rowData);
}

function rowData()
{
    return $this->rowData;
}

function rowDataByName()
{
    $data=array();
    for($i=0;$i<$this->countColuns();$i++)
        $data[$this->columnName($i)]=$this->rowData[$i];
    return $data;
}

}
?>
