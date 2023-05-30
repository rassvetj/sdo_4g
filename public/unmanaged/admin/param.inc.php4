<?php
       function showprevtag($key,$value)
        {

        $val = $value[$key];


        echo "<tr><td bgcolor=white><br><div id='prev$key' style=\"";
     
        while (list($param) = each($value[$key]))        
         {
                $str=getstyle($param,$val[$param]);
                echo($str);
         };
        echo "\"> S T U D I U M   P r e v i e w</div><br></td></tr>\n";
        echo "<tr><td bgcolor=white>\n<br>\n";
//        echo "<div class=".$val['name']." id='old$key' > S T U D I U M   P r e v i e w</div><br></td></tr>";

?>
   <center>
   <table width='95%' align="center" border="0" cellspacing="1" cellpadding="5" class=br> 
   <tr align='center'>
     <th bgcolor="f5f5f5">
		S T U D I U M   P r e v i e w
   </th>
   </tr>
   </table>
<br></td></tr>

<?

        }

       function showprevlink($key,$value)
        {

        $val = $value[$key];


        echo "<tr><td bgcolor=white><br><div id='prev$key' style=\"";
     
        while (list($param) = each($value[$key]))        
         {
                $str=getstyle($param,$val[$param]);
                echo($str);
         };
        echo "\"> S T U D I U M   P r e v i e w</div><br></td></tr>\n";
        echo "<tr><td bgcolor=white>\n<br>\n";
//        echo "<div class=".$val['name']." id='old$key' > S T U D I U M   P r e v i e w</div><br></td></tr>";
       ?>
   <center>
   <table width='95%' align="center" border="0" cellspacing="1" cellpadding="5" class=br> 
   <tr align='center'>
     <td bgcolor="f5f5f5">
		<a href="setup2.php4" onclick="return false">S T U D I U M   P r e v i e w</a>
   </td>
   </tr>
   </table>
<br></td></tr>
<?

        }

       function showprevclass($key,$value)
        {

        $val = $value[$key];


        echo "<tr><td bgcolor=white><br><div id='prev$key' style=\"";
     
        while (list($param) = each($value[$key]))        
         {
                $str=getstyle($param,$val[$param]);
                echo($str);
         };
        echo "\"> S T U D I U M   P r e v i e w</div><br></td></tr>\n";
        echo "<tr><td bgcolor=white>\n<br>\n";
        echo "<div class=".$val['name']." id='old$key' > S T U D I U M   P r e v i e w</div><br></td></tr>";

        }

      function getstyle($param,$val)
      {
       switch ($param) 
                {
                      case "fontSize"   : $str="font-size: ".$val."; "  ; break;
                      case "color"      : $str="color: ".$val."; "  ; break;
                      case "fontFamily" : $str="font-family: ".$val."; "  ; break;
                      case "fontWeight" : $str="font-weight: ".$val."; "  ; break;
                      case "lineHeight" : $str="line-height: ".$val."; "  ; break;
                      case "cursor"     : $str="cursor: ".$val."; "      ; break;
                      case "borderStyle": $str="border-style: ".$val."; "  ; break;
                      case "borderColor": $str="border-color: ".$val."; "  ; break;
                      case "borderWidth": $str="border-width: ".$val."; "  ; break;
                      case "backgroundColor" : $str="background-color: ".$val."; "  ; break;
                      case "padding"    : $str="padding: ".$val."; "  ; break;
                      case "paddingTop" : $str="padding-top: ".$val."; "  ; break;
                      case "paddingLeft": $str="padding-left: ".$val."; "  ; break;
                      case "paddingRight": $str="padding-right: ".$val."; "  ; break;
                      case "paddingBottom": $str="padding-bottom: ".$val."; "  ; break;
                      case "textTransform": $str="text-transform: ".$val."; "  ; break;
                      case "whiteSpace" : $str="white-space: ".$val."; "  ; break;
                      case "letterSpacing": $str="letter-spacing: ".$val."; "  ; break;
                      case "textAlign": $str="text-align: ".$val."; "  ; break;
                      case "width": $str="width: ".$val."; "  ; break;
                      case "height": $str="height: ".$val."; "  ; break;
		      case "display": $str="display: ".$val."; "  ; break;
		      case "borderBottom": $str="border-bottom: ".$val."; "  ; break;
		      case "borderTop": $str="border-top: ".$val."; "  ; break;
		      case "borderLeft": $str="border-left: ".$val."; "  ; break;
		      case "borderRight": $str="border-right: ".$val."; "  ; break;
		      case "border"	: $str="border: ".$val."; "  ; break;
		      case "textDecoration": $str="text-decoration: ".$val."; "  ; break;
//                      case "name"         : $str=""; break;
//                      case "coment"       : $str=""; break;
//                      case "type"         : $str=""; break;
                     default : $str="";
                }


//                if (!empty($str)) {$str.=""; };
                return($str);
      }

?>