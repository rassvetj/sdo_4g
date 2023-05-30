<?php

class HM_Tag_TagService extends HM_Service_Abstract
{
    protected $_isIndexable = false;

    // не хватает что-то вроде HM_Service_Abstract::fetchAllParent...
    public function getTags($itemId, $itemType)
    {
        $return = array();
        $select = $this->getSelect()
            ->from(array('t' => 'tag'), array('id', 'body'))
            ->join(array('tr' => 'tag_ref'), 't.id = tr.tag_id', array())
            ->where('tr.item_id = ?', intval($itemId))
            ->where('tr.item_type = ?', intval($itemType));

        if ($tags = $select->query()->fetchAll()) {
            foreach ($tags as $tag) {
            	$return[$tag['id']] = $tag['body'];
            }
        }
        return $return;
    }

    public function getTagsCache($itemIds, $itemType)
    {
        if (!is_array($itemIds) || !count($itemIds)) return array();

        $return = array();
        $select = $this->getSelect()
            ->from(array('t' => 'tag'), array('id', 'body'))
            ->join(array('tr' => 'tag_ref'), 't.id = tr.tag_id', array('item_id'))
            ->where('tr.item_id IN (?)', new Zend_Db_Expr(implode(',', $itemIds)))
            ->where('tr.item_type = ?', intval($itemType));

        if ($tags = $select->query()->fetchAll()) {
            foreach ($tags as $tag) {
            	$return[$tag['item_id']][$tag['id']] = $tag['body'];
            }
        }
        return $return;
    }


    /**
     * Возвращает обект с информацией о тегах заданного типа и их рейтинге
     * 
     * @param array|int $itemTypes
     * @param int $subjectId
     * @param string $subjectName
     * @return Ambigous <multitype:, stdClass>
     */
    public function getTagsRating( $itemTypes, $subjectId = null, $subjectName = null )
    {
        $min = 1000;
        $max = 0;
        $arResult = array();
        /*
        $selectCourses = $this->getSelect()
            ->from(array('sc' => 'subjects_courses'), array('sc.course_id'))
            ->group(array('sc.course_id'));

        $subjectCourses = $selectCourses->query()->fetchAll();
        */
        $select = $this->getSelect()
            ->from(array('t' => 'tag'), array('id', 'body'))
            ->join(array('tr' => 'tag_ref'), 't.id = tr.tag_id', array(new Zend_Db_Expr('COUNT(tr.tag_id) as rating')))
            ->group(array('t.id','t.body'));

        if ( is_int($itemTypes) ) { // для одного типа
            $select->where('tr.item_type = ?', $itemTypes);

            if (($itemTypes == HM_Tag_Ref_RefModel::TYPE_BLOG) && isset($subjectId)) {
                $select->join(array('b' => 'blog'), 'b.id = tr.item_id', array())
                    ->where($this->quoteInto('b.subject_id = ?',$subjectId));
                if (isset($subjectName)){
                    $select->where($this->quoteInto('b.subject_name = ?',$subjectName));
                }
            }
            
        } elseif ( is_array($itemTypes) ) { //для массивов типов
            $select->where('tr.item_type IN (?)', new Zend_Db_Expr(implode(', ', $itemTypes)));
            
            /**
             * Display only PUBLIc tags
             */
            $subSelect = $this->getSelect()
                    ->from(array('tr2' => 'tag_ref'), array('tr2.item_id'));
            $doSubSelect = false;
            
            if (count($itemTypes)) {
                $subSelect
                    ->joinLeft(array('r' => 'resources'), 'tr2.item_id = r.resource_id', null)
                    ->where($this->quoteInto(
                            array('tr2.item_type IN (?)', ' AND r.status = ?'),
                            array($itemTypes, HM_Resource_ResourceModel::STATUS_PUBLISHED)
                ));
                $doSubSelect = true;
            }
            
            if ($doSubSelect) $select->where('tr.item_id IN (?)', new Zend_Db_Expr($subSelect));
        }

        $select->order('rating DESC');
        $select->limit(50);

        $tags = $select->query()->fetchAll();

        foreach ($tags as $tag) {
            if ($tag["rating"] > $max) {
                $max = $tag["rating"];
            }
            if ($tag['rating'] < $min) {
                $min = $tag["rating"];
            }
        }
        foreach ($tags as $tag) {

            $p = $max - $min;
            if ($p == 0) {
                $p = 1;
            }
            $percent = round(100 * ($tag['rating'] - $min) / $p);

            $objTag = new stdClass();
            $objTag->id = $tag['id'];
            $objTag->body = $tag['body'];
            $objTag->percent = $percent;
            $objTag->num = round($percent * 0.09);
            $arResult[] = $objTag;
        }

        usort($arResult, array('HM_Tag_TagService', '_sortByBody'));
        return $arResult;
    }

    static public function _sortByBody($tag1, $tag2) {
        return $tag1->body < $tag2->body ? -1 : 1;
    }

    /**
     * Возвращает форматированную строку со списком меток элемента заданного типа
     * @param unknown_type $itemIds
     * @param HM_Tag_Type_Abstract $itemType
     * @param Bool $forGrid - форматировать как html для грида
     * @return string
     */
    public function getStrTagsByIds($itemId, $itemType, $forGrid = false)
    {
        $arResult = $this->getTags($itemId, $itemType);

        if ( !count($arResult) ) return '';

        asort($arResult);
        //форматирование в раскрывающийся список
        if ( $forGrid ) {
           $txt = ( count($arResult) > 1 )? '<p class="total">'. $this->pluralTagCount(count($arResult)) . '</p>' : '';
           foreach ($arResult as $item) {
               $txt .= "<p>$item</p>";
           }
           return $txt;
        }
        return implode(', ', $arResult);
    }

    /**
     * Склонятор
     * @param int $count
     * @return string
     */
    public function pluralTagCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('метка plural', '%s метка', $count), $count);
    }

    public function getTagsByIds($itemIds, $itemType = false)
    {
        if ( !is_array($itemIds) ) {
            $itemIds = (array) $itemIds;
        }
        if (!count($itemIds)) return array();
        $return = array();
        $select = $this->getSelect()
            ->from(array('t' => 'tag'), array('id', 'body'))
            ->where('t.id IN (?)', new Zend_Db_Expr(implode(',', $itemIds)));
        if ($itemType) {
            $select->join(array('tr' => 'tag_ref'), 't.id = tr.tag_id', array())
                ->where('tr.item_type = ?', $itemType);
        }
//        exit($select->__toString());
        return $select->query()->fetchAll();
    }

    /**
     * Возварщает выборку элементов по метке и типам
     * @param string $tags
     * @param array | int $itemTypes
     * @return Ambigous <multitype:, string, boolean, mixed>
     */
    public function getIdsByTags($tags,$itemTypes = NULL)
    {
        $select = $this->getSelect()
                       ->from(array('t' => 'tag'), array('body'))
                       ->join(array('tr' => 'tag_ref'), 't.id = tr.tag_id', array('item_id','item_type'))
                       ->where('t.body LIKE ?', $tags);

        if ( is_numeric($itemTypes)) { // для одного типа
            $select->where('tr.item_type = ?', $itemTypes);
        } elseif ( is_array($itemTypes) ) { //для массивов типов
            $select->where('tr.item_type IN (?)', implode(',', $itemTypes));
        }

        return $select->query()->fetchAll();
    }

    public function deleteTags($itemId, $itemType)
    {
        $this->getService('TagRef')->deleteBy(array(
            'item_id = ?' => $itemId,
            'item_type = ?' => $itemType,
        ));
    }

    /**
     * Функция удаляет все теги с которыми не связан ни один элемент
     */
    public function clearTags()
    {
        $select = $this->getSelect()->from(array('t'=>'tag'),'id')
                                    ->joinLeft(array('tr' => 'tag_ref'), 't.id = tr.tag_id', array())
                                    ->where('tag_id IS NULL');

        $arRes = $select->query()->fetchAll();

        foreach ( $arRes as $tag) {
            $this->delete(intval($tag["id"]));
        }
    }

    public function getTagCondition($tag = null, $tagLike = null)
    {
        $where = array();
        if(!is_null($tag)) {
            $where['body LIKE ?'] = $tag;
        }
        if(!is_null($tagLike)) {
            $where['LOWER(body) LIKE ?'] = '%'.mb_strtolower($tagLike).'%';
        }
        return $where;
    }

    public function update($tags, $itemId, $itemType)
    {
        $itemId   = (int) $itemId;
        $itemType = (int) $itemType;

        $this->deleteTags($itemId, $itemType);
        $inserted = array();

        foreach($tags as $tag) {
            if($tag === "") continue; //Пустые теги не обрабатываем и не добавляем в базу
            // TODO исправлять только вместе с формой редактирования меток.
            // Из-за особеностей формы редактирования меток мы можем обрабатывать только строковые поля
            // по этому в следующей строчке в случае если $tag будет числом то мы считаем его индексом а не строкой тега
            $tagObj = is_numeric($tag) ? $this->getOne($this->find($tag)) : $this->getOne($this->fetchAll($this->getTagCondition($tag)));

            if(in_array($tagObj->id, $inserted)) continue; // Защита от одинаковых тэгов приводящих к ошибкам при записи в БД

            if(!isset($tagObj->id)) {
                $tagObj = $this->insert(array(
                    'body' => urldecode($tag),
                ));
            }
            $this->getService('TagRef')->insert(array(
                'tag_id' => $tagObj->id,
                'item_id' => $itemId,
                'item_type' => $itemType,
            ));

            $inserted[] = (int) $tagObj->id;
        }

        // убираем за собой мусор
        $this->clearTags();
    }

    public function convertAllToStrings($arrMixed)
    {
        $return = $tagsIds = $tagsStr = array();
        if (is_array($arrMixed)) {
            foreach ($arrMixed as $tag) {
                if (!self::isNewTag($tag)) {
                    $tagsIds[] = $tag;
                } else {
                    $tagsStr[] = $tag;
                }
            }
            $return = $tagsStr;
            if ($tags = $this->getTagsByIds($tagsIds)) { // any type
                foreach ($tags as $tag) {
                	$return[$tag['id']] = $tag['body'];
                }
            }
        }
        return $return;
    }

    static public function isNewTag($tag)
    {
        return !preg_match("/^([0-9])+$/",$tag); // если не новый - то здес ь целочисленный id
    }

    public function getItemsByTagIds($ids, $itemType = false)
    {
        return array();
    }
}