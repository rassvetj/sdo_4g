<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_SubjectsClassifiers extends HM_View_Infoblock_ScreenForm
{

	protected $id = 'subjectsClassifiers';
	protected $class = 'subjectsClassifiers';
	protected $itemType = HM_Classifier_Link_LinkModel::TYPE_SUBJECT;

	public function subjectsClassifiers($title = null, $attribs = null, $options = null)
	{
		$services = Zend_Registry::get('serviceContainer');
		$classifiersTypes = $services->getService('ClassifierType')->getClassifierTypesNames(HM_Classifier_Link_LinkModel::TYPE_SUBJECT);
		
        $categories = $services->getService('Classifier')->getChildren(0); // в списке нам нужен только верхний уровень
        $classifiersCount = $services->getService('Classifier')->getElementCount(HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $categories); // подсчет - рекурсивно, включая нижние уровни
        $classifiersFreshness = $services->getService('Classifier')->getCategoriesFreshness($categories); // подсчет - рекурсивно, включая нижние уровни
		$notClassified = $services->getService('Classifier')->getUnclassifiedElementCount(HM_Classifier_Link_LinkModel::TYPE_SUBJECT);
		
		$classifiers = array();
        if (count($categories)) {
            foreach($categories as $category) {
            	if($count = (int)$classifiersCount[$category->classifier_id]){
            		$type = ($category->type) ? $category->type : (($category->TYPE) ? $category->TYPE : $this->classifierType);
            		if (!isset($classifiers[$type])) {
            			$classifiers[$type] = array();
            		}
	                $classifiers[$type][] = array(
	                    'title' => $category->name,
	                	'count' => $count,
	                    'key' => $category->classifier_id,
                        'type' => $type,
	                	'freshness' => $classifiersFreshness[$category->classifier_id],
	                );
            	}
            }
        }
		
		$this->view->classifiers = $classifiers;
		$this->view->classifiersTypes = $classifiersTypes;
		$this->view->notClassified = $notClassified;
		
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/subjects-classifiers/script.js');
        
		$content = $this->view->render('subjectsClassifiers.tpl');
		if ($title == null) return $content;
		return parent::screenForm($title, $content, $attribs);

	}
}