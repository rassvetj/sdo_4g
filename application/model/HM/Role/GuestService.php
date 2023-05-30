<?php
class HM_Role_GuestService extends HM_Service_Abstract
{
	public function getStat($from, $to)
	{
        // мда...
        $from = $from.' 00:00:00';
        $to   = $to.' 23:59:59';

		$where = $this->quoteInto(
            array(
                '((start >= ?', ' AND start <= ?)', ' OR (stop >= ?', ' AND stop <= ?)) AND stop > start',
            ),
            array(
                $from, $to, $from, $to
            )
        );

		$select = $this->getSelect();
		$select
            ->from('session_guest', array(
                'amount' => new Zend_Db_Expr("COUNT(session_guest_id)")
            ))
		    ->where($where);

        $row = $select->query()->fetch();
        
        return (int) $row['amount'];
	}
	
	
    public function setSession($value)
    {
        if($value !== 'none'){
            $value = intval($value);
            if($value == 0){
                $res = $this->insert(array('start' => date('Y-m-d H:i:s'), 'stop' => date('Y-m-d H:i:s')));
                setcookie("usersSystemCounter_guest", $res->session_guest_id, 0, '/');
            }else{
                $res = $this->update(array('session_guest_id' => $value, 'stop' => date('Y-m-d H:i:s')));
            }
        }
    }
    
    public function setNotGuest()
    {
        /** @var Zend_Controller_Request_Http $request */
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $guestId = $request->getCookie('usersSystemCounter_guest', 'none');

        if ($guestId === 'none') {
            return;
        }

        $this->delete($guestId);

        setcookie("usersSystemCounter_guest", 'none', time() + 3600*24*60, '/');
    }
}