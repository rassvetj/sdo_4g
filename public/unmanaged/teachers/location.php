<?php
require_once("../1.php");

if ($_POST['location'] && $_POST['sheid'] && ($_SESSION['s']['location']==$_POST['sheid']) 
    && ($_SESSION['s']['perm']==2)) {
    sql("DELETE FROM schedule_locations WHERE sheid='".(int) $_POST['sheid']."'");
    sql("INSERT 
         INTO schedule_locations (sheid, location, teacher) 
         VALUES ('".(int) $_POST['sheid']."',
         '".(int) $_POST['location']."',
         '".(int) $_SESSION['s']['mid']."')");
    exit('ok');
}

if ($_POST['sheid'] && ($_SESSION['s']['location']==$_POST['sheid']) 
    && ($_SESSION['s']['perm']==1)) {                
    $sql = "SELECT location FROM schedule_locations WHERE sheid='".(int) $_POST['sheid']."'";
    $res = sql($sql);
    while($row = sqlget($res)) {
    	exit($row['location']);
    }
    
}
    
exit(0);
?>