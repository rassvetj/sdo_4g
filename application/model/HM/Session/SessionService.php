<?php
class HM_Session_SessionService extends HM_Service_Abstract
{
    public function generateKey()
    {
        $user = $this->getService('User')->getCurrentUser();
        if ($user) {
            $key = md5(md5(sprintf('%s|%s', $user->MID, $user->Login)).time());
            return $key;
        }
        return false;
    }


    public function toLog($data = array(), $userId = null)
    {
        if (!isset($userId)) {
            $userId = $this->getService('User')->getCurrentUserId();
        }

        $sessionData = array(
            'mid'   => $userId,
            'start' => $this->getDateTime(),
            'stop'  => $this->getDateTime(),
            'ip'    => $_SERVER["REMOTE_ADDR"]
        );

        $sessionData = array_merge($sessionData, $data);

        $session = $this->insert($sessionData);
    }


    public function setAuthorizerKey()
    {
        $s = new Zend_Session_Namespace('s');
        if ($s->sessid) {
            $key = $this->generateKey();
           
            if ($key) {
                $this->updateWhere(
                    array(
                        'sesskey' => ''
                    ),
                    $this->quoteInto('mid = ?', $this->getService('User')->getCurrentUserId())
                );

                $this->update(
                    array(
                        'sessid' => $s->sessid,
                        'sesskey' => $key
                    )
                );
                setcookie('hmkey', $key, time() + 3600*24*30*6, '/');
            }
        }
    }
    
    
    public function getUsersStats($from, $to)
    {
        $select = $this->getSelect();
        
        $from = date('Y-m-d', strtotime($from));
        $to = date('Y-m-d', strtotime($to));
        
        $select->from('sessions', array('amount' => new Zend_Db_Expr("COUNT(DISTINCT mid)")) )
               //->where()
               ->where('start >= ?',  $from . ' 00:00')
               ->where('stop <= ?',  $to . ' 23:59:59')
               /*->where('start >= \'' . $from . ' 00:00\'' . ' AND start <= \''. $to . ' 23:59:59\'')
               ->orwhere('stop >= \'' . $from . ' 00:00\'' . ' AND stop <= \''. $to . ' 23:59:59\'')*/
               //->group(array('mid'))
               ;
              // echo $select;
        $query = $select->query();
        $fetch = $query->fetchAll();
        
        //pr($fetch);
        
        $countUsers = intval($fetch[0]['amount']);
        $countGuests = $this->getService('Guest')->getStat($from, $to);
        return array('users' => $countUsers, 'guests' => $countGuests);        
        
    }
}