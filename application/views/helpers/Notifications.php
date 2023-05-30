<?php
require_once "ZendX/JQuery/View/Helper/UiWidget.php";
class HM_View_Helper_Notifications extends ZendX_JQuery_View_Helper_UiWidget
{

    public function notifications($messages, $params = null, $attribs = null)
    {
        if (is_array($messages) && count($messages)) {
            $html = "";
            $js = "";
            $globalClear = "";
            $localClear = "";
            if (!isset($params['html'])) {
                //$params['html'] = true;
            }
            $flush_messages = is_array($params) && $params['html'];
            $mappings = array(
                HM_Notification_NotificationModel::TYPE_NOTICE  => "notice",
                HM_Notification_NotificationModel::TYPE_SUCCESS => "success",
                HM_Notification_NotificationModel::TYPE_ERROR   => "error",
                HM_Notification_NotificationModel::TYPE_CRIT    => "crit"
            );
            foreach($messages as $index => $message) {
                // fallback to TYPE_NOTICE if $message is array && type is not set
                if (is_array($message) && !isset($message['type'])) {
                    $message['type'] = HM_Notification_NotificationModel::TYPE_NOTICE;
                }
                if (is_string($message)) {
                    $message = array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => $message);
                }
                $id = $this->view->id('error-message');

                // we must check type
                if ($message['type'] === HM_Notification_NotificationModel::TYPE_NOTICE
                        || $message['type'] === HM_Notification_NotificationModel::TYPE_SUCCESS
                        || $message['type'] === HM_Notification_NotificationModel::TYPE_ERROR
                        || $message['type'] === HM_Notification_NotificationModel::TYPE_CRIT
                ) {
                    $local_html = '<div id="'.$this->view->escape($id).'" title="'.$this->view->escape($message['short-message']).'">'.($message['hasMarkup']
                        ? $message['message']
                        : $this->view->escape($message['message'])
                    ).'</div>';
                    if ($flush_messages) {
                        $localClear = 'jQuery.ui.errorbox.clear(jQuery('.Zend_Json::encode("#{$id}").'));';
                        $html .= $local_html;
                        $js .= 'jQuery('.Zend_Json::encode("#{$id}").')';
                    } else {
                        $globalClear = 'jQuery.ui.errorbox.clear();';
                        $js .= 'jQuery('.Zend_Json::encode(strval($local_html)).')';
                    }
                    $js .= '.errorbox('.Zend_Json::encode(array( 'level' => $mappings[$message['type']] )).');';
                } elseif ($message['type'] === HM_Notification_NotificationModel::TYPE_INSTANT) {
                    $js .= sprintf("$.gritter.add({title: '%s', image: '%s', text: '%s'});\r\n", 
                         $this->view->escape($message['instantTitle']),   
                         $this->view->escape($message['instantImage']),   
                         $message['message']  
                    );
                }
            }
            $this->jquery->addOnLoad($globalClear.$localClear.$js);

            return $html;
        }
        return '';
    }
}