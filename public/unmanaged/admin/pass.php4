<?php
$include=TRUE;

error_reporting(2039);

require ("setup.inc.php");
//require ("adm_t.php4");
echo show_tb();
require ("adm_fun.php4");

debug_yes("array",$HTTP_COOKIE_VARS);
debug_yes("array",$HTTP_GET_VARS);


$disable=FALSE;

if (isset($change) && isset($newpass) && isset($confim) && isset($oldpass) && !($disable))
        {
         if (!empty($confim) && !empty($newpass) && !empty($oldpass))
          {
                if ($newpass==$confim)
                        {
                        $connect=get_mysql_base();
                        $res=sql_query(3);
                        $result=@sqlget($res);
//                      extract($result,EXTR_OVERWRITE);
                        debug_yes("array",$result);
                        if ($oldpass==$result['value'])
                                {
                                        $res=sql_query(4,$newpass);
                                        echo "<center><h3>           .</h3></center>";
                                }else
                                {
                                        echo "<center><h3>        .</h3></center>";
                                }
//                      debug_yes("array",$result);
                        }else
                        {
                                echo "<center><h3>                                 </h3><br>
                                      ,                     !</h3></center>";
                        }
                }else
                {
                                echo "<center><h3>    ,                    </h3></center>";
                }
        }
?>
<center>
        <form action="pass.php4" target="_self" name="act" method="get">
                <table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" class=br   style="font-size:13px">
                        <tr align="center">
                                <td class=questt>
                                                                        <b>       </b>
                                </td>
                        </tr>
                        <tr align="left" bgcolor="white">
                                <td >
                                        <input type="password" name="oldpass" style="width:250px"> : Old Password
                                </td>
                        </tr>
                        <tr align="left" class=questt>
                                <td>
                                        <input type="password"  name="newpass" style="width:250px"> : New Password
                                </td>
                        </tr>
                        <tr align="left" bgcolor="white">
                                <td>
                                        <input type="password" name="confim" style="width:250px"> : Confim new Password
                                </td>
                        </tr>
                        <tr align="center" class=questt>
                                <td>
                                        <input type="submit" name="change" value="Ok" style="width:250px">
                                </td>
                        </tr>

                </table>
        </form>
</center>

<?php

//require_once("adm_b.php4");
echo show_tb();
?>