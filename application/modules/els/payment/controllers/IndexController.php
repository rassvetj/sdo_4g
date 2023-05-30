<?php
class Payment_IndexController extends HM_Controller_Action
{
	private $_serviceUser	 	 = null;
	private $_servicePayment 	 = null;
	
	public function indexAction(){
		
		header('Location: /ticket/');
		die;
		
		$this->view->setHeader(_('Мои оплаты'));
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/rgsu_style.css');
			
		if(!$this->_serviceUser)		{ $this->_serviceUser 			= $this->getService('User');		}
		if(!$this->_servicePaymentCode)	{ $this->_servicePaymentCode 	= $this->getService('PaymentCode');	}
		
		$user 			= $this->_serviceUser->getCurrentUser();
		
		$personal_code	= $this->_servicePaymentCode->getCode($user->mid_external);
		
		$this->view->form = new HM_Form_Ask();
		
		if(empty($personal_code)){			
			$this->view->error = _('Нет данных');
			return;
		}
		
		
		$select = $this->_servicePaymentCode->getSelect();
		$select->from(
            array(
                'sp' => 'student_payments'
            ),
            array(
				'fio' 				=> 'sp.fio',
				'contract' 			=> 'sp.contract',				
				'faculty' 			=> 'sp.faculty',				
				'specialty' 		=> 'sp.specialty',
				'course' 			=> 'sp.course',
				'period' 			=> 'sp.period',
				'sum_schedule' 		=> 'sp.sum_schedule',
				'sum_actual' 		=> 'sp.sum_actual',
				'sum_underpayment' 	=> 'sp.sum_underpayment',
				'sum_overpayment' 	=> 'sp.sum_overpayment',
				#'fine' 				=> 'sp.fine',
				'expired_days' 		=> 'sp.expired_days',
				'date_expected' 	=> 'sp.date_expected',
				'actual_payments' 	=> 'sp.actual_payments',
				#'comment' 			=> 'sp.comment',
            )
        );
		
		$select->where($this->quoteInto('person_code = ?', $personal_code));
		
		$grid_id = 'student_payments';
		
		$grid = $this->getGrid(
            $select,
            array(
                'fio' 				=> array('title' => _('ФИО')),	
                'contract' 			=> array('title' => _('Номер основного договора')),	
                'faculty' 			=> array('title' => _('Факультет')),	
                'specialty' 		=> array('title' => _('Специальность')),	
                'course' 			=> array('title' => _('Курс')),	
                'period' 			=> array('title' => _('Расчетный период')),	
                'sum_schedule' 		=> array('title' => _('Сумма по графику')),	
                'sum_actual' 		=> array('title' => _('Сумма фактическая')),	
                'sum_underpayment' 	=> array('title' => _('Сумма недоплаты')),	
                'sum_overpayment' 	=> array('title' => _('Сумма переплаты')),	
                #'fine' 				=> array('title' => _('Пени')),	
                'expired_days' 		=> array('title' => _('Дней просрочки платежа')),	
                'date_expected' 	=> array('title' => _('Ожидаемая дата оплаты')),	
                'actual_payments' 	=> array(
									   		'title' => _('Список фактический оплат'),
									   		'callback' => array(
									   			'function' => array($this, 'updateActualPayments'),
									   			'params' => array('{{actual_payments}}', '{{date_expected}}')
									   	 	)
									   ),
                #'comment' 			=> array('title' => _('Комментарий специалиста')),	
            ),
            array(
				'fio' 				=> null,
				'contract' 			=> null,
				'faculty' 			=> null,
				'specialty' 		=> null,
				'course' 			=> null,
				'period' 			=> null,
				'sum_schedule' 		=> null,
				'sum_actual' 		=> null,
				'sum_underpayment' 	=> null,
				'sum_overpayment' 	=> null,
				#'fine' 				=> null,
				'expired_days' 		=> null,
				'date_expected' 	=> array('render' => 'DateSmart'),				
            ),
            $grid_id
        );



		$grid->updateColumn('date_expected', array(
            'format' => array(
                'Date',
                array('date_format' => Zend_Locale_Format::getDateTimeFormat())
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{date_expected}}')
            )
        ));
		
		
		
		$this->view->gridAjaxRequest = $this->isAjaxRequest();
		$this->view->grid			 = $grid->deploy();
		
		
		
		
	}
	
	
	public function updateDate($date)
    {
     	if (!strtotime($date)) return '';
		
        return $date;
    }
	
	
	public function updateActualPayments($payments, $date)
	{		
		$date_timestamp = strtotime($date);	
		if(		empty($payments) 	&& 	$date_timestamp < time()	){ return '<span style="color:red;">'._('Не найдено').'</span>'; }		
		return $payments;
	}
	
}