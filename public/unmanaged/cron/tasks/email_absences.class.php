<?php

class CCronTask_email_absences extends CCronTask_interface {
    var $mark = -3;
    var $start = false;    
    var $last;
    
    function init() {
        $fname = $GLOBALS['wwf'].'/temp/crontask_email_absences.time';
        $last = strtotime('-1 week');
        if (file_exists($fname)) {
            if ($last = file_get_contents($fname)) {
                $last = trim($last);
            }
        }
        $this->last = $last;
        if (time()>=strtotime('+1 week',$last)) {
            $this->start = true;
        }
        
    }
    
    function run() {
        if (!$this->start) return;
        $sql = "SELECT DISTINCT
                    People.EMail AS email,
                    People.LastName AS lname,
                    People.FirstName AS fname,
                    People.Patronymic AS mname,
                    structure_of_organ.mid, 
                    structure_of_organ.owner_soid, 
                    structure_of_organ.type,
                    scheduleID.V_STATUS AS mark,
                    schedule.SHEID,
                    schedule.Title,
                    schedule.begin,
                    schedule.end,
                    schedule.teacher,
                    teacher.LastName AS teacher_lname,
                    teacher.FirstName AS teacher_fname,
                    teacher.Patronymic AS teacher_mname
                FROM structure_of_organ
                INNER JOIN People ON (People.MID = structure_of_organ.mid)
                LEFT JOIN scheduleID ON (scheduleID.MID=structure_of_organ.mid)
                LEFT JOIN schedule ON (schedule.SHEID=scheduleID.SHEID)
                LEFT JOIN People AS teacher ON (teacher.MID=schedule.teacher)
                WHERE structure_of_organ.type IN ('0','1')
                AND schedule.end>='".date('Y-m-d 00:00:00',$this->last)."' 
                AND schedule.end<='".date('Y-m-d 00:00:00')."'";
        
        $res = sql($sql);
        while($row = sqlget($res)) {            
            switch($row['type']) {
                case 0:
                    if ($row['mark'] == $this->mark) {
                        $schedules = $data[$row['owner_soid']]['people'][$row['mid']]['schedules'];
                        $data[$row['owner_soid']]['people'][$row['mid']] = $row;
                        $data[$row['owner_soid']]['people'][$row['mid']]['schedules'] = $schedules;
                        $data[$row['owner_soid']]['people'][$row['mid']]['schedules'][$row['SHEID']] = $row;                       
                    }
                break;
                case 1:
                    $data[$row['owner_soid']]['heads'][$row['mid']] = $row;
                break;
            }
        }

        // Если есть чё посылать, то посылаем
        if (is_array($data) && count($data)) {
            require_once("../lib/phpmailer/class.phpmailer.php");            
            $mail_controller = new Controller();
            $mail_controller->initialize(CONTROLLER_ON);
            $mail_controller->setView('DocumentMail');
            $smarty = new Smarty_els();
            $from = getDeansOptions();
            while(list($k,$v) = each($data)) {
                if (is_array($v['heads']) && count($v['heads'])) {
                    // подготовка мессаги
                    $msg = '';
                    if (is_array($v['people']) && count($v['people'])) {
                        $smarty->assign('people',$v['people']);
                        $msg = $smarty->fetch('email_absences.tpl');
                        $smarty->clear_all_assign();
                    }
                    
                    if (!empty($msg)) {
                        $mail_controller->setContent($msg);
                        while(list(,$person) = each($v['heads'])) {
                            if (!empty($person['email'])) {

                                $phpmailer = new PHPMailer();
                                $phpmailer->ContentType = "text/html";
                                $phpmailer->Subject = 'Статистика отсутствий за предыдущую неделю ['.date('d.m.Y',$this->last).'-'.date('d.m.Y').']';
                                $phpmailer->From = $from['email'];
                                $phpmailer->FromName = $from['name'];
                                $phpmailer->Body = $mail_controller->terminate();
                                $phpmailer->AddAddress($person['email'], $person['lname'].' '.$person['fname'].' '.$person['mname']);
                                $result = $phpmailer->Send();
                                
                            }                            
                        }
                    }
                    
                }
            }
        }
        $this->_save_lasttime();
    }
    
    function _save_lasttime() {
        $fname = $GLOBALS['wwf'].'/temp/crontask_email_absences.time';
        if ($fp = fopen($fname,'w+')) {
            fwrite($fp,mktime(0,0,0,date('m'),date('d'),date('Y')));
            fclose($fp);
        }
    }
}

?>