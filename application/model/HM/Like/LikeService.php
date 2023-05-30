<?php
class HM_Like_LikeService extends HM_Service_Abstract
{
    const TYPE_LIKE    = 'LIKE';
    const TYPE_DISLIKE = 'DISLIKE';
    
    public function like($item_type, $item_id, $like_type) 
    {
        $userId = $this->getService('User')->getCurrentUserId();
        
        if (!$userId) {
            throw new HM_Exception(_('Для голосования необходимо авторизоваться'));
        }
        
        // проверяем, а вдруг пользователь уже голосовал?
        $where = array(
            'item_type = ?',
            ' AND item_id = ?',
            ' AND user_id = ?'
        );
        
        $args = array($item_type, $item_id, $userId);
        
        $oldLikeOfUser = $this->getService('LikeUser')->fetchRow($this->quoteInto($where, $args));
        $likeDelta = 0;
        $dislikeDelta = 0;
        
        // если голосовал, удаляем предыдущее голосование и фиксируем поправку
        if ($oldLikeOfUser) {
            if ($oldLikeOfUser->value == 1) {
                $likeDelta--;
            } else {
                $dislikeDelta--;
            }
            $this->getService('LikeUser')->delete($oldLikeOfUser->like_user_id);
        }
        
        $value = ($like_type === self::TYPE_LIKE) ? 1 : -1;

        // если старый голос совпал с новым, значит пользователь отменил 
        // голосование - добавлять новую запись в бд не надо
        if ($oldLikeOfUser->value != $value) {
            
            if ($value === 1) {
                $likeDelta++;
            } else {
                $dislikeDelta++;
            }
            
            // вставляем новое голосование пользователя
            $this->getService('LikeUser')->insert(array(
                'item_type' => $item_type,
                'item_id'   => $item_id,
                'user_id'   => $userId,
                'value'     => $value,
                'date'      => HM_Date::now()->toString(HM_DATE::SQL)
            ));
        }

        $where = array(
            'item_type = ?',
            ' AND item_id = ?'
        );

        $args = array($item_type, $item_id);
        
        $like = $this->fetchRow($this->quoteInto($where, $args));
        
        // создаем запись для сущности с кэшем суммы всех голосований
        if (!$like) {
            $like = $this->insert(array(
                'item_type' => $item_type,
                'item_id' => $item_id,
                'count_like' => 0,
                'count_dislike' => 0
            ));
        }
        
        // обновляем число лайков/дислайков
        $updateData = array(
            'count_like'    => new Zend_Db_Expr("count_like + ($likeDelta)"),
            'count_dislike' => new Zend_Db_Expr("count_dislike + ($dislikeDelta)")
        );
        
        $this->updateWhere($updateData, $this->quoteInto('like_id = ?', $like->like_id));
        
        return array(
            'count_like'    => ($like->count_like + $likeDelta),
            'count_dislike' => ($like->count_dislike + $dislikeDelta)
        );
        
    }

}