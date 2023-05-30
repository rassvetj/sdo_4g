<?php
class Report_IndexController extends HM_Controller_Action_Report
{
    public function indexAction()
    {
        $reportId = (int) $this->_getParam('report_id', 0);

        $reportItem = $this->getOne($this->getService('Report')->find($reportId));

        if ($reportItem) {

            // Чистим state грида
            $page = sprintf('%s-%s-%s', $this->_request->getModuleName(), $this->_request->getControllerName(), 'view');
            unset(Zend_Registry::get('session_namespace_default')->grid[$page]['grid']);

            
            $reportItem->fields = (strlen($reportItem->fields))? unserialize($reportItem->fields) : array();

            $config = new HM_Report_Config();
            $report = new HM_Report();
            $report->setConfig($config);
            $report->setFields($reportItem->fields);
            $fields = $report->getInputFields($this);

            if (!$fields) {
                $this->_redirector->gotoSimple('view', 'index', 'report', array('report_id' => $reportId));
            }

            $form = new HM_Form();
            $form->setAction($this->view->url(array('action' => 'index', 'controller' => 'index', 'module' => 'report', 'report_id' => $reportId)));

            $values = array();
            if (isset(Zend_Registry::get('session_namespace_default')->report['values'][$reportId])) {
                $values = Zend_Registry::get('session_namespace_default')->report['values'][$reportId];
            }

            $fieldNames = array();
            foreach($fields as $field) {
                if ($field['type'] == 'select') {
                    $form->addElement('select', _($field['name']), array(
                            'label' => _($field['title']),
                            'required' => true,
                            'multiOptions' => $field['values'],
                            'Filters' => array(
                                'StripTags'
                            )
                        )
                    );
                } elseif (in_array($field['type'], array('date', 'datetime', 'datetimestamp'))) {

                    $form->addElement('DatePicker', $field['name'].'_from', array(
                            'label' => $field['title']._(' (C)'),
                            'required' => true,
                            'Filters' => array(
                                'StripTags'
                            ),
                            'JQueryParams' => array(
                                'showOn' => 'button',
                                'buttonImage' => "/images/icons/calendar.png",
                                'buttonImageOnly' => 'true'
                            )
                        )
                    );

                    $form->addElement('DatePicker', $field['name'].'_to', array(
                            'label' => $field['title']._(' (По)'),
                            'required' => true,
                            'Filters' => array(
                                'StripTags'
                            ),
                            'JQueryParams' => array(
                                'showOn' => 'button',
                                'buttonImage' => "/images/icons/calendar.png",
                                'buttonImageOnly' => 'true'
                            )
                        )
                    );

                } else {
                    $form->addElement('text', $field['name'], array(
                            'label' => $field['title'],
                            'required' => true,
                            'Filters' => array(
                                'StripTags'
                            )
                        )
                    );
                }

                if (isset($values[$field['name']])) {
                    $form->getElement($field['name'])->setValue($values[$field['name']]);
                } elseif (in_array($field['type'], array('date', 'datetime', 'datetimestamp'))) {

                    $dateFieldPostfixes = array('from', 'to');

                    foreach($dateFieldPostfixes as $dateFieldPostfix) {
                        if (isset($values[$field['name']."_$dateFieldPostfix"])) {
                            $form->getElement($field['name']."_$dateFieldPostfix")->setValue($values[$field['name']."_$dateFieldPostfix"]);
                        } elseif (isset($field['filter'][$dateFieldPostfix])) {
                            $form->getElement($field['name']."_$dateFieldPostfix")->setValue($field['filter'][$dateFieldPostfix]);
                        }
                    }

                }

                if (in_array($field['type'], array('date', 'datetime', 'datetimestamp'))) {
                    $fieldNames[] = $field['name'].'_from';
                    $fieldNames[] = $field['name'].'_to';
                } else {
                    $fieldNames[] = $field['name'];
                }
            }

            $form->addDisplayGroup(
                $fieldNames,
                'inputGroup',
                array('legend' => _('Входные параметры'))
            );

            $form->addElement('Submit', 'submit', array('Label' => _('Далее')));

            if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
                Zend_Registry::get('session_namespace_default')->report['values'][$reportId] = $form->getValues();
                $this->_redirector->gotoSimple('view', 'index', 'report', array('report_id' => $reportId));
            }

            $this->view->form = $form;
            $this->view->reportId = $reportId;
        }
    }

    public function viewAction()
    {  
        $reportId = (int) $this->_getParam('report_id', 0);

        $reportItem = $this->getOne($this->getService('Report')->find($reportId));

        $this->view->grid = false;

        if ($reportItem) {
            $this->getService('Unmanaged')->setSubHeader(_($reportItem->name));

            /*if (strlen($reportItem->fields)) {
                $reportItem->fields = unserialize($reportItem->fields);
            }*/
            $reportItem->fields = (strlen($reportItem->fields))? unserialize($reportItem->fields) : array();

            $config = new HM_Report_Config();
            $report = new HM_Report();
            $report->setConfig($config);

            if (isset(Zend_Registry::get('session_namespace_default')->report['values'][$reportId])) {
                $report->setValues(Zend_Registry::get('session_namespace_default')->report['values'][$reportId]);
            }

            $report->setFields($reportItem->fields);
			
	/*		for($i=0; $i<count($reportItem->fields); ++$i) {
				$reportItem->fields[$i]['title'] = _($reportItem->fields[$i]['title']);
				echo $reportItem->fields[$i]['title'].'<br/>';
			}
			exit();	 */
			
            
            // сортировка по умолчанию нужна в MSSQL, иначе не работает пагинатор грида
            $sorting = $this->_request->getParam("ordergrid");
            if ($sorting == ""){
                $arr = explode('.', _($reportItem->fields[0]['field']));
                $field = array_pop($arr);
                $this->_request->setParam("ordergrid", $field . '_ASC');
            }            

            $grid = $report->getGrid($this);

            $this->view->grid = $grid->deploy();
        }

    }

}