<?php

define('LOP_MAX_UPLOAD_SIZE',100000000); // ~100Mb
define('ITEMS_PER_PAGE',50);

$lo_types = array(
0 => _("Электронное издание"),
1 => _("Печатное издание"),
2 => 'CD/DVD'
);

$lom_lang_preferences = array('ru','en','x-none');

function get_login_and_lastname_and_firstname_by_mid($mid) {
         $query = "SELECT Login, LastName, FirstName FROM People WHERE MID='$mid'";
         $result = sql($query);
         if (sqlrows($result)) {
            $row = sqlget($result);
            $ret = $row['Login'].' ('.$row['LastName'].' '.$row['FirstName'].')';
         }
         
         return $ret;
}

function getMaxPagesCount($id,$table,$where="",$npp)
{

    $nmax=0;

    $tmpl = "SELECT COUNT($id) AS cnt FROM $table $where";
    $res = sql($tmpl);

    if (sqlrows($res))
    {

        $row = sqlget($res);
        $nmax = ceil($row['cnt'] / $npp);
    }
    return $nmax;
}

function doPerPages($page,$n,$npp,$id,$table,$where="")
{

    $content="";

    $tmpl = "SELECT COUNT($id) AS cnt FROM $table $where";
    $res = sql($tmpl);

    if (sqlrows($res))
    {

        $row = sqlget($res);
        $nmax = $row['cnt'];
        if ($nmax>=$npp)
        {
            $j=1;
            $content .= "<div align=center>";
            for($i=0;$i<=$nmax-1;$i+=$npp)
            {
                $content .= "[<a href=\"$page";
                $content .=  "page=$i";
                if (isset($GLOBALS['sort'])) $content .= "&sort=".(int) $GLOBALS['sort'];
                $content .="\">";
                if (($n>=$i)&&($n<$i+$npp)) $content .=  "<b>";
                $content .= $j++;
                if (($n>=$i)&&($n<$i+$npp)) $content .=  "</b>";
                $content .=  "</a>] ";
                
            }
            $content .= "</div>";
        }
    }

    return $content;
}

function &getPeopleList() {
    
    $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
    
    $sql = "SELECT MID, LastName, FirstName FROM People ORDER BY People.LastName, People.FirstName";
    $res = sql($sql);        
    
    while($row = sqlget($res)) {
        if ($peopleFilter->is_filtered($row['MID'])) $ret[]=$row;
    }
    return $ret;
}

function mkdirs($dir, $mode = 0777, $recursive = true) {
    
  if( is_null($dir) || $dir === "" ) {
      return FALSE;
  }  
  if( is_dir($dir) || $dir === "/" ) {
      return TRUE;
  }
  if( mkdirs(dirname($dir), $mode, $recursive) ) {
      $oldumask = umask(0);
      $ret = mkdir($dir, $mode);
      umask($oldumask);
      return $ret;
  }  
  return FALSE;
}

function deldir($dir){
    $d = dir($dir);
    while($entry = $d->read()) {
        if ($entry != "." && $entry != "..") {
            if (Is_Dir($dir."/".$entry))
                deldir($dir."/".$entry);
            else
                unlink ($dir."/".$entry);
        }
    }
    $d->close();
    @rmdir($dir);
}

?>