<?php
class Union_UnionController extends HM_Controller_Action
{
    public function indexAction()
    {
        $row = array();
        //получаем get-переменную dublicate
        $dubParam = $this->_getParam('dublicate',0);
        //получаем get-переменную MID
        $midParam = $this->_getParam('MID',0);
        //получаем массив из уникального пользователя и дубликата
        $rowUsers = $this->getService('User')->queryDublicate($midParam, $dubParam);
        //передаем в вид уникального пользователя и дубликат
        $this->view->rowUnicUser = $rowUsers[1];
        $this->view->rowDublUser = $rowUsers[0];
        //принимает get переменные при нажатии кнопки 
        $midUnic = $this->_getParam('unical');
        $midDub  = $this->_getParam('dublicate');
        //проверяем существование get переменных
        if (null !== $midUnic and null !== $midDub)
        {             
            //переносим все учебные курсы уникальному пользователю
            $resultUpdate = $this->getService('Student')->updateUnic($midUnic, $midDub); 
           
            if ($resultUpdate !== false)
            {    
                //если курсы удачно перенесены в копилку уникального пользователя удаляем дубликат из из БД(Table)`People`
                $resultDelete = $this->getService('User')->deleteDublicate($midDub, $midUnic);
                if ($resultDelete == true)
                    //редиректим назад
                    $this->_redirector->gotoSimple('index', 'student', 'assign');
                     //указал как у вас абсолютные пути-ничего не придумывал
                //$this->_redirector->gotoSimple('assign', 'student', 'user', array('user_id' => $userId));
            }    
            else 
                $this->_flashMessenger->addMessage(_('Произошла ошибка при объединении дубликатов, повторите попытку'));
                
        }
               
    } 
}    