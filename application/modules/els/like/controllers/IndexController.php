<?php
class Like_IndexController extends HM_Controller_Action_Activity
{
    protected $service     = 'Like';
    protected $idParamName = 'like_id';
    protected $idFieldName = 'like_id';
    protected $id          = 0;
    
    public function updateValueColumn($value) {
        if ($value == 1) {
            return '<div class="hm-like-liked hm-like"><div class="hm-like-button-like-image"></div>Нравится</div>';
        } else {
            return '<div class="hm-like-disliked hm-like"><div class="hm-like-button-dislike-image"></div>Не нравится</div>';
        }

    }

    public function voteListAction()
    {
        $item_type = (int) $this->_getParam('item_type', false);
        $item_id   = (int) $this->_getParam('item_id',   false);

        switch ($item_type) {
            // БЛОГ
            case HM_Like_LikeModel::ITEM_TYPE_BLOG:
                $blog = $this->getOne($this->getService('Blog')->find($item_id));
                // если не нашли блог, переадрисовываем на главную страницу
                if (!$blog) {
                    $this->redirectToIndex();
                }
                
                $title = _('Подробная информация по голосованию для записи блога').' "'.$blog->title.'"';
                
                break;
            default:
                $this->redirectToIndex();
        }
        
        $this->view->title = $title;
        $this->view->isAjaxRequest = $this->isAjaxRequest();

        $select = $this->getService('Like')->getSelect();
        $select->from(array('lu' => 'like_user'), array(
            'lu.like_user_id',
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(u.LastName, ' ') , u.FirstName), ' '), u.Patronymic)"),
            'lu.value',
            'lu.date',
        ));
        $select->joinLeft(array('u' => 'People'), 'u.MID = lu.user_id', array());
        $select->where('item_type = ?', $item_type);
        $select->where('item_id = ?',   $item_id);
        
        $grid = $this->getGrid(
            $select,
            array(
                'like_user_id' => array('hidden' => true),
                'fio' => array('title' => _('ФИО')),
                'value' => array(
                    'title' => _('Проголосовал'),
                    'callback' => array(
                        'function'=> array($this, 'updateValueColumn'),
                        'params'=> array('{{value}}')
                    )
                ),
                'date' => array('title' => _('Дата голосования'))
            ),
            array(
                'blog_id' => null,
                'title' => null,
                'created'   => array('render' => 'Date'),
                //'author' => null,
                'created_by' => null,
                'tags' => null
            ),
            'grid_blog'
        );
        $grid->addAction(array(
                'module' => 'like',
                'controller' => 'index',
                'action' => 'delete'
            ),
            array('like_user_id'),
            $this->view->icon('delete')
        );
        $grid->deploy();
        
        $this->view->grid = $grid;
        
    }
    
    public function redirectToIndex()
    {
        $this->_redirect('/');
    }
    
    public function likeAction()
    {
        $like_type = $this->_getParam('like_type', false);
        $item_type = (int) $this->_getParam('item_type', false);
        $item_id   = (int) $this->_getParam('item_id',   false);

        try {
            if (!$like_type || !$item_type || !$item_id) {
                throw new HM_Exception(_('Неверно указаны параметры для голосования'));
            }
            
            $stats = $this->getService('Like')->like($item_type, $item_id, $like_type);

            $result = array(
                'result' => $stats,
                'message' => 'OK'
            );
            
        } catch (HM_Exception $e) {
            
            $result = array(
                'result'  => false,
                'message' => $e->getMessage()
            );
            
        } catch (Exception $e) {

            $result = array(
                'result'  => false,
                'message' => $e->getMessage()//_('Голосование не удалось. Попробуйте позже.')
            );
            
        }
        
        die(json_encode($result));
    }

    public function deleteAction(){
        $id = $this->_getParam('like_user_id',0);
        if ($id){
            $res = $this->getOne($this->getService('LikeUser')->find($id));
            if ($res){
                $updateData = array(
                    'count_like'    => new Zend_Db_Expr("count_like - 1"),
                );
                $updRes=$this->getService('Like')->updateWhere($updateData, $this->quoteInto(array('item_id = ?',' AND item_type = ?'), array($res->item_id,$res->item_type)));
                if ($updRes){
                    $this->getService('LikeUser')->delete($id);
                    $this->_flashMessenger->addMessage(_('Результат голосования пользователя успешно удален.'));
                    $this->_redirector->gotoSimple('vote-list','index','like',array(
                            'item_type' => $this->_getParam('item_type', false),
                            'item_id' => $this->_getParam('item_id', false),
                            'subject' => $this->_getParam('subject', 'project'),
                            'subject_id' => $this->_getParam('subject_id', 0)
                        ));
                }
            }
        }
        $this->_flashMessenger->addMessage(_('Во время удаления произошли ошибки.'));
        $this->_redirector->gotoSimple('vote-list','index','like',array(
                'item_type' => $this->_getParam('item_type', false),
                'item_id' => $this->_getParam('item_id', false),
                'subject' => $this->_getParam('subject', 'project'),
                'subject_id' => $this->_getParam('subject_id', 0)
            ));
    }
}

