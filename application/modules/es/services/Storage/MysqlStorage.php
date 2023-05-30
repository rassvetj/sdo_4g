<?php
/**
 * Description of MysqlStorage
 *
 * @author slava
 */
define('STAT_SHOW_LAST_DAYS', 30);

class Es_Service_Storage_MysqlStorage extends Es_Service_Storage_AbstractSqlstorage {
    
    public function _pull(\Es_Entity_AbstractFilter $filter) {
        $group = $filter->getGroup();

        $excludeTypes = $filter->getExcludeEventTypes();

        if (!$filter->getIsGroupResultRequire()) {
            $sql = "SELECT ev.*, evt.name type_str, tg.name egroup_type, tg.event_group_type_id egroup_id,
                    evg.trigger_instance_id, evg.data group_data, evg.type group_type, evu.views
                    FROM es_events ev
                    JOIN es_event_users evu ON (ev.event_id = evu.event_id)
                    JOIN es_event_types evt ON (ev.event_type_id = evt.event_type_id)
                    JOIN es_event_group_types tg ON (evt.event_group_type_id = tg.event_group_type_id)
                    LEFT JOIN es_event_groups evg ON (evg.event_group_id = ev.event_group_id)
                    WHERE evu.user_id = ".($filter->getUserId())."
                    AND evt.name IN ('".implode('\',\'', $filter->getTypes())."')
                    AND ev.create_time < ".$filter->getToTime();

            if (!empty($excludeTypes)) {
                $sql .= " AND evt.name NOT IN ('".implode('\',\'', $excludeTypes)."')";
            }

            if ($group != null) {
                $sql .= " AND ev.event_group_id = ".$group->getId();
            }

            if ($filter->getSingleSubject()) {
                $sql .= " AND evg.trigger_instance_id = ".$filter->getSingleSubject();
            }
            if ($filter->getOnlyNotShowed()) {
                $sql .= " AND evu.views = 0";
            }
            if ($filter->getFromTime() !== null) {
                $sql .= " AND ev.create_time > ".$filter->getFromTime()."
                          ORDER BY ev.create_time DESC";
            } else {
                $sql .= " ORDER BY ev.create_time DESC
                          LIMIT ".$filter->getLimit();
            }
        } else {
            $subSql = "
                 SELECT @cRank := IF(@cGroup = ev.event_group_id, @cRank+1, 1) rank,
                 @cGroup := ev.event_group_id, 
                 ev.*, evt.name type_str, tg.name egroup_type, tg.event_group_type_id egroup_id, evg.trigger_instance_id, evg.data group_data,
                 evg.type group_type, evu.views
                 FROM (SELECT * FROM es_events ORDER BY create_time DESC) ev
                 JOIN es_event_users evu ON (ev.event_id = evu.event_id)
                 JOIN es_event_types evt ON (ev.event_type_id = evt.event_type_id)
                 JOIN es_event_group_types tg ON (evt.event_group_type_id = tg.event_group_type_id)
                 LEFT JOIN es_event_groups evg ON (evg.event_group_id = ev.event_group_id)
                 WHERE evu.user_id = ".($filter->getUserId())."
                 AND evt.name IN ('".implode('\',\'', $filter->getTypes())."')
                 AND ev.create_time < ".$filter->getToTime()."
                 ";

            if (!empty($excludeTypes)) {
                $subSql .= " AND evt.name NOT IN ('".implode('\',\'', $excludeTypes)."')";
            }

            if ($group != null) {
                $subSql .= " AND ev.event_group_id = ".$group->getId();
            }
            if ($filter->getFromTime() !== null) {
                $subSql .= " AND ev.create_time > ".$filter->getFromTime();
            }
            if ($filter->getSingleSubject()) {
                $subSql .= " AND evg.trigger_instance_id = ".$filter->getSingleSubject();
            }
            if ($filter->getOnlyNotShowed()) {
                $subSql .= " AND evu.views = 0";
            }
            $subSql .= " ORDER BY ev.event_group_id, ev.create_time DESC";
            $this->getConnection()->query('SET @cRank = 0');
            $this->getConnection()->query('SET @cGroup = 1');
            $sql = "SELECT rs.event_id, rs.event_type_id, rs.type_str, rs.event_trigger_id, rs.event_group_id, rs.egroup_id, rs.views,
                    rs.description, rs.create_time, rs.trigger_instance_id, rs.group_data, rs.group_type, rs.egroup_type FROM (
                        ".$subSql."
                    ) rs ";
            if ($filter->getFromTime() === null) {
                $sql .= "where rank <= ".$filter->getLimit();
            }
        }



        $stmt = $this->getConnection()->query($sql);
        $rows = $stmt->fetchAll();
        /*@var $eventCollection Es_Entity_AbstractEventCollection */
        $eventCollection = $this->getEsEventDispatcher()->getService('ESFactory')->newEventCollection();
        foreach ($rows as $eventRow) {
            /*@var $event Es_Entity_AbstractEvent */
            $event = $this->getEsEventDispatcher()->getService('ESFactory')->newEventEmptyIstance();
            $event->setId(intval($eventRow['event_id']));
            $event->setEventType(intval($eventRow['event_type_id']));
            $event->setEventTypeStr($eventRow['type_str']);
            $event->setCreateTime(floatval($eventRow['create_time']));
            $event->setParams(Zend_Json::decode($eventRow['description']));
            $event->setParam('views', (bool)(int)$eventRow['views']);
            $event->subjectId(intval($eventRow['event_trigger_id']));

            $eventGroup = $this->getEsEventDispatcher()->getService('EventGroup');
            $eventGroup->setId(intval($eventRow['event_group_id']));
            $eventGroup->setTriggerInstanceId($eventRow['trigger_instance_id']);
            $eventGroup->setData($eventRow['group_data']);
            $eventGroup->setType($eventRow['group_type']);
            $event->setGroup($eventGroup);
            
            $groupType = $this->getEsEventDispatcher()->getService('ESFactory')->newGroupType();
            $groupType->setName($eventRow['egroup_type']);
            $groupType->setId((int)$eventRow['egroup_id']);
            $event->setGroupType($groupType);

            $eventCollection->addEvent($event);
        }
        return $eventCollection;
    }

    public function _push(\Es_Entity_AbstractEvent $event) {
        $relatedUsers = $event->getRelatedUserList();
        if (sizeof($relatedUsers) > 0) {
            try {
                /* @var $connection Zend_Db_Adapter_Pdo_Mysql */
                $connection = $this->getConnection();

                $decorator = new Es_Service_Decorator_JsonDecorator($event);
                $result = $connection->insert('es_events', 
                        array(
                            'event_type_id' => $event->getEventType(),
                            'event_trigger_id' => $event->subjectId(),
                            'event_group_id' => $event->getGroup()->getId(),
                            'description' => $decorator->out(),
                            'create_time' => $event->getCreateTime(),
                        )
                );
                $insertId = $connection->lastInsertId();
                $sql = "INSERT INTO `es_event_users` (`event_id`,`user_id`) VALUES ";
                $values = array();
                foreach ($relatedUsers as $userId) {
                    $values[] = '('.$insertId.','.$userId.')';
                }
                $sql .= implode(",", $values);
                $connection->query($sql);
                $event->setId((int)$insertId);
                return $event;
            } catch (Exception $e) {
                throw new Es_Exception_Runtime("Event has not been inserted! Previous exception message: ".$e->getMessage());
            }
        }
        return false;
    }
    
    public function _remove(\Es_Entity_AbstractFilter $filter) {
        
    }

    public function _unsubscribe(\Es_Entity_AbstractFilter $filter) {
        if (
            $filter->getGroupId() === null && $filter->getEventId() === null &&
            $filter->getEventType() === null && $filter->getTypes() === null
        ) {
            throw new Es_Exception_InvalidArgument('Event filter must contain eventId or eventGroupId or EventType object');
        }
        if ($filter->getGroupId() !== null) {
            $sql = 'SELECT e.event_id ID FROM es_events e
                JOIN es_event_groups eg ON (eg.event_group_id = e.event_group_id)
                WHERE eg.event_group_id';
            if (is_array($filter->getGroupId())) {
                $sql .= ' IN ('.implode(',', $filter->getGroupId()).')';
            } else {
                $sql .= ' = '.$filter->getGroupId();
            }
            $stmt = $this->getConnection()->query($sql);
            $rows = $stmt->fetchAll();
            if (sizeof($rows) > 0) {
                $ids = array();
                foreach ($rows as $row) {
                    $ids[] = (int)$row['ID'];
                }
                $sql = 'UPDATE es_event_users SET views = 1 WHERE user_id='.$filter->getUserId().' AND event_id IN ('.implode(',', $ids).')';
            } else {
                return false;
            }
        } elseif ($filter->getEventId() !== null) {
            $sql = 'UPDATE es_event_users SET views = 1 WHERE user_id='.$filter->getUserId().' AND event_id='.$filter->getEventId();
        } elseif ($filter->getEventType() !== null) {
            $sel = 'SELECT event_id id
                    FROM es_events e
                    JOIN es_event_types et ON (e.event_type_id = et.event_type_id AND et.event_type_id = '.$filter->getEventType()->getId().')';
            $stmt = $this->getConnection()->query($sel);
            $rows = $stmt->fetchAll();
            if (sizeof($rows) > 0) {
                $eventIds = array();
                foreach ($rows as $row) {
                    $eventIds[] = (int)$row['id'];
                }
                $sql = 'UPDATE es_event_users SET views = 1 WHERE user_id='.$filter->getUserId().' AND event_id IN ('.implode(',', $eventIds).')';
            } else {
                return false;
            }
        } else {
            $sel = 'SELECT event_id id
                    FROM es_events e
                    JOIN es_event_types et ON (e.event_type_id = et.event_type_id AND et.name IN (\''.implode('\',\'', $filter->getTypes()).'\'))';
            $stmt = $this->getConnection()->query($sel);
            $rows = $stmt->fetchAll();
            if (sizeof($rows) > 0) {
                $eventIds = array();
                foreach ($rows as $row) {
                    $eventIds[] = (int)$row['id'];
                }
                $sql = 'UPDATE es_event_users SET views = 1 WHERE user_id='.$filter->getUserId().' AND event_id IN ('.implode(',', $eventIds).')';
            } else {
                return false;
            }
        }
        try {
            $stmt = $this->getConnection()->query($sql);
            return ($stmt->rowCount() > 0)?true:false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function _getEventTypesList() {
        $sql = 'SELECT * FROM es_event_types';
        $stmt = $this->getConnection()->query($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $list = $this->getEsEventDispatcher()->getService('ESFactory')->newEventTypeList();
        foreach ($rows as $eventTypeRow) {
            /*@var $eventTypeInstance Es_Entity_AbstractEventType */
            $eventTypeInstance = $this->getEsEventDispatcher()->getService('ESFactory')->newEventType();
            $eventTypeInstance->setId(intval($eventTypeRow['event_type_id']));
            $eventTypeInstance->setName($eventTypeRow['name']);
            $list->addType($eventTypeInstance);
        }
        return $list;
    }

    public function _getGroupByUniqueName($type, $triggerInstanceId) {
        $sql = 'SELECT * FROM es_event_groups WHERE trigger_instance_id='.$triggerInstanceId.' AND type=\''.$type.'\'';
        $stmt = $this->getConnection()->query($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        $eventGroup = $this->getEsEventDispatcher()->getService('EventGroup');
        $eventGroup->setType($type);
        $eventGroup->setTriggerInstanceId($triggerInstanceId);
        if ($row) {
            $eventGroup->setId(intval($row['event_group_id']));
            $eventGroup->setData($row['data']);
        }
        return $eventGroup;
    }

    public function _createGroup(Es_Entity_AbstractGroup $group) {
        $result = $this->getConnection()->insert('es_event_groups', array(
            'trigger_instance_id' => $group->getTriggerInstanceId(),
            'type' => $group->getType(),
            'data' => $group->getData()
        ));
        $group->setId((int)$this->getConnection()->lastInsertId());
        return $group;
    }
    
    public function _pullStats(\Es_Entity_AbstractFilter $filter) {
        $groupTypeIds = $filter->getGroupTypeId();
        $userId = $filter->getUserId();
        $sql = 'SELECT rs.group_id, rs.group_name, sum(rs.views) v, count(*) total FROM (
                    SELECT ev.event_id, evu.user_id, tg.event_group_type_id group_id, tg.name group_name,
                    evu.views views FROM es_events ev
                    JOIN es_event_types evt ON (evt.event_type_id = ev.event_type_id)
                    JOIN es_event_group_types tg ON (tg.event_group_type_id = evt.event_group_type_id)
                    JOIN es_event_users evu ON (evu.event_id = ev.event_id)
                    WHERE 
                        ev.create_time>('.time().'-(24*3600*'.STAT_SHOW_LAST_DAYS.')) and 
                        evu.user_id='.$userId;

        $excludeTypes = $filter->getExcludeEventTypes();

        if (!empty($excludeTypes)) {
            $sql .= " AND evt.name NOT IN ('".implode('\',\'', $excludeTypes)."')";
        }

        if (
                sizeof($groupTypeIds) > 0 && !$filter->getForceStats()
        ) {
            $sql .= ' AND tg.event_group_type_id  IN ('.implode(',', $groupTypeIds).')';
        }
        $sql .= ' GROUP BY event_id
                ) rs GROUP BY group_id';
        if ($filter->getForceStats()) {
            $sql .= ' UNION 
                SELECT tg.event_group_type_id group_id, tg.name group_name, 0, 0
                FROM es_event_group_types tg';
        }
        $stmt = $this->getConnection()->query($sql);
        $statRows = $stmt->fetchAll();
        $groupTypeList = $this->getEsEventDispatcher()->getService('GroupTypeList');
        $ids = array();
        foreach ($statRows as $stRow) {
            if (!in_array((int)$stRow['group_id'], $ids)) {
                $type = $this->getEsEventDispatcher()->getService('ESFactory')->newGroupType();
                $type->setId((int)$stRow['group_id']);
                $type->setName($stRow['group_name']);
                $typeStat = $this->getEsEventDispatcher()->getService('ESFactory')->newGroupTypeStat();
                $typeStat->setShowed((int)$stRow['v']);
                $typeStat->setNotShowed((int)$stRow['total'] - (int)$stRow['v']);
                $type->setStat($typeStat);
                $groupTypeList->add($type);
                $ids[] = (int)$stRow['group_id'];
            }
        }
        return $groupTypeList;
    }
    
    public function _pullNotifies(\Es_Entity_AbstractFilter $filter) {
        /*@var $factory Es_Service_Factory */
        $factory = $this->getEsEventDispatcher()->getService('ESFactory');
        /*@var $notifiesList Es_Entity_NotifiesList */
        $notifiesList = $this->getEsEventDispatcher()->getService('ESFactory')->newNotifiesList();
        
        $notifiesSubselect = "
            SELECT ent.*, et.event_type_id, et.name type_name
            FROM es_notify_types ent 
            RIGHT JOIN es_event_types et ON (1)
        ";
        
        if (is_numeric($filter->getUserId())) {
            $userNotifiesSubselect = "
                SELECT * FROM es_user_notifies WHERE user_id=".$filter->getUserId()."
            ";
        } elseif (is_array($filter->getUserId())) {
            $userNotifiesSubselect = "
                SELECT * FROM es_user_notifies WHERE user_id IN (".implode(',', $filter->getUserId()).")
            ";
        }
        
        $sql = "SELECT t.*, tr.user_id, tr.is_active
            FROM (".$notifiesSubselect.") t 
            LEFT JOIN (".$userNotifiesSubselect.") tr ON (tr.event_type_id = t.event_type_id AND tr.notify_type_id = t.notify_type_id)";
        $wheres = array();
        if ($filter->getEventType() !== null) {
            $wheres[] = "t.event_type_id = ".$filter->getEventType()->getId();
        }
        if ($filter->getNotifyType() !== null) {
            $wheres[] = "t.notify_type_id = ".$filter->getNotifyType()->getId();
        }
        if (sizeof($wheres) > 0) {
            $where = " WHERE ".implode(' AND ', $wheres);
            $sql .= $where;
        }
        $sql .= " ORDER BY t.event_type_id, t.notify_type_id";
        $stmt = $this->getConnection()->query($sql);
        $rows = $stmt->fetchAll();
        foreach ($rows as $notifyRow) {
            
            /*@var $eventType Es_Entity_AbstractEventType */
            $eventType = $factory->newEventType();
            $eventType->setId((int)$notifyRow['event_type_id']);
            $eventType->setName($notifyRow['type_name']);
            
            /*@var $notifyType Es_Entity_AbstractNotifyType */
            $notifyType = $factory->newNotifyType();
            $notifyType->setId((int)$notifyRow['notify_type_id']);
            $notifyType->setName($notifyRow['name']);
            
            /*@var $notify Es_Entity_AbstractNotify */
            $notify = $factory->newNotify();
            $notify->setIsActive((bool)intval($notifyRow['is_active']));
            $notify->setUserId((int)$notifyRow['user_id']);
            $notify->setEventType($eventType);
            $notify->setNotifyType($notifyType);
            
            $notifiesList->add($notify);
        }
        return $notifiesList;
    }
    
    public function _updateNotify(\Es_Entity_AbstractNotify $notify) {
        $userId = $notify->getUserId();
        $eventTypeId = (int)$notify->getEventType()->getId();
        $notifyTypeId = (int)$notify->getNotifyType()->getId();
        $isActive = (int)$notify->isActive();
        $selectSql = '
            SELECT * FROM es_user_notifies WHERE
            user_id='.$userId.' AND
            notify_type_id='.$notifyTypeId.' AND
            event_type_id='.$eventTypeId.'
        ';
        $stmt = $this->getConnection()->query($selectSql);
        $row = $stmt->fetch();
        if ($row) {
            $sql = 'UPDATE es_user_notifies SET is_active='.$isActive.
                   ' WHERE user_id='.$userId.' AND 
                     notify_type_id='.$notifyTypeId.' AND
                     event_type_id='.$eventTypeId;
        } else {
            $sql = 'INSERT INTO es_user_notifies (`user_id`,`notify_type_id`,`event_type_id`,`is_active`) VALUES 
                    ('.$userId.','.$notifyTypeId.', '.$eventTypeId.', '.$isActive.')';
        }
        try {
            $stmt = $this->getConnection()->query($sql);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
}

?>
