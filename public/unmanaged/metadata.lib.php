<?

define("ELDER", 1920);
define("MEAN", 1970);

if (defined('USE_NEW_METADATA') && USE_NEW_METADATA) {
	define('METADATA_VALUE_END_SIGN','%END%');
} else {
	define('METADATA_VALUE_END_SIGN',';');
}

require_once('metadata_custom.lib.php');
require_once("lib/FCKeditor/fckeditor.php");

//////////////////////////////////////////////////////// обработка метаданных
function parse_meta($text, $type){   // разбирает текст вида name1=val1;name2=val2<BR>name3=val3.... на массив строк
	$meta = array();
	if ($type == "simple") {
		$arr = array();

		eregi("type=(.*)".METADATA_VALUE_END_SIGN."title=".METADATA_VALUE_END_SIGN."value=", $text, $arr1);
		$arr[1] = $arr1[1];
		preg_match("/".METADATA_VALUE_END_SIGN."title=".METADATA_VALUE_END_SIGN."value=(.*?)".METADATA_VALUE_END_SIGN."/s", $text, $arr1);
		$arr[2] = $arr1[1];
		//$arr[2] = substr($arr1[1], 0, -11);

		//if (eregi("block=simple~name=description;type=(.*);title=;value=(.*);sub=;~\[~~\]", $text, $arr)) {
		if(isset($arr[1]) || isset($arr[2])) {
			$meta[0] = array("name" => "description",
			"type" => $arr[1],
			"title" => "",
			"value" => $arr[2],
			"sub" => "");
			return $meta;
		}
		//}
	}
	$text = trim($text, "[~~]");
	$temp = explode("[~~]", $text);
	$not_found = true;
	$type = trim($type);
	foreach($temp as $key => $value) {

		if(strpos($value, "block=".$type) !== false) {
			if ($value != 'block='.$type) {
				$not_found = false;
				break;
			}
		}
	}



	if($not_found) {
		$meta = load_metadata($type);
		return $meta;
	}

	$text = str_replace("block=".$type."~", "", $value);
	$lines=explode( "~", $text);

	$i=0;
	if( count($lines) > 0 ){
		foreach( $lines as $k => $line ) {
			$data=explode(METADATA_VALUE_END_SIGN,$line);
			if(count($data) > 0) {
				foreach($data as $elem) {
					//$val=explode( "=", $elem );
					$val[0]=substr($elem,0,strpos($elem,'='));
					$val[1]=substr($elem,strpos($elem,'=')+1);					
					$meta[$i][$val[0]] = $val[1];
				}
			}
			$i++;
		}
	}
	else {
		if($text!=""){
			$meta[0][info]=$text;
			echo $text;
		}
	}

	return($meta);
}

function makeTextFromMeta( $meta, $type=""){ // создает текст для запись в БД

	if( count( $meta ) > 0 ){
		$tmp = "block=$type~";
		foreach( $meta as $row ){
			$names=array_keys( $row );
			//  $tmp.=$names[0]."--";
			$i=0;
			foreach( $row as $elem ){
				$tmp.=$names[$i]."=".$elem.METADATA_VALUE_END_SIGN;
				$i++;
			}
			$tmp.="~";
		}
	}
	$tmp .= "[~~]";
	//  echo $tmp;
	return( $tmp );
}

function get_reg_block_title($type) {
   switch ($type) {
      case "contacts":
			return _("Контактная информация");
      break;
      case "address_postal":
         return _("Почтовый адрес");
      break;
		case "passport":
        return _("Паспортные данные")." <span style='color: red'>*</span>";
			break;
      case "nechto":
        return _("Какая-то лажа");
      break;
      case "dateB":
			return _("День рождения");
      break;
      case "add_info":
        return _("Примечания");
      break;
      case "category":
        return _("Категория");
      break;      
      case "study_department":
        return _("Учебная группа");
      break;
      case "military_state":
        return _("Воинское звание");
      break;
      case "nation":
        return _("Национальность")."<br /> ("._("племенная принадлежность").") &nbsp;&nbsp;&nbsp;";
      break;
      case "education":
        return _("Образование");
      break;
      case "position_until_study":
        return _("Занимаемая должность")."<br /> "._("до прибытия на учебу");
      break;
      case "family_state";
        return _("Семейное положение")."<br /> ("._("количество членов семьи").")";
      break;
      case "speciality":
        return _("Специальность обучения");
      break;
      case "education_period":
        return _("Сроки обучения по директивам");
      break;
      case "date_of_arriving":
        return _("Дата прибытия в ВУЗ");
      break;
      case "access_level":
        return _("Уровень доступа");
      break;
      case "user_organization_id":
        return _("Личный идентификатор");
      break;
      default:
      	if (function_exists('get_reg_block_title_custom')) {
        	return get_reg_block_title_custom($type);
      	}
      break;
  }
}



function load_metadata($type){
 // загружает метаданные заданного типа из шаблона
	$data = array();
    $i=0;
    switch( $type ){
      case "track":
       $i=0;
       $data[$i][name]="ident";
       $data[$i][type]="text";
       $data[$i][title]=_("Код специальности");
       $data[$i][value]="";
       $data[$i][sub]="";
       $i++;
       $data[$i][name]="sub_ident";
       $data[$i][type]="text";
       $data[$i][title]=_("Направление");
       $data[$i][value]="";
       $data[$i][sub]="";
       $i++;
       $data[$i][name]="all_courses";
       $data[$i][type]="text";
       $data[$i][title]=_("Всего курсов");
       $data[$i][value]=0;
       $data[$i][sub]="";
       $i++;
       $data[$i][name]="all_hours";
       $data[$i][type]="text";
       $data[$i][title]=_("Всего часов");
       $data[$i][value]=0;
       $data[$i][sub]=_("а.ч.");
       $data[$i][name]="all_courses";
       $data[$i][type]="text";
       $data[$i][title]=_("Курсов по выбору");
       $data[$i][value]=0;
       $data[$i][sub]="";
       $i++;
       $data[$i][name]="annot";
       $data[$i][type]="textarea";
       $data[$i][title]=_("Аннотация");
       $data[$i][value]="";
       $data[$i][sub]="";
       $i++;
       $data[$i][name]="control";
       $data[$i][type]="select";
       $data[$i][title]=_("Итоговый контроль");
       $data[$i][value]=_("нет");
       $data[$i][values]=_("нет|экзамен|тест|зачет|экзамен");
       $data[$i][sub]="";

        break;

        case "simple":
            $i=0;
            $data[$i][name]="description";
            $data[$i][type]="fckeditor";
            $data[$i][title]="";
            $data[$i][value]="";
            $data[$i][sub]="";
        break;
        
        case "standart":
              $i = 0;
              $data[$i][name]="authors";
              $data[$i][type]="textarea";
              $data[$i][title]=_("Авторы");
              $data[$i][value]="";
              $data[$i][sub]="";
              $i++;
              $data[$i][name]="units";
              $data[$i][type]="text";
              $data[$i][title]=_("Кол-во модулей (тем)");
              $data[$i][value]=0;
              $data[$i][sub]=_("шт.");
              $i++;
              $data[$i][name]="units_vol";
              $data[$i][type]="text";
              $data[$i][title]="";
              $data[$i][value]=0;
              $data[$i][sub]=_("ч.");
              $i++;
              $data[$i][name]="annot";
              $data[$i][type]="textarea";
              $data[$i][title]=_("Аннотация");
              $data[$i][value]="";
              $data[$i][sub]="";
              $i++;
              $data[$i][name]="control";
              $data[$i][type]="select";
              $data[$i][title]=_("Итоговый контроль");
              $data[$i][value]=_("нет");
              $data[$i][values]=_("нет|экзамен|тест|зачет|экзамен");
              $data[$i][sub]="";
        break;
        
        case "military":
              $i = 0;
              $data[$i][name]="authors";
              $data[$i][type]="textarea";
              $data[$i][title]=_("Авторы");
              $data[$i][value]="";
              $data[$i][sub]="";
              $i++;
              $data[$i][name]="annot";
              $data[$i][type]="textarea";
              $data[$i][title]=_("Аннотация");
              $data[$i][value]="";
              $data[$i][sub]="";
              $i++;
              $data[$i][name]="targets";
              $data[$i][type]="textarea";
              $data[$i][title]=_("I. Цели и задачи подготовки");
              $data[$i][value]="";
              $data[$i][sub]="";
              $i++;
              $data[$i][name]="req";
              $data[$i][type]="textarea";
              $data[$i][title]=_("II. Подготовка производится в соответствии с требованиями");
              $data[$i][value]="";
              $data[$i][sub]="";
              $i++;
              $data[$i][name]="ware";
              $data[$i][type]="textarea";
              $data[$i][title]=_("III. Учебно-материальная база подготовки");
              $data[$i][value]="";
              $data[$i][sub]="";

              $i++;
              $data[$i][name]="tutorial";
              $data[$i][type]="textarea";
              $data[$i][title]=_("V. Организационно-методические указания");
              $data[$i][value]="";
              $data[$i][sub]="";
        break;  
      /*  
      case "item":
       $i=0;
       $data[$i][name]="annot";
       $data[$i][type]="textarea";
       $data[$i][title]=_("");
       $data[$i][value]="";
       $data[$i][sub]="";
      break;
      */
      case "item":
       $i=0;
       $data[$i][name]="item_type";
       $data[$i][type]="select";
       $data[$i][title]=_("Тип занятия");
       $data[$i][value]="";
       $data[$i][sub]="";
       $i++;
       $data[$i][name]="item_duration";
       $data[$i][type]="text";
       $data[$i][title]=_("Длительность");
       $data[$i][value]="";
       $data[$i][sub]="академ. час";
      break;
      case "item_old":
       $i=0;
       $data[$i][name]="lectures";
       $data[$i][type]="text";
       $data[$i][title]=_("Лекции");
       $data[$i][value]=0;
       $data[$i][sub]=_("ч.");
       $i++;
       $data[$i][name]="workshop";
       $data[$i][type]="text";
       $data[$i][title]=_("Семинары");
       $data[$i][value]=0;
       $data[$i][sub]=_("ч.");
       $i++;
       $data[$i][name]="labs";
       $data[$i][type]="text";
       $data[$i][title]=_("Лабораторные");
       $data[$i][value]=0;
       $data[$i][sub]=_("ч.");
      $i++;
       $data[$i][name]="courserwork";
       $data[$i][type]="text";
       $data[$i][title]=_("КСР");
       $data[$i][value]=0;
       $data[$i][sub]=_("ч.");

       $i++;
       $data[$i][name]="sum";
       $data[$i][type]="sum";
       $data[$i][title]=_("Итого");
       $data[$i][value]="0+1+2+3";
       $data[$i][sub]=_("ч.");
       $i++;
       $data[$i][name]="annot";
       $data[$i][type]="textarea";
       $data[$i][title]=_("Аннотация");
       $data[$i][value]="";
       $data[$i][sub]="";
       $i++;
       $data[$i][name]="control";
       $data[$i][type]="select";
       $data[$i][title]=_("Текущий контроль");
       $data[$i][value]=_("нет");
       $data[$i][values]=_("нет|экзамен|к.р.|реферат|тест|зачет");
       $data[$i][sub]="";

        break;
      case "module":

       $data[$i][name]="annot";
       $data[$i][type]="textarea";
       $data[$i][title]=_("аннотация");
       $data[$i][value]="";
       $data[$i][sub]="";

      break;
      case "job":

       $data[$i][name]="department";
       $data[$i][type]="text";
       $data[$i][title]=_("департамент");
       $data[$i][value]="";
       $data[$i][sub]="";
       $data[$i][db_id]=""; // идентификатор в БД
       $i++;

       $data[$i][name]="position";
       $data[$i][type]="text";
       $data[$i][title]=_("должность");
       $data[$i][value]="";
       $data[$i][sub]="";
       $data[$i][db_id]="";
       $i++;

       $data[$i][name]="category";
       $data[$i][type]="text";
       $data[$i][title]=_("должность");
       $data[$i][value]="";
       $data[$i][sub]="";
       $data[$i][db_id]="";
       $i++;

       $data[$i][name]="place";
       $data[$i][type]="text";
       $data[$i][title]=_("место");
       $data[$i][value]="";
       $data[$i][sub]="";
       $data[$i][db_id]="";
       $i++;

       $data[$i][name]="date";
       $data[$i][type]="text";
       $data[$i][title]=_("принят");
       $data[$i][value]="";
       $data[$i][sub]=_("дата поступления на работу");
       $data[$i][db_id]="";
       $i++;

      break;

      case "military_state":
       $data[$i][name]="rank";
       $data[$i][type]="select";
       $data[$i][title]="";
       $data[$i][value]="";
       $query = "SELECT Title FROM rank ORDER BY rnid";
       $result = sql($query);
       $data[$i][values] = "";
       while($row = sqlget($result)) {
             $data[$i][values] .= $row['Title']."|";
       }
       
       $data[$i][values]=trim($data[$i][values], "|");
       $i++;
     //#########################################################################
     /*  $data[$i][name]  = "day_awarding";
       $data[$i][type]  = "select";
       $data[$i][title] = "Дата&nbsp;присвоения:&nbsp;<br>";
       $data[$i][value] = "";
       $data[$i][values]= "1|2|3|4|5|6|7|8|9|10|11|12|13|14|15|16|17|18|19|20|21|22|23|24|25|26|27|28|29|30|31";
       $data[$i][flow] = "line";
       $i++;

       $data[$i][name]  = "month_awarding";
       $data[$i][type]  = "select";
       $data[$i][title] = "";
       $data[$i][value] = "";
       $data[$i][values]= "Январь|Февраль|Март|Апрель|Май|Июнь|Июль|Август|Сентябрь|Октябрь|Ноябрь|Декабрь";
       $data[$i][flow] = "line";
       $i++;

       $data[$i][name]  = "year_awarding";
       $data[$i][type]  = "select";
       $data[$i][title] = "";
       $data[$i][value] = "";
       $data[$i][values] = "";
       for($j = ELDER; $j <= date("Y", time()); $j++) {
          $data[$i][values] .= $j."|";
       }
       $data[$i][values] = trim($data[$i][values], "|");
       $i++;
     //#########################################################################
       $data[$i][name]="position";
       $data[$i][type]="text";
       $data[$i][title]="Должность: ";
       $data[$i][value]="";
       $data[$i][values]="";
       $data[$i][size]="40";
       $i++;

       $data[$i][name]="year_of_position";
       $data[$i][type]="select";
       $data[$i][title]="С&nbsp;какого&nbsp;года&nbsp;в&nbsp;должности:<br>";
       $data[$i][value]="";
       $data[$i][values] = "";
       for($j = ELDER; $j <= date("Y", time()); $j++) {
          $data[$i][values] .= $j."|";
       }
       $data[$i][values] = trim($data[$i][values], "|");
       $i++;

       $data[$i][name]="prev_position";
       $data[$i][type]="text";
       $data[$i][title]="Предыдущая должность: ";
       $data[$i][value]="";
       $data[$i][size]="40";
       $i++;*/

      break;
        
      case "study_department":
       $data[$i][name]  = "study_department";
       $data[$i][type]  = "text";
       $data[$i][title] = "";
       $data[$i][value] = "";
       $data[$i][flow] = "line";

      break;
      
      case "access_level":
       $data[$i][name] = "access_level";
       $data[$i][type] = "select";
       $data[$i][value] = "10";
       $data[$i][values]= "0|1|2|3|4|5|6|7|8|9|10";
       $data[$i][flow] = "line";
      break;

      case "user_organization_id":
       $data[$i][name]  = "user_organization_id";
       $data[$i][type]  = "text";
       $data[$i][title] = "";
       $data[$i][size] = 28;
       $data[$i][value] = "";
       $data[$i][flow] = "line";
      break;
      
      case "passport":

       $data[$i][name]="serial";
       $data[$i][type]="text";
       $data[$i][reg_exp] = "^[0-9][0-9] ?[0-9][0-9]$";
       $data[$i][title]=_("серия");
       $data[$i][size]="4";
       $data[$i]['helper'] = "12 34";
       $data[$i]['not_public'] = true;
       $i++;

       $data[$i][name]="number";
       $data[$i][type]="text";
       $data[$i][reg_exp] = "^[0-9][0-9][0-9][0-9][0-9][0-9]$";
       $data[$i][title]=_("номер");
       $data[$i][value]="";
       $data[$i][sub]="";
       $data[$i][size]="10";
       $data[$i]['helper'] = "123456";
       $data[$i]['not_public'] = true;
       $i++;

       $data[$i][name]="date";
       $data[$i][type]="note";
       $data[$i][title]=_("дата выдачи");
       $data[$i][flow]="line";
       $data[$i]['not_public'] = true;
       $i++;

       $data[$i][name] = "day";
       $data[$i][type] = "select";
       $data[$i][value] = "";
       $data[$i][values]= "1|2|3|4|5|6|7|8|9|10|11|12|13|14|15|16|17|18|19|20|21|22|23|24|25|26|27|28|29|30|31";
       $data[$i][flow] = "line";
       $data[$i]['not_public'] = true;
       $i++;

       $data[$i][name]  = "month";
       $data[$i][type]  = "select";
       $data[$i][title] = "";
       $data[$i][value] = "";
			$data[$i][values]= _("Января")."|"._("Февраля")."|"._("Марта")."|"._("Апреля")."|"._("Мая")."|"._("Июня")."|"._("Июля")."|"._("Августа")."|"._("Сентября")."|"._("Октября")."|"._("Ноября")."|"._("Декабря");
       $data[$i][flow] = "line";
       $data[$i]['not_public'] = true;
       $i++;

       $data[$i][name]  = "year";
       $data[$i][type]  = "select";
       $data[$i][title] = "";
       $data[$i][value] = "";
       $data[$i][values] = "";
       for($j = ELDER; $j <= date("Y", time()); $j++) {
          $data[$i][values] .= $j."|";
       }
       $data[$i][values] = trim($data[$i][values], "|");
       $data[$i]['not_public'] = true;
       $i++;
     break;

      case "dateB":
       $data[$i][name]  = "dayB";
       $data[$i][type]  = "select";
       $data[$i][title] = "";
       $data[$i][value] = "";
       $data[$i][values]= "1|2|3|4|5|6|7|8|9|10|11|12|13|14|15|16|17|18|19|20|21|22|23|24|25|26|27|28|29|30|31";
       $data[$i][flow] = "line";
       $data[$i]['not_public'] = true;
       $i++;

       $data[$i][name]  = "dayM";
       $data[$i][type]  = "select";
       $data[$i][title] = "";
       $data[$i][value] = "";
			$data[$i][values]= _("Января")."|"._("Февраля")."|"._("Марта")."|"._("Апреля")."|"._("Мая")."|"._("Июня")."|"._("Июля")."|"._("Августа")."|"._("Сентября")."|"._("Октября")."|"._("Ноября")."|"._("Декабря");
       $data[$i][flow] = "line";
       $data[$i]['not_public'] = true;
       $i++;

       $data[$i][name]  = "dayY";
       $data[$i][type]  = "select";
       $data[$i][title] = "";
       $data[$i][value] = "";
       $data[$i][values] = "";
       for($j = ELDER; $j <= date("Y", time()); $j++) {
          $data[$i][values] .= $j."|";
       }
       $data[$i][values] = trim($data[$i][values], "|");
       $data[$i]['not_public'] = true;
       $i++;
      break;

     case "address_postal":

       $data[$i][name]="index";
       $data[$i][type]="text";
       $data[$i][title]=_("почт. индекс");
       $data[$i][size]="10";
       $data[$i][value] = "";
       $data[$i]['not_public'] = true;
       $i++;

       $data[$i][name]="country";
       $data[$i][type]="text";
       $data[$i][title]=_("страна");
       $data[$i][size]="20";
       $data[$i][value] = "";
       $data[$i]['not_public'] = true;
       $i++;

       $data[$i][name]="region";
       $data[$i][type]="text";
       $data[$i][title]=_("область, регион");
       $data[$i][size]="40";
       $data[$i][value] = "";
       $data[$i]['not_public'] = true;
       $i++;

       $data[$i][name]="city";
       $data[$i][type]="text";
       $data[$i][title]=_("город");
       $data[$i][size]="40";
       $data[$i][value] = "";
       $data[$i]['not_public'] = true;
       $i++;

       $data[$i][name]="street";
       $data[$i][type]="text";
       $data[$i][title]=_("улица");
       $data[$i][size]="40";
       $data[$i][value] = "";
       $data[$i]['not_public'] = true;
       $i++;

       $data[$i][name]="bldng";
       $data[$i][type]="text";
       $data[$i][title]=_("дом");
       $data[$i][value] = "";
       $data[$i]['not_public'] = true;
       $i++;

       $data[$i][name]="section";
       $data[$i][type]="text";
       $data[$i][title]=_("корпус/строение");
       $data[$i][value] = "";
       $data[$i]['not_public'] = true;
       $i++;

       $data[$i][name]="room";
       $data[$i][type]="text";
       $data[$i][title]=_("квартира");
       $data[$i][value] = "";
       $data[$i]['not_public'] = true;
       $i++;

     break;
     case "contacts":
       $data[$i]['name']  = "PhoneNumber";
       $data[$i]['type']  = "text";
       $data[$i]['title'] = _("телефон");
       $data[$i]['size']  = "15";
       $data[$i][value] = "";
	   $data[$i]['helper'] = "+7 (495) 123-45-67";
       $i++;

			$data[$i][name]  = "ICQNumber";
       $data[$i][type]  = "text";
       $data[$i][title] = "icq";
       $data[$i][size]  = "10";
       $data[$i][value] = "";
			$i++;

       $data[$i][name]  = "CellularNumber";
       $data[$i][type]  = "text";
       $data[$i][title] = _("моб. телефон");
       $data[$i][size]  = "15";
       $data[$i][value] = "";
	   $data[$i]['helper'] = "+7 (495) 123-45-67";
       $i++;

			$data[$i][name]  = "Fax";
       $data[$i][type]  = "text";
       $data[$i][title] = _("факс");
       $data[$i][size]  = "15";
       $data[$i][value] = "";
			$i++;
     break;

     case "group":
       $data[$i][name]="nick";
       $data[$i][type]="text";
       $data[$i][title]=_("код группы");
       $i++;

       $data[$i][name]="chief";
       $data[$i][type]="text";
       $data[$i][title]=_("старший группы");
       $data[$i][size]="20";
       $i++;

       $data[$i][name]="info";
       $data[$i][type]="textarea";
       $data[$i][title]=_("характеристика");
       $data[$i][size]="20";
       $i++;

     break;
     case "nation":
       $data[$i][name] = "nation";
       $data[$i][type] = "text";
       $data[$i][title] = "";
       $data[$i][size] = "45";
       $i++;
     break;
     case "education":
       $data[$i][name] = "common";
       $data[$i][type] = "textarea";
       $data[$i][title] = _("общее");
       $i++;
       
       $data[$i][name] = "military";
       $data[$i][type] = "textarea";
       $data[$i][title] = _("военное");
       $i++;
     break;
     case "position_until_study":
       $data[$i][name] = "position_until_study";
       $data[$i][type] = "text";
       $data[$i][title] = "";
       $data[$i][size] = "45";
       $i++;
     break;
     case "family_state":
       $data[$i][name] = "family_state";
       $data[$i][type] = "textarea";
       $data[$i][title] = "";
       $i++;
     break;
     case "speciality":
       $data[$i][name] = "speciality";
       $data[$i][type] = "text";
       $data[$i][title] = "";
       $data[$i][size] = "45";
       $i++;
     break;
     case "dateB":
       $data[$i][name]  = "dayB";
       $data[$i][type]  = "select";
       $data[$i][title] = "";
       $data[$i][value] = "";
       $data[$i][values]= "1|2|3|4|5|6|7|8|9|10|11|12|13|14|15|16|17|18|19|20|21|22|23|24|25|26|27|28|29|30|31";
       $data[$i][flow] = "line";
       $data[$i]['not_public'] = true;
       $i++;

       $data[$i][name]  = "dayM";
       $data[$i][type]  = "select";
       $data[$i][title] = "";
       $data[$i][value] = "";
			$data[$i][values]= _("Января")."|"._("Февраля")."|"._("Марта")."|"._("Апреля")."|"._("Мая")."|"._("Июня")."|"._("Июля")."|"._("Августа")."|"._("Сентября")."|"._("Октября")."|"._("Ноября")."|"._("Декабря");
       $data[$i][flow] = "line";
       $data[$i]['not_public'] = true;
       $i++;

       $data[$i][name]  = "dayY";
       $data[$i][type]  = "select";
       $data[$i][title] = "";
       $data[$i][value] = "";
       $data[$i][values] = "";
       for($j = ELDER; $j <= date("Y", time()); $j++) {
          $data[$i][values] .= $j."|";
       }
       $data[$i][values] = trim($data[$i][values], "|");
       $data[$i][flow] = "line";
       $data[$i]['not_public'] = true;

     break;
     case "add_info":
       $data[$i][name]  = "free_text";
       $data[$i][type]  = "textarea";
       $data[$i][title] = "";
       $data[$i][value] = "";
       $data[$i][flow] = "line";
       $data[$i]['not_public'] = false;
     break;
     case "education_period":
       $data[$i][name]  = "day_of_education_begin";
       $data[$i][type]  = "select";
       $data[$i][title] = _("начало:");
       $data[$i][value] = "";
       $data[$i][values]= "1|2|3|4|5|6|7|8|9|10|11|12|13|14|15|16|17|18|19|20|21|22|23|24|25|26|27|28|29|30|31";
       $data[$i][flow] = "line";
       $i++;

       $data[$i][name]  = "month_of_education_begin";
       $data[$i][type]  = "select";
       $data[$i][title] = "";
       $data[$i][value] = "";
			$data[$i][values]= _("Января")."|"._("Февраля")."|"._("Марта")."|"._("Апреля")."|"._("Мая")."|"._("Июня")."|"._("Июля")."|"._("Августа")."|"._("Сентября")."|"._("Октября")."|"._("Ноября")."|"._("Декабря");
       $data[$i][flow] = "line";
       $i++;

       $data[$i][name]  = "year_education_begin";
       $data[$i][type]  = "select";
       $data[$i][title] = "";
       $data[$i][value] = "";
       $data[$i][values] = "";
       for($j = ELDER; $j <= date("Y", time()); $j++) {
          $data[$i][values] .= $j."|";
       }
       $data[$i][values] = trim($data[$i][values], "|");
       $i++;
       
       $data[$i][name]  = "day_of_education_end";
       $data[$i][type]  = "select";
       $data[$i][title] = _("окончание:")." ";
       $data[$i][value] = "";
       $data[$i][values]= "1|2|3|4|5|6|7|8|9|10|11|12|13|14|15|16|17|18|19|20|21|22|23|24|25|26|27|28|29|30|31";
       $data[$i][flow] = "line";
       $i++;

       $data[$i][name]  = "month_of_education_end";
       $data[$i][type]  = "select";
       $data[$i][title] = "";
       $data[$i][value] = "";
       $data[$i][values]= _("Январь")."|"._("Февраль")."|"._("Март")."|"._("Апрель")."|"._("Май")."|"._("Июнь")."|"._("Июль")."|"._("Август")."|"._("Сентябрь")."|"._("Октябрь")."|"._("Ноябрь")."|"._("Декабрь");
       $data[$i][flow] = "line";
       $i++;

       $data[$i][name]  = "year_education_end";
       $data[$i][type]  = "select";
       $data[$i][title] = "";
       $data[$i][value] = "";
       $data[$i][values] = "";
       for($j = ELDER; $j <= date("Y", time()); $j++) {
          $data[$i][values] .= $j."|";
       }
       $data[$i][values] = trim($data[$i][values], "|");
      break;
      case "date_of_arriving":
       $data[$i][name]  = "day_of_arriving";
       $data[$i][type]  = "select";
       $data[$i][title] = "";
       $data[$i][value] = "";
       $data[$i][values]= "1|2|3|4|5|6|7|8|9|10|11|12|13|14|15|16|17|18|19|20|21|22|23|24|25|26|27|28|29|30|31";
       $data[$i][flow] = "line";
       $i++;

       $data[$i][name]  = "month_of_arriving";
       $data[$i][type]  = "select";
       $data[$i][title] = "";
       $data[$i][value] = "";
			$data[$i][values]= _("Января")."|"._("Февраля")."|"._("Марта")."|"._("Апреля")."|"._("Мая")."|"._("Июня")."|"._("Июля")."|"._("Августа")."|"._("Сентября")."|"._("Октября")."|"._("Ноября")."|"._("Декабря");
       $data[$i][flow] = "line";
       $i++;

       $data[$i][name]  = "year_of_arriving";
       $data[$i][type]  = "select";
       $data[$i][title] = "";
       $data[$i][value] = "";
       $data[$i][values] = "";
       for($j = ELDER; $j <= date("Y", time()); $j++) {
          $data[$i][values] .= $j."|";
       }
       $data[$i][values] = trim($data[$i][values], "|");
      break;

  

     break;

     $data[$i][name]="";
     $data[$i][type]="text";
     $data[$i][title]=_("описание");
     $data[$i][value]="";
     $data[$i][sub]=_("описание без метаданных");
     $i++;

      default:
      	if (function_exists('load_metadata_custom')){
	      	$data = load_metadata_custom($type);
      	}
    }

	return( $data );
}


function change_data( &$meta, $name, $data ){
	// если в исходных данных есть указанное данное то заменяет его значение

	if( count( $meta ) > 0 ){
		$n=count( $meta );
		for( $i = 0; $i < $n; $i++ ){
			if( $meta[$i][name]==$name ){
				$meta[$i][value]=$data;
				//        echo $meta[$i][name]."=".$meta[$i][value]."<BR>";
				break;
			}
		}
	}
	return( $tmp );
}

function set_metadata( $all_data, $names, $type ){
	// возвращает строку - содержащую метаданные для записи в БД
	// формирует ее из массива all_data по тем именам, которые в names
	//  $i=0;
	//  foreach( $all_data as $dat ){
	//     echo $names[$i]."=$dat<BR>";
	//     $i++;
	//   }
	$source_meta=load_metadata( $type ); // загрузить метаданные этого типа
	if( count( $names )>0 ){
		foreach( $names as $name ){

            // process file type
            $mid = $_SESSION['s']['mid'];
            if (isset($_REQUEST['MID'])) {
                $mid = (int) $_REQUEST['MID'];
            }
            if (isset($_FILES[$name]) && count($_FILES[$name])) {
                if ($mid > 0) {
                    $path = $GLOBALS['wwf'].'/options/metadata/'.$mid.'/';
                    if (strstr($name, 'custom_metadata_item') != false) {
                        @mkdir($GLOBALS['wwf'].'/options/metadata/');
                        @mkdir($path);
                        @unlink($path.basename($name));
                        if (move_uploaded_file($_FILES[$name]['tmp_name'], $path.basename($name.'.dat'))) {
                            $_POST[$name] = $all_data[$name] = basename($_FILES[$name]['name']);
                       }
                    }
                }
                unset($_FILES[$name]);
            }
            
            //      if( isset( $all_data[$name] ) ){
			//       echo $name."->".$all_data[$name]."<BR>";
			change_data( $source_meta, $name, $all_data[$name] ); // если в исходных данных есть указанное данное то заменяет его значение
			//      $tmp.=$name."<BR>";
			//      }
		}
	}
	//  echo $tmp;

	//   foreach( $names as $name )
	//     echo $name."<BR>";
	$tmp.=makeTextFromMeta( $source_meta, $type );
	return( $tmp );
}

function get_posted_names( $post ){
	// получеат из массива POST посланные метаданные - в пост содержаться имена и значения имен
	// ищет в них те поля которые у нас есть в meta
	// если item!=0 то для этого itema
	$data=array();

	if( count ($post)>0 ){
		$names=array_keys( $post );
		foreach( $names as $name ){
			$data[]=$name;
		}
	}
	return( $data );
}


function getvalue( $name, $post ){
	return( $post[$name] );
}

function getmetavalue( $metadata, $name ){  // выбирает из метаданных значение даннного с именем name
	foreach( $metadata as $data ){
		if( $data[name]==$name )
		$ret=$data[value];
	}
	return( $ret );
}

function read_metadata($text, $type=""){
	if( $text != "" ){
		$meta=parse_meta($text, $type);
	}
	else { // метапустые - надо инициализировать
		$meta = load_metadata($type);
	}
	return $meta;
}


function view_metadata_as_text($metadata, $type = ""){
	// разбирает метаднные элемена и формирует в текст
	$tmp = "";
	if($type != "") {
		//здесь происходит проверка совпадют ли метаданные с дефолтовыми
		//определенные в функции load_metadata если да то ничего н епоказываем
		$default_metadata = load_metadata($type);
		$no_difference = true;
		if(is_array($metadata))
		foreach($metadata as $key => $value) {
			if(is_array($value))
			foreach($value as $sub_key => $sub_value) {
				if(trim($value[$sub_key]) != trim($default_metadata[$key][$sub_key]) ) {
					$no_difference = false;
				}
			}
		}

		if($no_difference) {
			return "";
		}
	}
	if( count( $metadata ) > 0 ) {
		$n=0;
		$tmp.="<TABLE cellpadding=4 cellspacing=1 border='0'><TR>";
		$spaces="&nbsp;&nbsp;&nbsp;";
		$cells = array();
		$empty = true;
		foreach( $metadata as $data ){
			$cell = '';
			if (!empty($data['value'])) $empty = false;
			switch( $data[type]){
				case "select";
				if (($data['flow'] != 'line') && strlen($data[title])) {
					$cell.="<td width=40%><nobr>$spaces".$data[title]."</nobr></td>";
				}
				$cell.="<td> ".$data[value]." ".$data[sub]."</td>";
				$n++;
				break;
				case "text";
				if (($data['flow'] != 'line') && strlen($data[title])) {
					$cell.="<td width=40%><nobr>$spaces".$data[title].":</nobr></td>";
				}
				$cell.="<td> ".$data[value]." ".$data[sub]."</td>";
				$n++;
				break;
				case "note";
				if (strlen($data[title])) {
					$cell.="<td width=40%><nobr>$spaces".$data[title].":</nobr></td>";
				}
				$cell.="<td> ".$data[sub]."</td>";
				$n++;
				break;
				case "sum";
				$nums=explode("+",$data[value]);
				$sum=0;
				foreach( $nums as $num ) $sum+=$metadata[$num][value];
				if (($data['flow'] != 'line') && strlen($data[title])) {
					$cell.="<td width=40%>$spaces".$data[title].":<span name='".$data[name]."'></td>";
				}
				$cell.="<td> ".$sum." ".$data[sub]."</span></td>";
				$n++;
				break;
				case "textarea"; case "fckeditor";
				$cell.=strlen($data[title]) ? "<td width=40%><nobr>$spaces".$data[title].": </td><td>" : "<td colspan='2'>";
				$cell.=nl2br($data[value])." ".$data[sub]."</td>";
				$n++;
				break;
				default:
					break;
			}
			if (strlen($cell)) {
				if ($data['flow'] != 'line') {
					$tmp .= implode('&nbsp;', $cells) . $cell . "</tr><tr>";
					$cells = array();
				} else {
					$cells[] = $cell;
				}
			}
		}
		if (count($cells)) $tmp .= implode('&nbsp;', $cells);
		$tmp.="</TR></TABLE>";
		if( $n==0 ) $tmp="";
	}
	return !$empty ? $tmp : '<p class="nodata">' . _('нет данных') . '</p>';
}

function edit_metadata( $metadata, $button="", $editable = false){
   if (!count($metadata)) return "";
   $tmp.="<TABLE width=100% border=0 class='brdr'>";
   if( count( $metadata)> 0 ) {
      foreach( $metadata as $data) {
         $readonly = "";
         if ($editable && !$data['editable'] && ($_SESSION['s']['perm']!=4)) $readonly = 'disabled="true"';
         if($data['title'])
         {
             $data['title'] = (isset($data[reg_exp]) && $data[reg_exp])? $data['title'].'<span style="color:red">*</span>' : $data['title'];
             $data['title'] = '<div style="width:200px">'.$data['title'].'</div>';
         }
         if($flow=="line" )
            $tmp.="";
         else
            $tmp.="<TR class=message>";
         switch( $data[type]) {
            case 'select_text':
               if(isset ($data[size]))
                  $size=$data[size];
               else
                  $size=3;
               $opts = array();                                
               if (strlen($data['values'])) {
                  $opts = explode("|", $data['values']); 
               }
               if (function_exists($data['func'])) {
                  $func = trim($data['func']);
                  $opts = $func();
               }
               $sopt = "";
               $mean_value = MEAN;
               $real_value = $data['value'];
               $value_to_compare = (empty($real_value) && (in_array($data['name'], array('year', 'dayY')))) ? "mean_value" : "real_value";
               $selected = false;
               foreach( $opts as $opt) {
                  if($$value_to_compare == $opt) {
                     $sel="selected";
                     $selected = true;
                  }
                  else
                     $sel="";
                  $sopt.="<OPTION $sel value='{$opt}'>".$opt."</OPTION>";
               }
               if($flow != "line") {
                  $tmp .= "<td>";
               }
  			   $tmp .= $data[title]. (($flow != "line") ? "</td><td>" : "") ."<SELECT $readonly name='".$data[name]."'>$sopt</SELECT>".(strlen($readonly) ? "<input type=\"hidden\" name=\"{$data[name]}\" value=\"".htmlspecialchars($data['value'])."\"/>" : '').$data[sub];
               if (!empty($data['whatisit'])) {
                   if (empty($data['helper'])) {
                       $tmp .= "<br>";
                   }
                   $tmp .= "<a href=\"javascript:void(0)\" onClick=\"alert('".htmlspecialchars(str_replace("'",'"',$data['whatisit']),ENT_QUOTES)."');\"><img src=\"{$GLOBALS['controller']->view_root->skin_url}/images/help2.gif\" hspace=2 border=0 title=\""._('помощь')."\"></a>";
               }
               $flow=$data[flow];
               if($flow!="line") {
				  $tmp .= " &nbsp; <input $readonly type=\"text\" name=\"{$data['name']}_value\" size=$size value=\"";
                  if (!$selected) {
                      $tmp .= $$value_to_compare;
                  }
                  $tmp .= "\">";
                  $tmp .= (strlen($readonly) ? "<input type=\"hidden\" name=\"{$data[name]}_value\" value=\"".htmlspecialchars($value_to_compare)."\"/>" : '');
                  $tmp.="</td>";  
               }    
            break;
            case "select";
               $opts=explode("|",$data[values]);
               $sopt = "";
               $mean_value = MEAN;
               $real_value = $data['value'];
               $value_to_compare = (empty($real_value) && (in_array($data['name'], array('year', 'dayY')))) ? "mean_value" : "real_value";
               foreach( $opts as $opt) {
                  if( $$value_to_compare == $opt )
                     $sel="selected";
                  else
                     $sel="";
                  $sopt.="<OPTION $sel value='{$opt}'>".$opt."</OPTION>";
               }
               if($flow != "line")
                  $tmp.="<td>";
				$tmp.=$data[title]. (($flow != "line") ? "</td><td>" : "") ."<div style='width:333px'><SELECT $readonly name='".$data[name]."'>$sopt</SELECT></div>".(strlen($readonly) ? "<input type=\"hidden\" name=\"{$data[name]}\" value=\"".htmlspecialchars($data['value'])."\"/>" : '').$data[sub];
               if (!empty($data['whatisit'])) {
                   if (empty($data['helper'])) {
                       $tmp .= "<br>";
                   }
                   $tmp .= "<a href=\"javascript:void(0)\" onClick=\"alert('".htmlspecialchars(str_replace("'",'"',$data['whatisit']),ENT_QUOTES)."');\"><img src=\"{$GLOBALS['controller']->view_root->skin_url}/images/help2.gif\" hspace=2 border=0 title=\""._('помощь')."\"></a>";
               }
               $flow=$data[flow];
               if($flow!="line")
                  $tmp.="</td>";
            break;
            case "file";
               if(isset ($data[size]))
                  $size=$data[size];
               else
                  $size=3;
               if( $flow!="line" )
                  $tmp.="<td>";
               $tmp.= $data[title] . (($flow != "line") ? "</td><td>" : "") . "<INPUT ".(strlen($readonly) ? "disabled=true" : '')." name='".$data[name]."' id=\"metadata_{$data['name']}\" type='file' value=''><INPUT name='".$data[name]."' id=\"metadata_{$data['name']}\" type='hidden' value='{$data['value']}'>";
               $mid = $_SESSION['s']['mid'];
               if (isset($_REQUEST['MID'])) {
                   $mid = (int) $_REQUEST['MID'];
               }
               if (isset($_REQUEST['mid'])) {
                   $mid = (int) $_REQUEST['mid'];
               }
               if (strlen($data['value']) && file_exists($GLOBALS['wwf'].'/options/metadata/'.$mid.'/'.$data['name'].'.dat')) {
                   $tmp .= "<br />".$data['value'];
                   $tmp .= " <a href=\"".$GLOBALS['sitepath']."metadata.php?id=$mid;".substr($data['name'], strrpos($data['name'], '_')+1)."&file={$data['value']}\">"._('скачать')."</a>";
                   $tmp .= " <a onClick=\"if (confirm('"._('Удалить?')."')) return true; else return false;\" href=\"".$GLOBALS['sitepath']."metadata.php?id=$mid;".substr($data['name'], strrpos($data['name'], '_')+1)."&file={$data['value']}&action=delete\">"._('удалить')."</a>";
               }
               if (!empty($data['helper'])) {
                   $tmp .= "<br><a href=\"javascript:void(0)\" onClick=\"if (elm = document.getElementById('metadata_{$data['name']}')) {elm.value = '".htmlspecialchars(str_replace("'",'"',$data['helper']),ENT_QUOTES)."';}\"><img src=\"{$GLOBALS['controller']->view_root->skin_url}/images/example.gif\" hspace=2 border=0 title=\""._('вставить пример')."\"></a>";
               }
               if (!empty($data['whatisit'])) {
                   if (empty($data['helper'])) {
                       $tmp .= "<br>";
                   }
                   $tmp .= "<a href=\"javascript:void(0)\" onClick=\"alert('".htmlspecialchars(str_replace("'",'"',$data['whatisit']),ENT_QUOTES)."');\"><img src=\"{$GLOBALS['controller']->view_root->skin_url}/images/help2.gif\" hspace=2 border=0 title=\""._('помощь')."\"></a>";
               }
               $tmp.= $data[sub];
               $flow=$data[flow];
               if($flow!="line")
                  $tmp.="</td>";
            break;
            case "text";
               if(isset ($data[size]))
                  $size=$data[size];
               else
                  $size=3;
               if( $flow!="line" )
                  $tmp.="<td>";
               $tmp.= $data[title] . (($flow != "line") ? "</td><td>" : "") . "<div style='width:333px'><INPUT $readonly name='".$data[name]."' size=".$size." id=\"metadata_{$data['name']}\" type='text' value='".$data[value]."'></div>".(strlen($readonly) ? "<input type=\"hidden\" name=\"{$data[name]}\" value=\"".htmlspecialchars($data['value'])."\"/>" : '');
               if (!empty($data['helper'])) {
                   $tmp .= "<br><a href=\"javascript:void(0)\" onClick=\"if (elm = document.getElementById('metadata_{$data['name']}')) {elm.value = '".htmlspecialchars(str_replace("'",'"',$data['helper']),ENT_QUOTES)."';}\"><img src=\"{$GLOBALS['controller']->view_root->skin_url}/images/example.gif\" hspace=2 border=0 title=\""._('вставить пример')."\"></a>";
               }
               if (!empty($data['whatisit'])) {
                   if (empty($data['helper'])) {
                       $tmp .= "<br>";
                   }
                   $tmp .= "<a href=\"javascript:void(0)\" onClick=\"alert('".htmlspecialchars(str_replace("'",'"',$data['whatisit']),ENT_QUOTES)."');\"><img src=\"{$GLOBALS['controller']->view_root->skin_url}/images/help2.gif\" hspace=2 border=0 title=\""._('помощь')."\"></a>";
               }
               $tmp.= $data[sub];
               $flow=$data[flow];
               if($flow!="line")
                  $tmp.="</td>";
            break;
            case "note";
               if( isset ($data[size]) )
                  $size=$data[size];
               else
                  $size=3;
               if ($flow != 'line') {
                   if ($data[flow] != 'line')
                   $tmp.="<td colspan=2>";
                   else $tmp.="<td>";
               }
               $tmp.=$data[title]." ".$data[sub];
               $flow=$data[flow];
               if ($flow != 'line')
               $tmp.="</td>";
               else $tmp.="</td><td>";

            break;
            case "sum";
               $nums=explode("+",$data[value]);
               $sum=0;
               foreach( $nums as $num )
                  $sum += $metadata[$num][value];
               $tmp.="<td>".$data[title]."</td><td><span name='".$data[name]."'>".$sum." ".$data[sub]."</td>";
            break;
            case "textarea";
               $data['rows'] = $data['rows']?$data['rows']:7;
               $data['cols'] = $data['cols']?$data['cols']:50;
               $tmp.="<td valign=top>".$data[title]."</td><td><TEXTAREA $readonly id=\"metadata_{$data['name']}\" cols='{$data[cols]}' rows='{$data[rows]}' name='".$data[name]."'>".$data[value]."</TEXTAREA>".(strlen($readonly) ? "<input type=\"hidden\" name=\"{$data[name]}\" value=\"".htmlspecialchars($data['value'])."\"/>" : '');
               if (!empty($data['helper'])) {
                   $tmp .= "<br><a href=\"javascript:void(0)\" onClick=\"if (elm = document.getElementById('metadata_{$data['name']}')) {elm.value = '".htmlspecialchars(str_replace("'",'"',$data['helper']),ENT_QUOTES)."';}\"><img src=\"{$GLOBALS['controller']->view_root->skin_url}/images/example.gif\" hspace=2 border=0 title=\""._('вставить пример')."\"></a>";
               }
               if (!empty($data['whatisit'])) {
                   if (empty($data['helper'])) {
                       $tmp .= "<br>";
                   }
                   $tmp .= "<a href=\"javascript:void(0)\" onClick=\"alert('".htmlspecialchars(str_replace("'",'"',$data['whatisit']),ENT_QUOTES)."');\"><img src=\"{$GLOBALS['controller']->view_root->skin_url}/images/help2.gif\" hspace=2 border=0 title=\""._('помощь')."\"></a>";
               }
               $tmp.=$data[sub]."</td>";
            break;
            case "fckeditor";
            	global $sitepath;
                $ob_tmp = ob_get_contents();
                ob_start(); 
                $oFCKeditor = new FCKeditor($data[name]) ;
                $oFCKeditor->BasePath   = "{$sitepath}lib/FCKeditor/";
                $oFCKeditor->Value      = $data[value];
                $oFCKeditor->Width      = 500;
                $oFCKeditor->Height     = 300;
                $fck_code = $oFCKeditor->Create() ;
                $fck_code = ob_get_contents();
                ob_end_clean();            
                ob_start();
                echo $ob_tmp;
                $tmp.="<td valign=top>".$data[title]."</td><td>".$fck_code.$data[sub]."</td>";
            break;
            default:
               foreach( $data as $elem ) {
                  if( $elem!="" )
                     $tmp.=",".$elem;
               }
         }
         if($flow == "line" )
            $tmp.="";
         else
            $tmp.="</tr>";
      }
   }
   $tmp.="</TABLE>";
   if( $button!="" )
      $tmp.="<BR><INPUT type='submit' name='$button' value='"._("сохранить")."'>";
   return( $tmp );
}


function view_metadata( $metadata, $button="" ){
	$tmp.="<TABLE width=100% border=0 class='brdr'>";
	if( is_array($metadata) && count( $metadata)> 0 ) {
		foreach( $metadata as $data) {
			if($flow=="line" )
			$tmp.="";
			else
			$tmp.="<TR class=message>";
			switch( $data[type]) {
				case "select";
				$opts=explode("|",$data[values]);
				$sopt = "";
				foreach( $opts as $opt) {
					if( $opt==$data[value] )
					$sel="selected";
					else
					$sel="";
					$sopt.="<OPTION $sel>".$opt."</OPTION>";
				}
				if($flow != "line")
				$tmp.="<td>";
				$tmp.=$data[title]. (($flow != "line") ? "</td><td>" : "") . "<SELECT disabled name='".$data[name]."'>$sopt</SELECT>".$data[sub];
				$tmp.=$data[value].$data[sub];
				$flow=$data[flow];
				if($flow!="line")
				$tmp.="</td>";
				break;
				case "text";
				if(isset ($data[size]))
				$size=$data[size];
				else
				$size=3;
				if( $flow!="line" )
				$tmp.="<td>";
				$tmp.=$data[title]. (($flow != "line") ? "</td><td>" : "") . "<INPUT disabled name='".$data[name]."' size=".$size." type='text' value='".$data[value]."'>".$data[sub];
				$tmp.=$data[value].$data[sub];
				$flow=$data[flow];
				if($flow!="line")
				$tmp.="</td>";
				break;
				case "note";
				if( isset ($data[size]) )
				$size=$data[size];
				else
				$size=3;
				$tmp.="<td colspan=2>".$data[title]." ".$data[sub]."</td>";
				break;
				case "sum";
				$nums=explode("+",$data[value]);
				$sum=0;
				foreach( $nums as $num )
				$sum += $metadata[$num][value];
				$tmp.="<td>".$data[title]."</td><td><span name='".$data[name]."'>".$sum." ".$data[sub]."</td>";
				break;
				case "textarea";
				$tmp.="<td valign=top>".$data[title]."</td><td><TEXTAREA disabled cols=30 rows=5 name='".$data[name]."'>".$data[value]."</TEXTAREA>".$data[sub]."</td>";
				$tmp.="<td valign=top>".$data[title]."</td><td>".$data[value]."".$data[sub]."</td>";

				break;
				default:
					foreach( $data as $elem ) {
						if( $elem!="" )
						$tmp.=",".$elem;
					}
			}
			if($flow == "line" )
			$tmp.="";
			else
			$tmp.="</tr>";
		}
	}
	$tmp.="</TABLE>";
	if( $button!="" )
	$tmp.="<BR><INPUT type='submit' name='$button' value='"._("сохранить")."'>";
	return( $tmp );
}

function extract_meta($str){
	$meta_blocks = explode(';', REGISTRATION_FORM);
	$meta_data = array();
	foreach ($meta_blocks as $block) {
		$meta_data = array_merge($meta_data, read_metadata($str, $block));
	}
	return $meta_data;
}

function get_meta_fields(){
	$meta_blocks = explode(';', REGISTRATION_FORM);
	$meta_fields = array();
	foreach ($meta_blocks as $block) {
		if(is_array($meta_array = load_metadata($block))){
			foreach ($meta_array as $meta_item) {
				$meta_fields[] = $meta_item['name'];
			}
		}
	}
	return $meta_fields;
}

function view_metadata_as_text_extended($metadata, $type = ""){
  // разбирает метаднные элемена и формирует в текст
  $tmp = "";
  if($type != "") {
    //здесь происходит проверка совпадют ли метаданные с дефолтовыми 
    //определенные в функции load_metadata если да то ничего н епоказываем
    $default_metadata = load_metadata($type);
    $no_difference = true;
    if(is_array($metadata))
    foreach($metadata as $key => $value) {
        if(is_array($value))
        foreach($value as $sub_key => $sub_value) {
            if(trim($value[$sub_key]) != trim($default_metadata[$key][$sub_key]) ) {
                $no_difference = false;
            }
        }
    }
    
    if($no_difference) {
//        return "";
    }
  }
  if( count( $metadata ) > 0 ) {
    $n=0;
		$tmp.="<TABLE width=100% cellpadding=4 cellspacing=0 border='0'>";
    $spaces="&nbsp;&nbsp;&nbsp;";
    foreach( $metadata as $data ){
      $tmp.="<TR>";
      if (empty($data['value'])) $data['value'] = _("не задано");
      switch( $data[type]){
        case "select";
          $tmp.="<td><strong><p>".$data[title]."</strong><br>".$data[value]." ".$data[sub]."</td></tr>";
          $n++;
        break;
        case "text";
          $tmp.="<td><strong><p>".$data[title].":</strong><br>".$data[value]." ".$data[sub]."</td></tr>";
          $n++;
        break;
        case "note";
          $tmp.="<td><strong><p>".$data[title].":</strong><br>".$data[sub]."</td></tr>";
          $n++;
        break;
        case "sum";
          $nums=explode("+",$data[value]);
          $sum=0;
          foreach( $nums as $num )
            $sum+=$metadata[$num][value];
            $tmp.="<td><br>".$data[title].":<span name='".$data[name]."'></td><td> ".$sum." ".$data[sub]."</span></td></tr>";
            $n++;
        break;
        case "textarea"; case "fckeditor";
           $tmp.=strlen($data[title]) ? "<td><strong><p>".$data[title].":</strong>" : "";
           $tmp.="<br>".nl2br($data[value])." ".$data[sub]."</td></tr>";
           $n++;
        break;
        default:
        break;
      }
    }
    $tmp.="</TABLE>";
    if( $n==0 ) $tmp="";
  }
  return( $tmp );
}

?>