<?php
class HM_Controller_Action_Quiz extends HM_Controller_Action
{
    const NAMESPACE_QUIZ = 'quiz';
    
    protected $_persistentModel; 
    
    public function init()
    {
    	$session = new Zend_Session_Namespace(self::NAMESPACE_QUIZ); 
		if (isset($session->persistentModel)) {
			$this->_persistentModel = $session->persistentModel;
            $this->view->model = $this->_persistentModel->getModel();			
            $this->view->results = $this->_persistentModel->getResults();
            $this->view->memoResults = $this->_persistentModel->getMemoResults();
            $this->view->quizId = $this->_getQuizId();			
		}
		
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/content-modules/quiz.js') );
        parent::init();
    }
    
    public function startAction()
    {
	    if (!$this->_isStarted()) {
    	    if (
                ($persistentModel = $this->_getPersistentModel()) &&
                ($persistentModel instanceof HM_Quiz_PersistentModel_Interface)            
            ) {
        	    $this->_persistentModel = $persistentModel;
        		$this->_persistentModel->setupModel();
        		
        		$this->_setCurrentItem();    	    
        	    
        	    $session = new Zend_Session_Namespace(self::NAMESPACE_QUIZ);
        	    $session->persistentModel = $this->_persistentModel;
        	    
        	} else {
        	    // что-то очень неправильно
        	    $this->_redirectToIndex(_('Произошла ошибка при запуске анкеты'), HM_Notification_NotificationModel::TYPE_ERROR);
        	}
    	} elseif (!$this->_isCurrentQuiz()) {
    	    // тоже неправильно, не сработал onUnload
    	    $this->_redirectToQuiz(_('Для продолжения необходимо закончить заполнение предыдущей анкеты.'));
    	}
    	$this->_redirector->gotoSimple('view');
    }
    
    public function viewAction()
    {
        //$this->view->stopUrl = $this->view->url(array_merge($url, array('action' => 'stop')));
        //$this->view->loadUrl = $this->view->url(array_merge($url, array('action' => 'load')));
        //$this->view->saveUrl = $this->view->url(array_merge($url, array('action' => 'save')));
        $this->view->resultsUrl = $this->view->url(array_merge($this->_getBaseUrl(), array('action' => 'results', 'item_id' => null)));
        
        $this->view->info = $this->_setInfo();
        $this->view->progress = $this->_getProgress();
    }
    
    public function loadAction()
    {
        if ($this->_isStarted()) { 
            if ($this->isAjaxRequest()) {
                $this->_helper->getHelper('layout')->disableLayout();
                Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
                //$this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);
            }
            $itemId = $this->_getParam('item_id'); 
            if (!$itemId) {
                $itemId = $this->_persistentModel->getCurrentItem();
            } else {
                $this->_setCurrentItem($itemId);
            }
            if ($this->_hasParam('item_id') && !$this->isAjaxRequest()) {
                $this->_redirector->goToSimple('view');
            } else {
                $this->view->itemId = $itemId;
                $this->view->saveUrl = $this->view->url(array_merge($this->_getBaseUrl(), array('action' => 'save')));
                $this->view->resultsUrl = $this->view->url(array_merge($this->_getBaseUrl(), array('action' => 'results', 'item_id' => null)));
                $this->view->prevNextItems = $this->_getPrevNextItems();
            }
        } else {
            $this->_redirectToIndex(_('Для продолжения заполнения анкеты необходимо авторизоваться'), HM_Notification_NotificationModel::TYPE_ERROR);
        }
    }
    
    public function stopAction()
    {
    	if ($this->_isStarted()) {
    	    $this->_destroyModel();
    	    $this->_redirectToIndex(_('Заполнение анкеты остановлено'));
        } {
            $this->_redirectToError(_('Заполнение анкеты уже остановлено'), HM_Notification_NotificationModel::TYPE_ERROR);
        }
    }

    public function finalizeAction()
    {
    	if ($this->_isStarted()) {
    	    $this->_finalize();
    	    $this->_destroyModel();
    	    $this->_redirectToIndex(_('Заполнение анкеты завершено'));
        } {
            $this->_redirectToError(_('Заполнение анкеты уже завершено'), HM_Notification_NotificationModel::TYPE_ERROR);
        }
    }

    public function saveAction()
    {
        $itemId = $this->_getParam('item_id');
        $results = $this->_getParam('results');
        $memos = $this->_getParam('memos');
        
        $this->_persistentModel->setResults($itemId, $results);
        $result = $this->_saveResults($itemId, $results);
        $memoResult = $this->_saveMemoResults($memos);
        $this->_persistentModel->setMemoResults($memos);
                
	    exit (Zend_Json::encode($result)); // $memoResult пока не учитываем, проверим потом при финализации
    }
    
    public function resultsAction()
    {
        $url = $this->_getBaseUrl();
        $this->view->stopUrl = $this->view->url(array_merge($url, array('action' => 'stop', 'item_id' => null)));
        $this->view->continueUrl = $this->view->url(array_merge($url, array('action' => 'view', 'item_id' => null)));
        $this->view->finalizeUrl = $this->view->url(array_merge($url, array('action' => 'finalize', 'item_id' => null)));
        
        $totalResults = $this->_getTotalResults();
    	$this->view->finalizeable = $this->_isFinalizeable($totalResults);
    	$this->view->totalResults = $totalResults;
    }
    
    public function _destroyModel()
    {
    	$session = new Zend_Session_Namespace(self::NAMESPACE_QUIZ);
		unset($session->persistentModel);
		unset($this->_persistentModel);
		unset($_SESSION[self::NAMESPACE_QUIZ]['persistentModel']);
    }
    
	public function _getProgress()
    {
    	$progress = array();
    	foreach ($this->_persistentModel->getItems() as $itemId) {
    		$progress[] = array(
    			'current' => ($this->_persistentModel->getCurrentItem() == $itemId),
    			'itemId' => $itemId,
    			'name' => $this->_getProgressTitle($itemId),
    			'itemProgress' => $this->_getItemProgress($itemId),
    			'url' => $this->view->url(array_merge($this->_getBaseUrl(), array('action' => 'load', 'item_id' => $itemId))),
    		);
    	}
    	return $progress;
    }

    protected function _setCurrentItem($itemId = false)
    {
    	if ($itemId) { // jump over the progressbar
			$this->_persistentModel->setCurrentItem($itemId);
    	} else {
	    	$ids = $this->_persistentModel->getItems();
	 		if (!$this->_persistentModel->getCurrentItem()) { // first time launch
				$this->_persistentModel->setCurrentItem(array_shift($ids));
	 		} else { // go next
	 			$index = array_search($this->_persistentModel->getCurrentItem(), $ids);
	 			$head = array_slice($ids, 0, $index);
	 			$tail = array_slice($ids, $index);
	 			foreach (array($tail, $head) as $search) {
		 			while (count($search)) { // search for first unanswered
		 				$id = array_shift($search);
		 				if (!isset($this->populate[$id])) {
		 					$this->_persistentModel->setCurrentItem($id);
		 					return;
		 				}
		 			}	 			}
				$this->_persistentModel->setCurrentItem(0);
	 		}
 		}
    }
    
    public function getItem($itemId)
    {
    	if (in_array($itemId, $this->_persistentModel->getItems())) {
    		$item = $this->_items->find($itemId)->current();
    		if (!empty($this->populate[$itemId])) {
    			$item->populate = $this->populate[$itemId];
    		}
    		return $item;
    	}
		return false;
    }
    
    public function _getPrevNextItems()
    {
    	$return = array(
    		'prevId' => null,
    		'prev'   => null,
    		'nextId' => null,
    		'next'   => null,
    	);
    	if ($itemId = $this->_persistentModel->getCurrentItem()) {
    		$ids = $this->_persistentModel->getItems();
    		$key = array_search($itemId, $ids);
    		$url = $this->_getBaseUrl();
			if (isset($ids[$key-1])) {
				$return['prevId'] = $ids[$key-1];
				$return['prev']   = $this->view->url(array_merge($url, array('action' => 'load', 'item_id' => $return['prevId'])));
			}
			if (isset($ids[$key+1])) {
				$return['nextId'] = $ids[$key+1];
				$return['next']   = $this->view->url(array_merge($url, array('action' => 'load', 'item_id' => $return['nextId'])));
			} else {
				$return['next']   = $this->view->url(array_merge($url, array('action' => 'results', 'item_id' => null)));
			}
    	}
    	return $return;
    }
    
    protected function _isStarted()
    {
        return isset($this->_persistentModel);
    } 
}