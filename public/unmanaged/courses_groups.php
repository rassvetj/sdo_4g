<?php

require_once("1.php");
require_once('courses.lib.php');
require_once('schedule.lib.php');
require_once('lib/classes/CCourseAdaptor.class.php');

define("COURSES_GROUPS_DIR", "temp/courses_groups");

require_once($GLOBALS['wwf'].'/lib/sajax/SajaxWrapper.php');
$sajax_javascript = CSajaxWrapper::init(array('search_people_unused')).$js;

$GLOBALS['controller']->setHeader(_('Рубрикатор курсов'));
$GLOBALS['controller']->setHelpSection($c);

switch ($c) {

    case "":
        echo show_tb();
        $GLOBALS['controller']->captureFromOb(CONTENT);
        $divs = get_structure();
        set_structure_levels($divs);

        echo "
            <div style='padding-bottom: 5px;'>
                <div style='float: left;'><img src='{$GLOBALS['sitepath']}images/icons/small_star.gif'>&nbsp;</div>
                <div><a href='{$GLOBALS['sitepath']}courses_groups.php?c=edit&did=0' style='text-decoration: none;'>"._("создать рубрику")."</a></div>
            </div>
            <table width=100% class=main cellspacing=0>
                <tr><th>"._("Название")."</th><th width='100px' align='center'>"._("Действия")."</th></tr>
        ";
        echo show_sublevel( $divs, 0 );
        echo "</table>";
        $GLOBALS['controller']->captureStop(CONTENT);
        echo show_tb();
        break;

    case "delete":
        intvals("did");

        if (!empty($did)) {
            delete_department($did);
            //$res=sql("DELETE FROM courses_groups WHERE did='$did'","errFM185");
            //sqlfree($res);
            //sql("DELETE FROM courses_groups_soids WHERE did='".(int) $did."'");
            //sql("DELETE FROM courses_groups_courses WHERE did='".(int) $did."'");
            //sql("DELETE FROM courses_groups_groups WHERE did='".(int) $did."'");
            //sql("DELETE FROM courses_groups_tracks WHERE did='".(int) $did."'");
        }

        refresh("$PHP_SELF?$sess");
        break;

    case "edit":
        intvals("did");
        echo show_tb();
        $GLOBALS['controller']->captureFromVar(CONTENT,'tmp',$tmp);
        $GLOBALS['controller']->setHeader(_("Редактирование свойств рубрики"));

        $tmp="SELECT * FROM courses_groups WHERE did={$did}";
        $r=sql( $tmp );
        $res=sqlget( $r );
        $not_in = $res['not_in'];
        sqlfree($r);


        $tmp =  "   <script type=\"text/javascript\" language=\"JavaScript\" src=\"{$sitepath}js/roles.js\"></script>
                    <form action=$PHP_SELF method=post enctype='multipart/form-data'>
                    <input type=hidden name=c value=\"post_edit\">
                    <input type=hidden name=did value='$did'>";

        $divs=getDivs( $res[ did ], $res[ owner_did ] );

        $tmp .= "   <table width=100% class=main cellspacing=0>
                    <tr>
                        <td nowrap>"._("Название")."</td>
                        <td ><input type=text name=name value='".$res[ name ]."'></td>
                    </tr>
                    <tr>
                        <td>"._("Входит в")."</td>
                        <td > {$divs} </td>
                    </tr>";

        if (file_exists($file = "temp/courses_groups/{$res['did']}/{$res['file_image']}")) {
            $tmp .= "<tr><td>Изображение</td><td><img src='{$file}' /></td></tr>";
        }

        $toolTip = new ToolTip();

        $tmp .= "   <tr>
                        <td>"._('Загрузка изображения')."</td>
                        <td><input type='file' name='file_image' />&nbsp;&nbsp;".$toolTip->display('rubric_pic')."</td>
                    </tr>
                </table>
                <table border=\"0\" cellspacing=\"5\" cellpadding=\"0\" width=\"100%\">
                      <tr>
                        <td align=\"right\" width=\"99%\">
                        ".okbutton()."
                        </td>
                        <td align=\"right\" width=\"1%\">
                        <div style='float: right;' class='button'><a href='{$GLOBALS['sitepath']}courses_groups.php'>"._("Отмена")."</a></div><input type='button' value='"._("отмена")."' style='display: none;'/><div class='clear-both'></div>
                        </td>
                      </tr>
                </table>
                </form>";

        $GLOBALS['controller']->captureStop(CONTENT);

        //$tmp .= getCoursesGant( "SELECT * FROM Courses WHERE did={$did}", TRUE, FALSE );

        echo $tmp;
        echo show_tb();
        return;

    case "post_edit":
        intvals("did");

        /* Проверка на зацикливание кафедр */
        if ($did && isDeparmentsCycle($did, $owner_did)) {
            $owner_did = '';
        }
        /**/

        if ($did) {
            $rq = " UPDATE courses_groups
                    SET name = ".$GLOBALS['adodb']->Quote($name).", owner_did = '".intval($owner_did)."'
                    WHERE did = {$did}";
            $res=sql($rq,"errGR138");
            sqlfree($res);
        }
        else {
            $rq = " INSERT INTO courses_groups (name, owner_did)
                    VALUES (".$GLOBALS['adodb']->Quote($name).", '".intval($owner_did)."')";
            $res=sql($rq,"errGR138");
            sqlfree($res);
            $did = sqllast();
        }

        $file_image = "";
        if (isset($_FILES['file_image']) && $_FILES['file_image']['size'] > 0) {
            $filepath = pathinfo($_FILES['file_image']['name']);
            if (in_array(strtolower($filepath['extension']), array("bmp", "jpg", "jpeg", "png", "bmp", "gif"))) {
                if (!@is_dir(COURSES_GROUPS_DIR)) {
                    @mkdir(COURSES_GROUPS_DIR, 0775);
                }
                if (!@is_dir(COURSES_GROUPS_DIR . "/{$did}")) {
                    @mkdir(COURSES_GROUPS_DIR . "/{$did}", 0775);
                }

                $_FILES['file_image']['name'] = to_translit(str_replace(" ", "_", $_FILES['file_image']['name']));
                $fn = COURSES_GROUPS_DIR . "/{$did}/{$_FILES['file_image']['name']}";

                if (@move_uploaded_file($_FILES['file_image']['tmp_name'], $fn)) {
                    //ресайзим картинку
                    makePreviewImage($fn,$fn,116,116);
                    $file_image = "file_image = '{$_FILES['file_image']['name']}'";
                }
            }
        }

        if ($file_image) {
            $rq = "UPDATE courses_groups SET {$file_image} WHERE did = {$did}";
            sql($rq);
        }
        
        refresh("$PHP_SELF?$sess");
        return;
}

/**
* Проверка на зацикливание кафедр
*/
function isDeparmentsCycle($depId, $ownerId) {

    if (empty($ownerId) || $ownerId == NULL) return false;
    $q = "SELECT owner_did FROM courses_groups WHERE did=".(int) $ownerId;
    $res = sql($q);
    while ($r = sqlget($res)) {

        if ($r['owner_did'] == $depId) return true;
        if ($r['owner_did'] == NULL) return false;
        sqlfree($res);

        $q = "SELECT owner_did FROM courses_groups WHERE did=".(int) $r['owner_did'];
        $res = sql($q);

    }
}

function getDivs( $self_did, $owner_did ){
    $tmp="SELECT * FROM courses_groups";
    $r=sql( $tmp );

    $divs="<SELECT name='owner_did'>";
    $divs.="<option value=0> - "._("укажите")." -</option>";
    while( $res=sqlget( $r ) ){
        if( $res[ did ] != $self_did ){
            if( $res[ did ] == $owner_did ) $sel=" selected "; else $sel="";
            $divs.="<option value=".$res[ did ] ." $sel>".$res[ name ]."</option>";
        }
    }
    $divs.="</SELECT>";// как задать только одного?";
    sqlfree($r);

    //      $rq="ALTER TABLE courses_groups ADD owner_did int";
    //      $res=sql( $rq,"ERR upgrading $table");


    return( $divs );
}


function show_structure( $divs ){
    if (is_array($divs)) {
        foreach( $divs as $div ){
            $sh="";
            for($i=0;$i<$div[ level ];$i++)
            $sh.="--";

            $tmp.=$sh.$div[ name ]."<BR>";
        }
    }
    return( $tmp );
}


function show_sublevel( $divs, $did, $sh="" ){
    if (is_array($divs)) {
        foreach( $divs as $r ){
            if( $r[ owner_did ] == $did ){
                //if( $did == 0 ){ $b=""; $bb="</b>"; } else{ $b=""; $bb="";}
                if($r[color]>""){ $color=$r[color]; $pic=""; }else{ $color="white"; $pic="-";}
                $tmp.="<tr>
            <!-- <td style='background:$color' width=30 align='center'>$pic</td> -->
            <td> $sh
                 $b $r[name] $bb
            </td>
            <td  align='center'>";
                $tmp.="<a href=$PHP_SELF?c=edit&did=$r[did]$sess>".getIcon('edit', _('Редактировать рубрику'))."</a>
               <a href=$PHP_SELF?c=delete&did=$r[did]$sess
               onclick=\"if (!confirm('"._("Удалить?")."')) return false;\" >".getIcon("delete", _('Удалить рубрику'))."</a>";
                $tmp.="</tr>";

                $tmp.=show_sublevel( $divs, $r[did], $sh.".." )."<P/>";
            }
        }
    }
    return( $tmp );
}

function get_structure( ){

    $tmp="SELECT * FROM courses_groups";
    $res=sql( $tmp );

    while( $r=sqlget( $res ) ){
        $divs[ $r[ did ] ][ did ]= $r[ did ];
        $divs[ $r[ did ] ][ owner_did ]= $r[ owner_did ];
        $divs[ $r[ did ] ][ name ]= $r[ name ];
        $divs[ $r[ did ] ][ color ]= $r[ color ];
        $divs[ $r[ did ] ][ mid ]= $r[ mid ];
        $divs[ $r[ did ] ][ info ]= $r[ info ];
    }
    sqlfree($r);
    return( $divs );
}

function get_structure_level( $divs, $div, $i=0 ){
    // check infinite loop
    $i++;
    if( ( $divs[ $div ][ owner_did ] > 0 ) && ( $i < count ( $divs ) ) ){
        $level=get_structure_level( $divs, $divs[ $div ][ owner_did ], $i ) + 1;
        //     echo "level= $level !! ";
    }else
    $level = 0;
    return( $level );
}

function set_structure_levels( &$divs ){
    if (is_array($divs)) {
        foreach( $divs as $div ){
            //    echo $div[ did ]."; ";
            $level = get_structure_level( $divs, $div[ did ] );
            $divs[ $div[ did ] ] [ level ] = $level;
            $divs[ $div[ did ] ] [ org ] = $i++;
        }
    }
}

function org_structure_levels( &$divs ){
    if (is_array($divs)) {
        foreach( $divs as $div ){
            //    echo $div[ did ]."; ";
            $level = get_structure_level( $divs, $div[ did ] );
            $divs[ $div[ did ] ] [ level ] = $level;
        }
    }
}

function delete_department($did) {
    if ($did) {
        $sql = "SELECT did FROM courses_groups WHERE owner_did='".(int) $did."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if ($row['did']) {
                delete_department($row['did']);
            }
        }

        //удалим данную категорию из свойств курсов
        //sql("UPDATE Courses SET did=0 WHERE did='".(int) $did."'");
        $res = sql($sql = "SELECT CID,did FROM Courses WHERE did LIKE '%;$did;%'");
        while ($row = sqlget($res)) {
            if (trim($row['did']) == ";$did;") {
                $replace = '';
            }else {
                $replace = ';';
            }
            sql("UPDATE Courses SET did='".str_replace(";$did;",$replace,$row['did'])."' WHERE CID='{$row['CID']}'");
        }

        sql("DELETE FROM courses_groups WHERE did='".(int) $did."'");
        //sql("DELETE FROM courses_groups_soids WHERE did='".(int) $did."'");
        //sql("DELETE FROM courses_groups_courses WHERE did='".(int) $did."'");
        //sql("DELETE FROM courses_groups_groups WHERE did='".(int) $did."'");
        //sql("DELETE FROM courses_groups_tracks WHERE did='".(int) $did."'");
    }
}

function search_people_unused($search, $current) {
    $html = '';
    $html .= "<option value=0>- "._("укажите")." -</option>";
    if ($current>0) {
        $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Login
                FROM People
                WHERE People.MID = '".(int) $current."'";
        $res = sql($sql);
        if ($row = sqlget($res)) {
            $html .= "<option selected value='".(int) $row['MID']."'> ".htmlspecialchars($row['LastName'].' '.$row['FirstName'].' ('.$row['Login'].')',ENT_QUOTES)."</option>";
            $html .= "<option value=0> ------</option>";
        }
    }
    if (!empty($search)) {
        $search = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,unicode_urldecode($search));
        $search = trim($search);
        $search = str_replace('*','%',$search);
        $where = "AND (People.LastName LIKE '%".addslashes($search)."%'
        OR People.FirstName LIKE '%".addslashes($search)."%'
        OR People.Login LIKE '%".addslashes($search)."%') AND People.MID NOT IN ('".(int) $current."')";
        $html .= peopleSelect( "deans", $current,"",true,true,$where);
    }
    return $html;
}


function getColors(){
    $tmp[1][id]="white";                 $tmp[1][title]=_("белый");
    $tmp[2][id]="gray";                         $tmp[2][title]=_("серый");
    $tmp[3][id]="yellow";                 $tmp[3][title]=_("желтый");
    $tmp[4][id]="red";                         $tmp[4][title]=_("красный");
    $tmp[5][id]="lightblue";         $tmp[5][title]=_("голубой");
    $tmp[6][id]="blue";                         $tmp[6][title]=_("синий");
    $tmp[7][id]="cyan";                         $tmp[7][title]=_("фисташковый");
    $tmp[8][id]="magenta";                 $tmp[8][title]=_("фиолетовый");
    $tmp[9][id]="darkgray";                 $tmp[9][title]=_("темносерый");
    $tmp[10][id]="black";                 $tmp[10][title]=_("черный");
    $tmp[11][id]="green";                 $tmp[11][title]=_("зеленый");
    $tmp[12][id]="braun";                 $tmp[12][title]=_("коричневый");
    $tmp[13][id]="Olive";                 $tmp[13][title]=_("оливковый");
    $tmp[14][id]="Navy";                 $tmp[14][title]=_("небесный");
    $tmp[15][id]="Purple";                 $tmp[15][title]=_("пурпурный");
    $tmp[16][id]="Silver";                 $tmp[16][title]=_("серебрянный");
    $tmp[17][id]="Lime";                 $tmp[17][title]=_("лимонный");
    $tmp[18][id]="Fuchsia";                 $tmp[18][title]=_("малиновый");
    $tmp[19][id]="Maroon";                 $tmp[19][title]=_("бордовый");

    return( $tmp );
}

function getPalette( $color="white" ){
    $tmp.="<SELECT name='color'>";
    $tmp.="<option value=0>- "._("укажите")." -</option>";
    $cols = getColors();
    foreach( $cols as $col ){
        if( $color == $col[id] ) $sel="selected"; else $sel="";
        $tmp.="<option value=".$col[id]." $sel>".$col[title]."</option>";
    }
    $tmp.="</SELECT>";
    return( $tmp );
}

?>