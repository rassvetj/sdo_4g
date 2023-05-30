<?php

/**
 * Description of NotifyType
 *
 * @author slava
 */
class Es_Entity_NotifyType extends Es_Entity_AbstractNotifyType {
    
    const NOTIFY_TYPE_EMAIL = 1;
    const NOTIFY_TYPE_WEEKLY_EMAIL = 2;
    
    protected function notifyTypeDescriptions() {
        return array(
            'Email notifications' => _('Уведомления по почте'),
            'Weekly reports by email' => _('Недельные уведомления о событиях')
        );
    }
    
    public function getLocatedName() {
        $types = $this->notifyTypeDescriptions();
        if (!array_key_exists($this->getName(), $types)) {
            throw new Es_Exception_InvalidArgument('Notify type name \''.$this->getName().'\' has no translation');
        }
        return $types[$this->getName()];
    }
    
}

?>
