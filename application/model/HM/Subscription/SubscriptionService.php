<?php
class HM_Subscription_SubscriptionService extends HM_Service_Abstract
{
    public function insertChannel($data)
    {
        return $this->getService('SubscriptionChannel')->insert($data);
    }

    public function deleteChannel($channelId)
    {
        return $this->getService('SubscriptionChannel')->delete($channelId);
    }

    public function insertEntry($data)
    {
        return $this->getService('SubscriptionEntry')->insert($data);
    }

    public function updateEntry($data)
    {
        return $this->getService('SubscriptionEntry')->update($data);
    }

    public function deleteEntry($entryId)
    {
        return $this->getService('SubscriptionEntry')->delete($entryId);
    }

    /**
     * Подписка пользователя на канал, определяемый ИД занятия
     * @param $userId
     * @param $lessonId
     */
    public function subscribeUserToChannelByLessonId($userId, $lessonId)
    {
        $channel = $this->getService('SubscriptionChannel')->getOne($this->getService('SubscriptionChannel')->fetchAll(array('lesson_id=?' => $lessonId)));
        // если такого канала еще нет - создаем его
        if (!$channel){
            $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($lessonId));
            $channel = $this->getService('Activity')->createLessonSubscriptionChannel($lesson);
        }

        if ($channel) {
            $subscription = $this->getOne($this->fetchAll(array('channel_id=?' => $channel->channel_id, 'user_id=?' => $userId)));
            if (!$subscription) {
                $this->insert(array('channel_id' => $channel->channel_id, 'user_id' => $userId));
            }
        }
    }

    /**
     * Отписка пользователя от канала, определяемого ИД занятия
     * @param $userId
     * @param $lessonId
     */
    public function unsubscribeUserFromChannelByLessonId($userId, $lessonId)
    {
        $channel = $this->getService('SubscriptionChannel')->getOne($this->getService('SubscriptionChannel')->fetchAll(array('lesson_id=?' => $lessonId)));
        if ($channel) {
            $subscription = $this->getOne($this->fetchAll(array('channel_id=?' => $channel->channel_id, 'user_id=?' => $userId)));
            if ($subscription) {
                $this->deleteBy(array('channel_id=?' => $channel->channel_id, 'user_id=?' => $userId));
            }
        }
    }
}