<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';

class HM_View_Infoblock_StudentDebtBlock extends HM_View_Infoblock_ScreenForm
{
	protected $id = 'studentdebtblock';
	
	public function studentDebtBlock($title = null, $attribs = null, $options = null)
    {	
		
		$this->_userService = $this->getService('User');		
		$user = $this->_userService->getCurrentUser();		
		$len = strlen($user->mid_external);
		if($len != 6 ){ //--только студент может иметь 6-значный код
			return false; 
		}
		
		$this->view->content = 'Мои задолженности';
		
		$user = $this->getService('User')->getCurrentUser();
		
		$this->_debtService = $this->getService('StudentDebt');	
		
		$select = $this->_debtService->getIndexSelect();
		
		$where = $this->_debtService->quoteInto(			
			array('mid_external=?'),
			array(				
				str_replace(' ', '', $user->mid_external), //--На случай, если встретится код вида "XXX XXX".
			)
		);
		$select->where($where);

		//--Т.к. это тестовая БД, то данные тут старые. Ручной коннект был к рабочей БД, и => там были актуальные записи.
		if ($items = $select->query()->fetchAll()) {	
			
			$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
			
			$content= "
			<table class='student-debt-widget'>
				<thead>
					<tr>
						<td>"._("Название дисциплины")."</td>
						<td>"._("Тип")."</td>										
					</tr>
				</thead>";
			
			foreach ($items as $i) {			
				$content.= "<tr>
					<td>"._($i['discipline'])."</td>								
					<td>"._($i['type'])."</td>
				</tr>";
			}
			$content.= "</table>";
		}
		else {
			$content = _("нет данных");		
		}
		
		$this->view->content = $content;
		
		$content = $this->view->render('studentDebtBlock.tpl');
        
        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
	}
}