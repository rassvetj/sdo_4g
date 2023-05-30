<?php
use Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;
/**
 * Weekly events reporting by email
 *
 * @author Slava Tutrinov
 */
class Es_Command_Wreport extends Command {
    
    protected function configure() {
        $this->setName("wreport")
                ->addArgument('c', Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Time units count')
                ->addArgument('m', Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Time units measure')
                ->setDescription("Недельный отчёт по событиям")
                ->setHelp(<<<EOT
Команда делает рассылку по произошедшим в системе событиям
EOT
        );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $timeOffsetParams = $input->getArguments();
        $timeUnitsCount = (int)$timeOffsetParams['c'];
        $timeUnitsMeasure = (string)$timeOffsetParams['m'];
        
        $timeUnitsCount = ($timeUnitsCount <= 0)?1:$timeUnitsCount;
        $timeUnitsMeasure = ($timeUnitsMeasure === '')?'week':$timeUnitsMeasure;
        
        $serviceContainer = Zend_Registry::get('serviceContainer');
        $users = $serviceContainer->getService('User')->getIds();
        /*@var $filter Es_Entity_Filter */
        $filter = $serviceContainer->getService('ESFactory')->newFilter();
        $filter->setUserId($users);
        $filter->setOnlyNotShowed(false);
        
        /*@var $notifyType  Es_Entity_AbstractNotifyType */
        $notifyType = $serviceContainer->getService('ESFactory')->newNotifyType();
        $notifyType->setId(Es_Entity_NotifyType::NOTIFY_TYPE_WEEKLY_EMAIL);
        $filter->setNotifyType($notifyType);
        
        $currentTimestamp = time();
        $fromTimestamp = \strtotime("-".$timeUnitsCount." ".$timeUnitsMeasure, $currentTimestamp);
        $filter->setToTime($currentTimestamp);
        $filter->setFromTime($fromTimestamp);
        
        $esService = $serviceContainer->getService('EventServerDispatcher');
        
        $notifiesPullEventResult = $esService->trigger(
                Es_Service_Dispatcher::EVENT_PULL_NOTIFIES,
                $esService->getEventActor(),
                array('filter' => $filter)
        );
        $notifiesList = $notifiesPullEventResult->getReturnValue();
        /*@var $notify Es_Entity_Notify */
        foreach ($users as $userId) {
            $types = array();
            foreach ($notifiesList as $notify) {
                if ($notify->getUserId() === (int)$userId) {
                    if ($notify->isActive()) {
                        $types[] = $notify->getEventType()->getName();
                    }
                }
            }
            $filter->setUserId($userId);
            $filter->setTypes($types);
            $esService->trigger(
                    Es_Service_Dispatcher::EVENT_REPORT_MAIL_SEND,
                    $esService->getEventActor(),
                    array('filter' => $filter)
            );
        }
    }
    
}

?>
