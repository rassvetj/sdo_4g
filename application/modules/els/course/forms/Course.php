<?php
/**
 * Форма для создания и редактирования курсов
 *
 */
class HM_Form_Course extends HM_Form
{
    public $status;

    public function init()
    {

        $modelName = Zend_Registry::get('serviceContainer')->getService('Course')->getMapper()->getModelClass();
        $model = new $modelName(null);

        $front = Zend_Controller_Front::getInstance();
        $req = $front->getRequest();

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->setName('course');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->baseUrl('course/list/' . $req->getParam('status'))
        ));

        $this->addElement('hidden', 'cid', array(

            'Required' => true,
            'Validators' => array(

                'Int'),
            'Filters' => array(

                'Int')));

        $this->addElement('text', 'name', array(

            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(

                array(

                    'StringLength',
                    255,
                    1)),
            'Filters' => array(

                'StripTags')));

        $this->addElement('text', 'name_translation', array(

            'Label' => _('Название').(' (en)'),
            'Required' => false,
            'Validators' => array(

                array(

                    'StringLength',
                    255,
                    1)),
            'Filters' => array(

                'StripTags')));

        $this->addElement('select', 'status', array(
                    'label' => _('Статус ресурса БЗ'),
                    'description' => _('Опубликованные ресурсы доспупны всем авторизованным пользователям через Портал Базы знаний; ограниченное использование ресурсов предполагает возможность включения их в состав учебных курсов преподавателями, но они не доступны через Портал; неопубликованные ресурсы доступны только менеджерам Базы знаний и разработчикам.'),                
                    'required' => true,
                    'filters' => array(array('int')),
                    'multiOptions' => HM_Course_CourseModel::getStatuses()
                )
            );


/*        $this->addElement('select', 'access', array(

            'Label' => _('Доступ'),
            'Required' => false,
            'multiOptions' => array(

                '0' => 'Свободный',
                '-1' => 'Назначаемый'),
            'Validators' => array(

                array(

                    'Int')),
            'Filters' => array(

                'Int')));

        $this->addElement('select', 'coordination', array(

            'Label' => _('Согласование'),
            'Required' => false,
            'multiOptions' => array(

                '0' => 'Без согласования',
                '1' => 'Необходимо согласование'),
            'Validators' => array(

                array(

                    'Int')),
            'Filters' => array(

                'Int')));

        $this->addElement('radio', 'struct', array(

            'Label' => _('Режим работы со структурой'),
            'Required' => false,
            'multiOptions' => array( // TODO выяснить чему равно значение при согласовании


                '1' => 'Произвольный',
                '2' => 'Последовательный',
                '3' => 'Режим «контрольных точек»'),
            'Validators' => array(

                array(

                    'Int')),
            'Filters' => array(

                'Int'),
            'value' => 1));
*/
        $this->addElement('textarea', 'describe', array('Label' => _('Краткое описание'),
            'Required' => false,
            'Validators' => array(),
            'Filters' =>
                array('StripTags')
            )
        );
        $this->addElement('textarea', 'describe_translation', array('Label' => _('Краткое описание').(' (en)'),
            'Required' => false,
            'Validators' => array(),
            'Filters' =>
                array('StripTags')
            )
        );
        $providers = array(_('Нет'));
        $collections = Zend_Registry::get('serviceContainer')->getService('Provider')->fetchAll(null, 'title');
        if (count($collections)) {
            $providers = $collections->getList('id', 'title', _('Нет'));
        }

        $this->addElement('select', 'provider',
            array(
                'Label' => _('Поставщик'),
                'Required' => false,
                'Validators' => array(),
                'Filters' => array('StripTags'),
                'MultiOptions' => $providers
            )
        );

        //   определяем действие и статус.

/*        $act = $req->getActionName();

        $status = $req->getParam('status');

        if ($act == 'edit' && $status == 'developed')
        {

            $this->addElement('select', 'developStatus', array(

                'Label' => _('Статус разработки'),
                'Required' => false,
                'Validators' => array(),
                'Filters' => array('StripTags'),
                'multiOptions' => $model->getSubStatusAvail()));

        }*/

        /*
		 $this->addElement ( 'DatePicker2', 'WorkDate',
							array ('Label' => _ ( 'Период работы курса' ),
							'Required' => false,
							'Validators' => array ('Date'),
							'Filters' => array ( ),
							)

		 );

		 $this->addElement ( 'text', 'DurationDate',
							array ('Label' => _ ( 'Длительность работы с курсом' ),
							'Required' => false,
							'Validators' => array (),
							'Filters' => array ('Int' ),
							'value' => 100
							)

		 );

		  */
        $this->addElement('DatePicker', 'planDate', array(

            'Label' => _('Плановая дата окончания разработки'),
            'Required' => false,
            'Validators' => array(),
            'Filters' => array(),
            'id' => "planDate",
            'value' => date("d.m.Y")));

        $subjects = array();
        //$collections = Zend_Registry::get('serviceContainer')->getService('CourseRubric')->fetchAll(null, 'name');
        //$subjects = $collections->getList('did', 'name');

/*        $this->addElement('UiMultiSelect', 'subjects',
            array(
                'Label' => _('Вид деятельности/тема обучения'),
                'Required' => false,
                'Validators' => array(),
                'Filters' => array('Int'),
                'jQueryParams' => array(
                    'remoteUrl' => $this->getView()->url(array('module' => 'course', 'controller' => 'list', 'action' => 'subjects'))
                ),
                'multiOptions' => $subjects,
                'class' => 'multiselect'
            )
        );*/

        $collections = Zend_Registry::get('serviceContainer')->getService('CourseCompetence')->fetchAll(null, 'name');
        $competents = $collections->getList('coid', 'name');

        // Эти значения получаем из таблицы comp2course
        //$arrayUst=array(2,3);


        //$competents = Zend_Registry::get( 'serviceContainer' )->getService( 'Course' )->getDiff($competents,$arrayUst);


        $this->addElement('UiMultiSelect', 'competents',
            array(
                'Label' => _('Компетенции'),
                'Required' => false,
                'Filters' => array(
                    'Int'
                ),
                'multiOptions' => $competents,
                'class' => 'multiselect'
/*                'list1Name' => 'comp1',
                'list1Title' => _('Все'),
                'list1Options' => $competents,
                'list2Name' => 'competence',
                'list2Title' => _('Компетенции'),
                'list2Options' => array(),
                'Label' => _('Компетенции')*/
            )
        );

        $this->addElement('text', 'hours', array(

            'Label'    => _('Продолжительность обучения (в часах)'),
            'Required' => false,
            'jQueryParams' => array(
                'min' => 0,
                'max' => 100
            ),
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            )));

         /*$this->addElement('checkbox', 'has_tree', array(
             'Label' => _('Не показывать меню учебного модуля (он имеет собственную встроенную навигацию)'),
             'Value' => 0
             //'MultiOptions' => array('has' => 'Курс использует собственную встроенную навигацию'),
         ));*/

        $this->addElement('checkbox', 'new_window', array(
            'Label' => _('Принудительно открывать модуль в новом окне'),
            'Value' => 0
            //'MultiOptions' => array('has' => 'Курс использует собственную встроенную навигацию'),
        ));

        $this->addElement('select', 'emulate', array(
            'Label' => _('Эмулировать режим совместимости с версией Internet Explorer'),
            'Required' => false,
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'MultiOptions' => HM_Course_CourseModel::getEmulateModes()
        ));

        $this->addElement(new HM_Form_Element_FcbkComplete('tags', array(
                'Label' => _('Метки'),
				'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать &laquo;Enter&raquo;'),
                'json_url' => $this->getView()->url(array('module' => 'course', 'controller' => 'index', 'action' => 'tags')),
                'value' => '',
            )
        ));

        /*
        $this->addElement( 'jqlists', 'directions', array (

            'list1Name' => 'direct1',
            'list1Title' => _( 'Все' ),
            'list1Options' => array (
                'Напр1',
                'Напр2' ),
            'list2Name' => 'directions',
            'list2Title' => _( 'Направления' ),
            'list2Options' => array (),
            'filter' => array (
                'option' => true,
                'ajaxurl' => '/sdasdas/' ),
            'Label' => _( 'Направления обучения' ) ) );

        //Для урла аякса для фильтрации
        $areapage = new Zend_Navigation_Page_Mvc( array (
            'action' => 'get-directions',
            'controller' => 'list',
            'module' => 'course' ) );

        $this->addElement( 'jqlists', 'area', array (

            'list1Name' => 'area1',
            'list1Title' => _( 'Все' ),
            'list1Options' => array (
                'Обл1',
                'Обл12' ),
            'list2Name' => 'area',
            'list2Title' => _( 'Области' ),
            'list2Options' => array (),
            'filter' => array (
                'option' => true,
                'ajaxurl' => $areapage->getHref() ),
            'Label' => _( 'Области деятельности' ) ) );
        */

        $this->addDisplayGroup(array(
            'cancelUrl',
            'cid',
            'name',
			'name_translation',
            'status',
            'describe',
			'describe_translation',
            'hours',
            /*'has_tree',*/
            'new_window',
            'emulate',
            'tags'
            ), 'groupCourse1', array(

                'legend' => _('Общие свойства')
            )
        );

        $classifierElements = $this->addClassifierElements(HM_Classifier_Link_LinkModel::TYPE_COURSE, $this->getParam('CID', 0));

        if (!$classifierElements) {
            $classifierElements = array();
        }

        $classifierElements[] = 'competents';

        $this->addDisplayGroup(
            $classifierElements,
            'groupCourse2',
            array(
                'legend' => _('Классификация')
            )
        );


        $this->addDisplayGroup(array(
                'provider',
                'planDate',
            ), 'groupCourse3', array(
                'legend' => _('Разработка и поставка')
            )
        );

        $this->addElement('Submit', 'submit', array(
            'Label' => _('Сохранить')));

        parent::init(); // required!
    }


}