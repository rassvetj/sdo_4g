<?php
class HM_View_Helper_Icon extends Zend_View_Helper_Abstract
{
    public function icon($name, $title = '', $onClick = '', $id = '', $class='', $type = 'icon')
    {
        if($type == 'text'){
            return $title;
        }

        if ($type == 'span'){
            return "<span class='icon-custom {$name}' title='{$title}'></span>";
        }

        switch($name) {
            case 'print':
                $url = "/images/icons/print.gif";
                if (empty($title)) {
                    $title = _('Открыть краткий отчёт');
                }
                break;
            case 'view':
                $url = "/images/icons/look.gif";
                if (empty($title)) {
                    $title = _('Открыть карточку');
                }
                break;
            case 'add':
            $url = "/images/icons/add_shedule.gif";
            if (empty($title)) {
                $title = _('Добавить');
            }
            break;
            case 'look':
            $url = "/images/icons/look.gif";
            if (empty($title)) {
                $title = _('Открыть');
            }
            break;
            case 'delete':
            $url = "/images/icons/delete.gif";
            if (empty($title)) {
                $title = _('Удалить');
            }
            if (empty($onClick)) {
                $onClick = "if (confirm('"._('Вы действительно хотите удалить?')."')) return true; return false;";
            }
            break;
            case 'cancel':
            $url = "/images/icons/delete.gif";
            if (empty($title)) {
                $title = _('Отменить');
            }
            if (empty($onClick)) {
                $onClick = "if (confirm('"._('Вы действительно хотите отменить?')."')) return true; return false;";
            }
            break;
            case 'edit':
            $url = "/images/icons/edit.gif";
            if (empty($title)) {
                $title = _('Редактировать');
            }
            break;
            case 'ok':
            $url = "/images/icons/ok.gif";
            if (empty($title)) {
                $title = _('Подтвердить');
            }
            break;
            case 'note2':
            $url = "/images/icons/note2.gif";
            if (empty($title)) {
                $title = _('Опубликовать');
            }
            break;
            break;
            case 'archive':
            $url = "/images/icons/attention.gif";
            if (empty($title)) {
                $title = _('Отправить в архив');
            }
            break;
            case 'develop' :
                $url = "/images/icons/attention.gif";
                if (empty($title))
                {
                    $title = _('Отправить в разработку');
                }
            break;
            case 'useradd' :
                $url = "/images/icons/ok.gif";
                if (empty($title))
                {
                    $title = _('Отправить в разработку');
                }
            break;
             case 'usernotadd' :
                $url = "/images/icons/delete.gif";
                if (empty($title))
                {
                    $title = _('Отправить в разработку');
                }
            break;
            case '{{fixType}}' :
                $url = "/images/icons/{{fixType}}.gif";
                if (empty($title))
                {
                    $title = _('Фиксирование строки');
                }
            break;
            case 'calendar' :
                $url = "/images/icons/calendar.png";
                if (empty($title))
                {
                    $title = _('Календарь');
                }
            break;
            case 'close_cross' :
                $url = "/images/icons/cross.png";
                if (empty($title))
                {
                    $title = _('Закрыть');
                }
            break;

            //изображения для типов впоросов

            case 'type_1' :
                $url = "/images/types/radio.gif";
                if (empty($title))
                {
                    $title = _('одиночный выбор');
                }
            break;
            case 'type_2' :
                $url = "/images/types/check.gif";
                if (empty($title))
                {
                    $title = _('множественный выбор');
                }
            break;
            case 'type_3' :
                $url = "/images/types/core.gif";
                if (empty($title))
                {
                    $title = _('на соответствие');
                }
            break;
            case 'type_4' :
                $url = "/images/types/attach.gif";
                if (empty($title))
                {
                    $title = _('с прикрепленным файлом');
                }
            break;
            case 'type_5' :
                $url = "/images/types/filling.gif";
                if (empty($title))
                {
                    $title = _('заполнение формы');
                }
            break;
            case 'type_6' :
                $url = "/images/types/free.gif";
                if (empty($title))
                {
                    $title = _('свободный ответ');
                }
            break;
            case 'type_7' :
                $url = "/images/types/map.gif";
                if (empty($title))
                {
                    $title = _('выбор по карте на картинке');
                }
            break;
            case 'type_8' :
                $url = "/images/types/radiopics.gif";
                if (empty($title))
                {
                    $title = _('выбор из набора картинок');
                }
            break;
            case 'type_9' :
                $url = "/images/types/blackbox.gif";
                if (empty($title))
                {
                    $title = _('внешний объект');
                }
            break;
            case 'type_10' :
                $url = "/images/types/training.gif";
                if (empty($title))
                {
                    $title = _('тренажер');
                }
            break;
            case 'type_11' :
                $url = "/images/types/check.gif";
                if (empty($title))
                {
                    $title = _('одиночный выбор');
                }
            break;
            case 'type_12' :
                $url = "/images/types/sort.gif";
                if (empty($title))
                {
                    $title = _('на упорядочивание');
                }
            break;
            case 'type_13' :
                $url = "/images/types/class.gif";
                if (empty($title))
                {
                    $title = _('на классификацию');
                }
            break;
            case 'no_type' :
                $url = "/images/icons/no_type.gif";
                if (empty($title))
                {
                    $title = _('Неизвестный тип');
                }
            break;
            case 'card':
                $url = "/images/content-modules/grid/card.gif";
                if (empty($title)) {
                    $title = _('Карточка');
                }
                break;
            default :
                if(preg_match('/^{{.*}}$/i', $name)){
                    $url = "/images/icons/".$name.".gif";
                }
                else{
                  $url = "/images/icons/attention.gif";
                }

                if (empty($title))
                {
                    $title = _('Неизвестное действие');
                }
                break;

        }

        $click = '';
        if (!empty($onClick)) {
            $click = "onClick = \"$onClick\"";
        }

        $classs = "class = \"ui-els-icon $class\"";;

        $idS = '';
        if (!empty($id)) {
            $idS = "id = \"$id\"";
        }
        $arrayWithout = array('card', '{{fixType}}');
        if(in_array($name, $arrayWithout) === false){
            return "<img src=\"$url\" title=\"".$title."\" $click $idS $classs />"."<span ". $click ." >" . $title . "</span>";
        }else{
            return "<img src=\"$url\" title=\"".$title."\" $click $idS $classs />";
        }
    }
}