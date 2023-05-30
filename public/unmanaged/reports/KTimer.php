<?php
/********************************************************************************************
*  Developed by Kovalenko Pavel (kovalenko_pavel@mail.ru)
*  for KEngine library
*  class KTimer - 
*  version 1.2
********************************************************************************************/

class KTimer
{ 
    var $stime; 
    var $etime;
    var $mode="status";

function KTimer($mode="status")
{
	$this->mode=$mode;
    $this->start_time();
}

function get_microtime(){ 
    $tmp=split(" ",microtime()); 
    $rt=$tmp[0]+$tmp[1]; 
    return $rt; 
}
  
function start_time(){ 
    $this->stime = $this->get_microtime(); 
}
  
function end_time(){ 
    $this->etime = $this->get_microtime(); 
}
  
function elapsed_time(){ 
    return ($this->etime - $this->stime); 
}

function finish()
{
    $this->end_time();
    echo $this->render();
}

function stop()
{
    $this->end_time();
    return $this->render();
}

function render()
{
    switch($this->mode){
    	case "digit";
    		return round($this->elapsed_time(),4);
        case "echo":
            return "time='".$this->elapsed_time()."'<br>";
        break;
        case "status":
            global $__mysql_q_count,$__mysql_q_time;
            $s="";
            if(isset($__mysql_q_count)){
                $s="q:$__mysql_q_count;t:".round($__mysql_q_time,4).";";
            }
            return "<script language='JavaScript'>window.status = 't:".round($this->elapsed_time(),4).";".$s."';</script>";
        break;
    }
    return "";
}
  
}
?>