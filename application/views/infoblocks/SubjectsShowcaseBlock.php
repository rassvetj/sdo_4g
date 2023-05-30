<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_SubjectsShowcaseBlock extends HM_View_Infoblock_ScreenForm
{

    protected $id = 'subjectsshowcaseblock';
    
    public function subjectsShowcaseBlock($title = null, $attribs = null, $options = null)
    {

        $classifierId = $options['classifier_id'];
        $classifierType = $options['category_id'];

        //$services = Zend_Registry::get('serviceContainer');

        //$classifier = $this->get

        if($classifierId == 0){

            if($classifierType == 0){
                $classifierTypes = $this->getService('ClassifierType')->fetchAll(
                    $this->getService('ClassifierType')->quoteInto('link_types LIKE ?', '%'.HM_Classifier_Link_LinkModel::TYPE_SUBJECT . '%')
                );


                $this->view->classifiers = $classifierTypes;
                $this->view->subjects = array();
                //pr($classifierTypes);

            /*    $classifierTypeIds = array_keys($classifierTypes->getList('type_id', 'name'));
                if(count($classifierTypeIds) > 0){

                    $classifiers = $this->getService('Classifier')->fetchAll(array('level = ?' => 0, 'type IN (?)' => $classifierTypeIds));

                    //pr($classifierTypeIds);
                    $this->view->classifiers = $classifiers;

                    //pr($classifiers);
                }
            */
                // Тут еще не классифицированные

            }else{
                $classifiers = $this->getService('Classifier')->fetchAll(array('level = ?' => 0, 'type = ?' => $classifierType));

                $this->view->classifiers = $classifiers;
                $this->view->subjects = array();

            }
        }else{

            $classifier = $this->getService('Classifier')->getOne($this->getService('Classifier')->find($classifierId));

            $classifiers = $this->getService('Classifier')->fetchAll(array('lft > ?' => $classifier->lft, 'rgt < ?' => $classifier->rgt, 'level = ?' => ($classifier->level + 1), 'type = ?' => $classifier->type));

            $this->view->classifiers = $classifiers;

            //pr((array('lft > ?' => $classifier->lft, 'rgt < ?' => $classifier->rgt, 'level = ?' => ($classifier->level + 1), 'type = ?' => $classifier->type)));


            $subjectIds = $this->getService('ClassifierLink')->fetchAll(array('classifier_id = ?' => $classifierId));

            $subjectIds = $subjectIds->getList('item_id', 'classifier_id');

            if(count($subjectIds) > 0){
                $subjectIds = array_keys($subjectIds);
            }else{
                $subjectIds = array(0);
            }

            $subjects = $this->getService('Subject')->fetchAll(array('subid IN (?)' => $subjectIds));

            $this->view->subjects = $subjects;
        }


        //pr($this->vew->classifiers);
        $this->createBreadcrumbs($classifierType, $classifierId);


        $content = $this->view->render('subjectsShowcaseBlock.tpl');


        if ($title == null) return $content;






        return parent::screenForm($title, $content, $attribs);

    }


    public function createBreadcrumbs($categoryId, $classifierId)
    {

        $home = _('Домой');

        $urlArray = array('module' => 'infoblock', 'controller' => 'course-showcase', 'action' => 'index');


        $res = array();
        if($categoryId > 0 || $classifierId > 0){
            $res[] = array('title' => $home, 'url' => array_merge($urlArray, array('category_id' => null, 'classifier_id' => null)));
        }


        if($categoryId > 0 || $classifierId == 0){
            $classifierType = $this->getService('ClassifierType')->getOne($this->getService('ClassifierType')->find($categoryId));

            if($classifierType){
                $lastElement = $classifierType->name;
            }

        }



        if($classifierId > 0){
            $classifier = $this->getService('Classifier')->getOne($this->getService('Classifier')->find($classifierId));

            if($classifier){
                $classifierType = $this->getService('ClassifierType')->getOne($this->getService('ClassifierType')->find($classifier->type));

                if($classifierType){
                    $res[] = array('title' => $classifierType->name, 'url' => array_merge($urlArray, array('category_id' => $classifierType->type_id, 'classifier_id' => null)));
                }


                $classsifiers = $this->getService('Classifier')->fetchAll(
                    array(
                        'lft < ?' => $classifier->lft,
                        'rgt > ?' => $classifier->rgt,
                        'type = ?' => $classifier->type
                    )
                );

                foreach($classsifiers as $classif){
                    $res[] = array('title' => $classif->name, 'url' => array_merge($urlArray, array('classifier_id' => $classif->classifier_id, 'category_id' => null)));
                }


                $lastElement = $classifier->name;

            }


        }

        $linkArray = array();
        foreach($res as $link){
            $linkArray[] = '<a href="' . $this->view->url($link['url']) . '">' . $link['title'] . '</a>';
        }

        if($lastElement != null){
            $linkArray[] = '<span>' . $lastElement . '</span>';
        }


        $return = implode(' <span class="sep">»</span> ', $linkArray);

        $this->view->breadcrumbs = $return;


    }



}