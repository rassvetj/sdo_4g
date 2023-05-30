<? 

if (!isset($include)) require ("setup.inc.php");

require ("coment.inc.php4");

$css="<?php\n";

debug_yes("array",$HTTP_COOKIE_VARS);

function get_js_name($param)
{
 switch ($param)
	{
    case     "font-family"       : return "fontFamily";
    case     "font-size"         : return "fontSize";
    case     "font-weight"       : return "fontWeight";
    case     "text-align"        : return "textAlign";
    case     "text-transform"    : return "textTransform";
    case     "color"             : return "color";
    case     "background-color"  : return "backgroundColor";
    case     "display"           : return "display";
    case     "line-height"       : return "lineHeight";
    case     "cursor"            : return "cursor";
    case     "white-space"       : return "whiteSpace";
    case     "letter-spacing"    : return "letterSpacing";
    case     "height"            : return "height";
    case     "width"             : return "width";
    case     "border-style"      : return "borderStyle";
    case     "border-color"      : return "borderColor";
    case     "border-width"      : return "borderWidth";
    case     "border-bottom"     : return "borderBottom";
    case     "border-top"        : return "borderTop";
    case     "border-left"       : return "borderLeft";
    case     "border-right"      : return "borderRight";
    case     "padding"           : return "padding";
    case     "padding-left"      : return "paddingLeft";
    case     "padding-right"     : return "paddingRight";
    case     "padding-top"       : return "paddingTop";
    case     "padding-bottom"    : return "paddingBottom";
    case     "border"            : return "border";
    case     "text-decoration"   : return "textDecoration";
	}
}

	$buf=implode("",file("../styles/style.css")); 
	$buf=str_replace("\r","",$buf);
	$buf=str_replace("\n","",$buf);


        preg_match_all ("/(a:[\w]+)[^{]*\{(.[^}]*)\}/", $buf, $matches);
	for ($i=0; $i< count($matches[0]); $i++) 
	{
	$n=$i+1;
	$css.="\$cssval[".$n."][name]='".$matches[1][$i]."';\n";
	$css.="\$cssval[".$n."][type]='link';\n";
	$css.= (isset($coment[$matches[1][$i]])) ? "\$cssval[".$n."][coment]='".$coment[$matches[1][$i]]."'; \n" : "\$cssval[".$n."][coment]=' '; \n";
	$buf2=$matches[2][$i];
        
	preg_match_all ("/(([\w,\-,\w]+):|([\w,\-,\w]+):\s([\%,\s,\',\#,\w]+));/", $buf2, $mat);
        
        for ($j=0; $j< count($mat[0]); $j++) $css.="\$cssval[".$n."][".get_js_name($mat[3][$j])."]='".str_replace("'","",$mat[4][$j])."';\n";
        }
	
	$full=$n;

        preg_match_all ("/\}([\s,\w][^:,^{])[^{]*\{(.[^}]*)\}/", $buf, $matches);
	for ($i=0; $i< count($matches[0]); $i++) 
	{
	$n=$i+1+$full;
	$css.="\$cssval[".$n."][name]='".$matches[1][$i]."';\n";
	$css.="\$cssval[".$n."][type]='tag';\n";
	$css.= (isset($coment[$matches[1][$i]])) ? "\$cssval[".$n."][coment]='".$coment[$matches[1][$i]]."'; \n" : "\$cssval[".$n."][coment]=' '; \n";
	$buf2=$matches[2][$i];
        preg_match_all ("/(([\w,\-,\w]+):|([\w,\-,\w]+):\s([\%,\s,\',\#,\w]+));/", $buf2, $mat);
        for ($j=0; $j< count($mat[0]); $j++) $css.="\$cssval[".$n."][".get_js_name($mat[3][$j])."]='".str_replace("'","",$mat[4][$j])."';\n";
	}

	$full=$n;

	preg_match_all ("/(\.[\w]+)[^\ot)][^{]*\{(.[^}]*)\}/", $buf, $matches);

	for ($i=0; $i< count($matches[0]); $i++) 
	{
	$buf2=$matches[2][$i];
	$n=$i+1+$full;

        	$css.="\$cssval[".$n."][name]='".substr($matches[1][$i],1,48)."';\n";		
		$css.="\$cssval[".$n."][type]='class';\n";
		$css.= (isset($coment[$matches[1][$i]])) ? "\$cssval[".$n."][coment]='".$coment[$matches[1][$i]]."'; \n" : "\$cssval[".$n."][coment]=' '; \n";
        	preg_match_all ("/(([\w,\-,\w]+):|([\w,\-,\w]+):\s([\s,\',\#,\w]+));/", $buf2, $mat);
                for ($j=0; $j< count($mat[0]); $j++) $css.="\$cssval[".$n."][".get_js_name($mat[3][$j])."]='".str_replace("'","",$mat[4][$j])."';\n";
	}


$css.="?>";

   $f=fopen($DIR_CSS."style.css.php4","w");
   return fwrite($f,$css).myclose($f);

?> 
