<?php
/********************************************************************************************
*  Developed by Kovalenko Pavel (kovalenko_pavel@mail.ru)
*  for KEngine library
*  class KDb_mysql_DEBUG - 
*  version 1.1
********************************************************************************************/
    include_once("KDb_mysql.php");
    include_once("KTimer.php");

class KDb_mysql_DEBUG extends KDb_mysql
{
  
function q($sql)
{    
    $time = new KTimer();
    global $__mysql_q_count,$__mysql_q_time;
    if(!isset($__mysql_q_count))$__mysql_q_count=1;
    else $__mysql_q_count++;
    if(!isset($__mysql_q_time))$__mysql_q_time=0;
    if(_K_MYSQL_Q_SHOW=='yes')
        $this->_dbgMsg($sql);

    parent::q($sql);
    $time->end_time();
    $__mysql_q_time+=$time->elapsed_time();
}

function _dbgMsg($msg)
{
    $ar=array("<"=>"&lt;",">"=>"&gt;");
    $msg=strtr($msg,$ar);
    if(_K_DEBUG=='yes')
    {
        global $_debug_out;
        $_debug_out.="<font color='green'><b>MySQL : </b> </font><br>$msg<br><br>\n";
    }else{
        echo "<font color='green'><b>MySQL debug:</b></font>$msg<br>\n";
    }
}
/*
function _dbgMsg($msg)
{
    $stack=debug_backtrace();
    //pr($stack);

    $ar=array("<"=>"&lt;",">"=>"&gt;");
    $msg=strtr($msg,$ar);
    if(_K_DEBUG=='yes')
    {
        global $_debug_out;
        $lastC='';
        $classname=array();        
        foreach($stack as $col){
            if($col['class']=='kdb_mysql_debug' || empty($col['class']))continue;
            if(empty($classname) || $col['class']!=end($classname)){
                $classname[]=$col['class'];
            }
        }
        $_debug_out.="<font color='green'><b>MySQL : </b><font color=red>".implode('  ',array_reverse($classname))."</font> :</font><br>$msg<br><br>\n";
    }else{
        echo "<font color='green'><b>MySQL debug:</b></font>$msg<br>\n";
    }
}
*/

}
?>
