<?php
        function showparamname($param,$val)
        {
           switch ($param) {
                      case "fontSize"   : echo "<tr><td>"._("Размер шрифта")."</td><td>$val</td>"          ; break;
                      case "color"      : echo "<tr><td>"._("Цвет шрифта")."</td><td>$val</td>"            ; break;
                      case "fontFamily" : echo "<tr><td>"._("Шрифт")."</td><td>$val</td>"                  ; break;
                      case "fontWeight" : echo "<tr><td>"._("Толщина шрифта")."</td><td>$val</td>"         ; break;
                      case "lineHeight" : echo "<tr><td>"._("Отступ между строчками")."</td><td>$val</td>"     ; break;
                      case "cursor"     : echo "<tr><td>"._("Курсор мышки при наведении")."</td><td>$val</td>" ; break;
                      case "backgroundColor" : echo "<tr><td>"._("Цвет фона")."</td><td>$val</td>" ; break;
                      case "borderStyle": echo "<tr><td>"._("Стиль бордюра")."</td><td>$val</td>" ; break;
                      case "borderColor": echo "<tr><td>"._("Цвет бордюра")."</td><td>$val</td>" ; break;
                      case "borderWidth": echo "<tr><td>"._("Толщина бордюра")."</td><td>$val</td>" ; break;
                      case "padding"    : echo "<tr><td>"._("Отступ")."</td><td>$val</td>" ; break;
                      case "paddingTop" : echo "<tr><td>"._("Отступ сверху")."</td><td>$val</td>" ; break;
                      case "paddingLeft": echo "<tr><td>"._("Отступ слева")."</td><td>$val</td>" ; break;
                      case "paddingRight": echo "<tr><td>"._("Отступ справа")."</td><td>$val</td>" ; break;
                      case "paddingBottom": echo "<tr><td>"._("Отступ снизу")."</td><td>$val</td>" ; break;
                      case "textTransform": echo "<tr><td>"._("Текст")."</td><td>$val</td>" ; break;
                      case "whiteSpace": echo "<tr><td>"._("Отступ наверное")."</td><td>$val</td>" ; break;
                      case "letterSpacing": echo "<tr><td>"._("Расстояние между буквами")."</td><td>$val</td>" ; break;
                      case "textAlign": echo "<tr><td>"._("Расположение текста")."</td><td>$val</td>" ; break;
                      case "width": echo "<tr><td>"._("Ширина")."</td><td>$val</td>" ; break;
                      case "height": echo "<tr><td>"._("Высота")."</td><td>$val</td>" ; break;
		      case "display": echo "<tr><td>Display</td><td>$val</td>" ; break;
		      case "borderBottom": echo "<tr><td>"._("Стиль бордюра внизу")."</td><td>$val</td>" ; break;
		      case "borderTop": echo "<tr><td>"._("Стиль бордюра вверху")."</td><td>$val</td>" ; break;
		      case "borderRight": echo "<tr><td>"._("Стиль бордюра справа")."</td><td>$val</td>" ; break;
		      case "borderLeft": echo "<tr><td>"._("Стиль бордюра слева")."</td><td>$val</td>" ; break;
		      case "border"	: echo "<tr><td>"._("Стиль бордюра")."</td><td>$val</td>" ; break;
		      case "textDecoration": echo "<tr><td>"._("Стиль текста")."</td><td>$val</td>" ; break;
                      
                      case  "" : ; break;
                           }
        };


?>