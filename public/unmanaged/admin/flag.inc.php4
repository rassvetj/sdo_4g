<?php
        function flagsup()
        {
        $flag["fontSize"]=0;
        $flag["color"]=0;
        $flag["fontFamily"]=0;
        $flag["fontWeight"]=0;
        $flag["lineHeight"]=0;
        $flag["cursor"]=0;
        $flag["backgroundColor"]=0;
        $flag["borderStyle"]=0;
        $flag["borderColor"]=0;
        $flag["borderWidth"]=0;
        $flag["padding"]=0;
        $flag["paddingTop"]=0;
        $flag["paddingLeft"]=0;
        $flag["paddingRight"]=0;
        $flag["paddingBottom"]=0;
        $flag["textTransform"]=0;
        $flag["whiteSpace"]=0;
        $flag["textAlign"]=0;
        $flag["width"]=0;
        $flag["height"]=0;
	$flag["display"]=0;
	$flag["letterSpacing"]=0;
	$flag["borderBottom"]=0;
	$flag["borderTop"]=0;
	$flag["borderLeft"]=0;
	$flag["borderRight"]=0;
	$flag["border"]=0;
	$flag["textDecoration"]=0;
//      $flag[""]=0;
        return($flag);
        }



        function flagswitch($key,$param,$val,$flag)
        {
        switch ($param) {
                      case "fontSize"   : $flag[$param]=changesize($key,$param,$val) ; echo"</tr>" ;   ; break;
                      case "color"      : $flag[$param]=showfontcolor($key,$param,$val)    ; echo"</tr>" ; break;
                      case "fontFamily" : $flag[$param]=showfontfamily($key,$param,$val)   ; echo"</tr>" ; break;
                      case "fontWeight" : $flag[$param]=showfontweight($key,$param,$val)   ; echo"</tr>" ; break;
                      case "lineHeight" : $flag[$param]=changesize($key,$param,$val)   ; echo"</tr>" ; break;
                      case "cursor"     : $flag[$param]=showcursor($key,$param,$val)       ; echo"</tr>" ; break;
                      case "backgroundColor" : $flag[$param]=showfontcolor($key,$param,$val)   ; echo"</tr>" ; break;
                      case "borderStyle": $flag[$param]=showborderStyle($key,$param,$val)   ; echo"</tr>" ; break;
                      case "borderColor": $flag[$param]=showfontcolor($key,$param,$val)   ; echo"</tr>" ; break;
                      case "borderWidth": $flag[$param]=changesize($key,$param,$val)   ; echo"</tr>" ; break;
                      case "padding"    : $flag[$param]=changesize($key,$param,$val)   ; echo"</tr>" ; break;
                      case "paddingTop" : $flag[$param]=changesize($key,$param,$val)   ; echo"</tr>" ; break;
                      case "paddingLeft": $flag[$param]=changesize($key,$param,$val)   ; echo"</tr>" ;break;
                      case "paddingRight":$flag[$param]=changesize($key,$param,$val)   ; echo"</tr>" ; break; 
                      case "paddingBottom":$flag[$param]=changesize($key,$param,$val)   ; echo"</tr>" ; break;
                      case "textTransform":$flag[$param]=showtexttransform($key,$param,$val)   ; echo"</tr>" ; break;
                      case "whiteSpace" :$flag[$param]=showwhitespace($key,$param,$val)   ; echo"</tr>" ; break;
                      case "letterSpacing" :$flag[$param]=changesize($key,$param,$val)   ; echo"</tr>" ; break;
                      case "textAlign" :$flag[$param]=showtextalign($key,$param,$val)   ; echo"</tr>" ; break;
                      case "width" :$flag[$param]=changesize($key,$param,$val)   ; echo"</tr>" ; break;
                      case "height" :$flag[$param]=changesize($key,$param,$val)   ; echo"</tr>" ; break;
		      case "display":$flag[$param]=changedisplay($key,$param,$val)   ; echo"</tr>" ; break;
		      case "borderBottom": $flag[$param]=changesize($key,$param,$val)   ; echo"</tr>" ; break;
		      case "borderTop": $flag[$param]=changesize($key,$param,$val)   ; echo"</tr>" ; break;
		      case "borderLeft": $flag[$param]=changesize($key,$param,$val)   ; echo"</tr>" ; break;
		      case "borderRight": $flag[$param]=changesize($key,$param,$val)   ; echo"</tr>" ; break;
		      case "border"	: $flag[$param]=changesize($key,$param,$val)   ; echo"</tr>" ; break;
		      case "textDecoration": $flag[$param]=changdecoration($key,$param,$val)   ; echo"</tr>" ; break;
                      }
        return($flag);
        }

        function editmode($all,$element)
        {
        echo "<input type='hidden' name='element' value='".$element."'>";


        
         if ($all) 
                 { 
                  $msg=_("Выключить расширенное редактирование стиля") ;
                  echo "<tr><th><input type='checkbox' name='all' value='' style='width:30px; hborder:none; border-width: 0px' onclick='form.submit()'  checked>".$msg;
                 } 
                  else
                 { 
                  $msg=_("Включить расширенное редактирование стиля") ;
                  echo "<tr><th><input type='checkbox' name='all' value='1' style='width:30px; border:none; border-width: 0px' onclick='form.submit()'>".$msg;
                 }
         echo "</th></tr>\n<tr><td><center>"._("Внимание: все неприменённые настройки пропадут!")."</center></td></tr>";
        }

?>