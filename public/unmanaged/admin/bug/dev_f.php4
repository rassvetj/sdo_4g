<?php

$connect=get_mysql_base();

function show_1_change($name,$email,$type,$file,$desc,$id,$pd,$ad)
{
       switch ($type)
        {
                case "new" : $msg['show']="Ok"; $color="#dd4049"; $msg['type']="added"; break;
                case "added" :  $msg['show']="Return"; $color="#66bf24"; $msg['type']="return"; break;
                case "return" : $msg['show']="Ok"; $color="#77bfff"; $msg['type']="added"; break;
                default : $msg['show']="error"; $color="#ffff00"; $msg['type']="new"; break;
        }
?>
   <tr align="center">
      <td bgcolor="f5f5f5" width="50px">
        <a href="show.php4?sql=UPDATE+buglog+SET+type='<?=$msg['type']?>',+build='0',+add_date='<?=date("Y-m-d")?>'+WHERE+DiD='<?=$id?>'"><?=$msg['show']?></a>
          </td>
      <td bgcolor="<?=$color?>" width="25px">&nbsp;</td>
      <td bgcolor="<?
       switch ($file)
        {
                case "critical" : echo "red"; break;
                case "data" :  echo "yellow"; break;
                case "design" : echo "teal"; break;
                case "others" : echo "white"; break;
                default : echo "red"; break;
        }
?>
" width="150px"><?=$file?>
          </td>
      <td bgcolor="white">
        <?=$desc?>
          </td>
      <td bgcolor="white" width="100px"><?=$pd?>
          </td>
      <td bgcolor="white" width="100px"><?=$ad?>
          </td>
      <td bgcolor="f5f5f5" width="50px">
        <?php echo "<a href='mailto:".$email."'>".$name."</a>"; ?>
          </td>
      <td bgcolor="f5f5f5" width="50px">
        <a href="show.php4?sql=DELETE+FROM+buglog+WHERE+`DiD`='<?=$id?>'">Del</a>
          </td>
      <td bgcolor="f5f5f5" width="50px">
        <a href="add.php4?edit=1&id=<?=$id?>">Edit</a>
          </td>
        </tr>
<?
}

function show_all_changes($res)
{
?>
<table align="center" border="0" cellspacing="1" cellpadding="5" width="1000px" bgcolor="black"  style="font-size:13px">
<tr align="center">
      <td bgcolor="f5f5f5" width="50px">
        <b>do</b>
         </td>
      <td bgcolor="#f5f5f5" width="25px">
        <b>T</b>
         </td>
      <td bgcolor="#f5f5f5" width="150px"><b>Error Type</b>
          </td>
      <td bgcolor="#f5f5f5">
        <b>Description</b>
        </td>
      <td bgcolor="#f5f5f5" width="100px"><b>add date</b>
          </td>
      <td bgcolor="#f5f5f5" width="100px"><b>last changes</b>
          </td>
      <td bgcolor="#f5f5f5" width="50px">
         <b>name</b>
          </td>
      <td bgcolor="#f5f5f5" width="50px">
        <b>del</b>
          </td>
      <td bgcolor="f5f5f5" width="50px">
        <b>edit</b>
        </td>
</tr>
<?php
  while ($temp=sqlget($res))
  show_1_change($temp['name'],$temp['email'],$temp['type'],$temp['file'],$temp['desc'],$temp['DiD'],$temp['post_date'],$temp['add_date']);
?>
  </table><br>
<?php
}

function show_all_builds()
{
  $res=dev_sql(2);
  while ($temp=sqlget($res))
        for($i=1;$i<=$temp['MAX(build)'];$i++) echo "<a href='show.php4?b=".$i."'>".$i."</a>&nbsp;" ;


}

function create_build()
{
  $new_build=0;
  $res=dev_sql(2);
  $temp=sqlget($res);
  $new_build=$temp['MAX(build)']+1;
  $res=dev_sql(7,$new_build);
  return $new_build;
}

function dev_sql($type,$param="")
                {
                        global $connect;

                        if ($connect)
                        {
                        debug_yes("min",$param->min);
                        debug_yes("max",$param->max);
                                switch ($type)
                                        {       case 1 : $sql="SELECT * from buglog WHERE `build`='0' ORDER BY `type` DESC"; break;
                                                case 3 : $sql="SELECT * from buglog WHERE `type`='new' AND `build`='0' ORDER BY `type` DESC"; break;
                                                case 4 : $sql="SELECT * from buglog WHERE `type`='added' AND `build`='0' ORDER BY `type` DESC"; break;
                                                case 5 : $sql="SELECT * from buglog WHERE `type`='return' AND `build`='0' ORDER BY `type` DESC"; break;
                                                case 6 : if (isset($param)) $sql="SELECT * from buglog WHERE `build`='".$param."' ORDER BY `type` DESC"; break;
                                                case 7 : if (isset($param)) $sql="UPDATE buglog SET build='".$param."' WHERE build='0' AND type='added'"; break;
                                                case 8 : if (!empty($param["id"])) $result=@sql("DELETE FROM buglog WHERE `DiD`='".$param["id"]."'"); $sql="INSERT INTO `buglog` (`DiD`, `name`, `email`, `file`, `desc`, `type`, `build`, `post_date`, `add_date`) VALUES ('".$param['id']."', '".$param['name']."', '".$param['email']."', '".$param['file']."', '".$param['desc']."', '".$param['type']."', '".$param['build']."', '".$param['pd']."', '".$param['ad']."')"; break;
                                                case 2 : $sql="SELECT MAX(build) FROM `buglog`"; break;
                                                case 9 : if (isset($param)) $sql="SELECT * from buglog WHERE `DiD`='".$param."'"; break;
                                                case "free" : if (!empty($param)) $sql=$param; break;
                                                default : exit();

                                        }
                debug_yes("SQL",$sql);
                                $result=@sql($sql) or die("Could not execute query");
                        }else
                        {       $result=array(0);
                        }

                        return $result;
                } // function sql_query look up

function run_sql($sql)
{
  $sql=stripslashes($sql);
  $res=dev_sql("free",$sql);
}

function get_vars($id)
{
 $res=dev_sql(9,$id);
  while ($temp=sqlget($res))
 {
 $result->name=$temp['name'];
 $result->id=$temp['DiD'];
 $result->email=$temp['email'];
 $result->file=$temp['file'];
 $result->desc=$temp['desc'];
 }
  return $result;
}

?>