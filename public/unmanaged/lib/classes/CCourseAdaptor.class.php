<?php
/**
 * Адаптор Курса
 *
 * @author Yuri Novitsky aka Nyver (c) 2006
 */
class CCourseAdaptor {

      var $attributes;

      function getMeterials($cid) {
          $ret = array();
          if ($cid) {
/*              $sql = "SELECT t1.bid as module, t1.title
                      FROM library t1
                      LEFT JOIN organizations t2 ON (t2.module = t1.bid)
                      WHERE (t1.cid = '".(int) $cid."' OR t2.cid = '".(int) $cid."') AND parent = '0'
                      ORDER BY t1.title";
*/
              $sql = "SELECT t2.oid as module, t2.title
                      FROM organizations t2
                      WHERE t2.cid = '".(int) $cid."'
                      ORDER BY t2.title
              ";
              $res = sql($sql);

              while($row = sqlget($res)) {
                  $ret[$row['module']] = $row['title'];
              }

          }
          return $ret;
      }

      function getTasks($cid) {
          $ret = array();
          if ($cid) {
/*              $sql = "SELECT t1.vol1, t2.title
                      FROM organizations t1
                      INNER JOIN test t2 ON (t2.tid = t1.vol1)
                      WHERE t1.cid = '".(int) $cid."'
                      ORDER BY t2.title";
*/

              $sql = "SELECT t1.tid as vol1, t1.title
                      FROM test t1
                      LEFT JOIN organizations t2 ON (t2.vol1 = t1.tid)
                      WHERE t1.cid = '".(int) $cid."' OR t2.cid = '".(int) $cid."'
                      ORDER BY t1.title
              ";

              $res = sql($sql);

              while($row = sqlget($res)) {
                  $ret[$row['vol1']] = $row['title'];
              }

          }
          return $ret;
      }

      function getRuns($cid) {
          $ret = array();
          if ($cid) {
/*              $sql = "SELECT t1.oid, t1.title
                      FROM organizations t1
                      INNER JOIN training_run t2 ON (t2.run_id = t1.vol2)
                      WHERE t1.cid = '".(int) $cid."'
                      ORDER BY t1.title";
*/
              $sql = "SELECT t1.run_id as oid, t1.name as title
                      FROM training_run t1
                      LEFT JOIN organizations t2 ON (t2.vol2 = t1.run_id)
                      WHERE t1.cid = '".(int) $cid."' OR t2.cid = '".(int) $cid."'
                      ORDER BY t1.name";

              $res = sql($sql);

              while($row = sqlget($res)) {
                  $ret[$row['oid']] = $row['title'];
              }

          }
          return $ret;
      }

      function CCourseAdaptor($aAttributes) {
          $this->attributes = $aAttributes;
      }

      function is_person_exists($cid, $mid) {
          return (CCourseAdaptor::is_person_exists($cid, $mid) || CCourseAdaptor::is_person_student($cid, $mid));
      }

      function is_person_claimant($cid, $mid) {
          if ($mid && $cid) {
              $sql = "SELECT MID FROM claimants WHERE MID='".(int) $mid."' AND CID='".(int) $cid."' AND Teacher='0'";
              $res = sql($sql);
              return sqlrows($res);
          }
      }

      function is_person_student($cid, $mid) {
          if ($mid && $cid) {
              $sql = "SELECT MID FROM Students WHERE MID='".(int) $mid."' AND CID='".(int) $cid."'";
              $res = sql($sql);
              return sqlrows($res);
          }
      }

      function publish_in_department($mid,$cid) {
          if ($mid && $cid) {
              $sql = "SELECT did FROM departments WHERE mid='".(int) $mid."' AND application = '".DEPARTMENT_APPLICATION."'";
              $res = sql($sql);

              while($row = sqlget($res)) {
                  $sql = "INSERT INTO departments_courses (did,cid) VALUES ('{$row['did']}','".(int) $cid."')";
                  sql($sql);
              }
          }
          return true;
      }

      function create() {
          $keys = array(); $values = array();
          if (is_array($this->attributes) && count($this->attributes)) {
              foreach($this->attributes as $key => $value) {
                  $keys[] = $key;
                  $values[] = $GLOBALS['adodb']->Quote($value);
              }
              if (count($keys) && count($values)) {
                  $sql = "INSERT INTO Courses (".join(',',$keys).") VALUES (".join(',',$values).")";
                  if ($res = sql($sql)) {
                      $cid = sqllast();
                      $this->publish_in_department($this->attributes['createby'],$cid);
                      return $cid;
                  }
              }
          }
          return false;
      }

      function update($cid) {
          $values = array();
          if ($cid && is_array($this->attributes) && count($this->attributes)) {
              foreach($this->attributes as $key => $value) {
                  $values[] = $key.' = '.$GLOBALS['adodb']->Quote($value);
              }
              if (count($values)) {
                  $sql = "UPDATE Courses SET ".join(',',$values)." WHERE CID='".(int) $cid."'";
                  if (sql($sql)) return true;
              }
          }
          return false;
      }

      function get($cid) {
          $ret = false;
          if ($cid) {
              $sql = "SELECT * FROM Courses WHERE CID='".(int) $cid."'";
              $res = sql($sql);

              while($row = sqlget($res)) {
                  $ret = $row;
              }
          }
          return $ret;
      }

      /**
       * Зарегить пользователя на курс
       *
       * @param int $cid
       * @param int $mid
       * @return bool
       */
      function assign($cid, $mid, $mail=true) {
          return assign_person2course($mid,$cid,$mail);
      }

    function assignPeople($cid, $people, $mail=true) {
          if (is_array($people) && count($people)) {
              foreach($people as $mid) {
                  CPollCourse::assign($cid, $mid, $mail);
              }
          }
      }

      /**
       * Удалить пользователя с курса
       *
       * @param int $cid
       * @param int $mid
       * @return bool
       */
      function drop($cid, $mid) {
          return delete_person_from_course($mid,$cid);
      }

      /**
       * Возвращает массив mid претендентов
       *
       * @param int $cid
       * @return array
       */
      function get_claimants($cid) {
          if ($cid) {
              $sql = "SELECT MID
                      FROM claimants
                      WHERE CID='".(int) $cid."' AND Teacher=0";
              $res = sql($sql);
              while($row = sqlget($res)) {
                  $ret[] = $row['MID'];
              }
              return $ret;
          }
      }

      /**
       * Переводит всех претендентов в студенты
       *
       * @param int $cid
       * @return bool
       * @access public
       */
      function process_claimants2students($cid) {
          if ($cid) {

              require_once($GLOBALS['wwf'].'/lib/classes/Chain.class.php');
              require_once($GLOBALS['wwf'].'/move2.lib.php');

              $claimants = CCourseAdaptor::get_claimants($cid);
              if (is_array($claimants) && count($claimants)) {
                  foreach ($claimants as $mid) {
                      CChainLog::erase($cid,$mid);
                      tost($mid,$cid);
                  }
              }
          }
      }

      function _change_locked($cid,$locked) {
          if ($cid) {
              $sql = "UPDATE Courses SET locked='".(int) $locked."' WHERE CID='".(int) $cid."'";
              return sql($sql);
          }
      }

      /**
       * Заблокировать курс
       *
       * @param int $cid
       * @return boolean
       */
      function lock($cid) {
          return CCourseAdaptor::_change_locked($cid,1);
      }

      /**
       * Разблокировать курс
       *
       * @param int $cid
       * @return boolean
       */
      function unlock($cid) {
          return CCourseAdaptor::_change_locked($cid,0);
      }

      function is_locked($cid) {
          return getField('Courses','locked','CID',(int) $cid);
      }

      /**
       * Возвращает тип доступа к курсу
       *
       * @param int $cid
       * @return array ('TypeDes'=>, 'chain'=>)
       */
      function get_assign_type($cid) {
          if ($cid) {
              $sql = "SELECT TypeDes, chain FROM Courses WHERE CID='".(int) $cid."'";
              $res = sql($sql);
              if (sqlrows($res) && ($row=sqlget($res))) return $row;
          }
      }
      
      function getDepartmentChildren($departmentId) {
          $ret = array();
          if ($departmentId) {
              $sql = "SELECT did FROM courses_groups WHERE owner_did = '$departmentId'";
              $res = sql($sql);
              
              while($row = sqlget($res)) {
                  $ret[$row['did']] = $row['did'];
                  $departments = CCourseAdaptor::getDepartmentChildren($row['did']);
                  
                  if (count($departments)) {
                      foreach($departments as $did) {
                          $ret[$did] = $did;
                      }
                  }
              }
          }
          return $ret;
      }

      function deleteTests($cid) {
          if (!$cid = (int)$cid) return false;
          sql("DELETE FROM list WHERE kod LIKE '$cid-%'");
          sql("DELETE FROM test WHERE cid = '$cid'");
          sql("DELETE FROM testquestions WHERE cid = '$cid'");
          sql("DELETE FROM testneed WHERE kod LIKE '$cid-%'");          
          return true;        
      }
      
      function deleteMaterials($cid) {
          if (!$cid = (int)$cid) return false;
          $modules = array();
          $sql = "SELECT bid FROM library WHERE cid = '$cid'";
          $res = sql($sql);            
          while($row = sqlget($res)) {
              $modules[$row['bid']] = $row['bid'];
          }             
          if (is_array($modules) && count($modules)) {
              $sql = "DELETE FROM library_index WHERE module IN ('".join("','",$modules)."')";
              sql($sql);
          }            
          sql("DELETE FROM library WHERE cid = '$cid'");          
          //чистим папку library
          $path = $GLOBALS['wwf']."/library/";
          foreach ($modules as $modId) {
              if (is_dir($path.$modId)) {
                  $dirs = scandir($path.$modId);
                  foreach ($dirs as $file) {
                      if (is_file($path.$modId.'/'.$file)) {
                          @unlink($path.$modId.'/'.$file);
                      }
                  }
                  @rmdir($path.$modId);
              }
          }
          
          return true;
      }
      
      function deleteStatistic($cid){
          //print_r($cid);
          //if (!$cid = (int)$cid) return false;
          //sql("DELETE FROM sequence_current WHERE cid = " . (int) $cid); 
          return true;
      }
      
      function clearForImport($cid) {
          if (!$cid = (int)$cid) return false;
          sql("DELETE FROM organizations WHERE cid = '$cid'");
          sql("DELETE FROM glossary WHERE cid = '$cid'");
          sql("DELETE FROM sequence_current WHERE cid = " . (int) $cid);
          return true;
      }

}

class CCoursesAdaptor {
    function get_as_array($status=0, $type=0) {
        $sql = "SELECT CID, Title
                FROM Courses
                WHERE Status>='".(int) $status."' AND `type` = '$type'
                ORDER BY Title";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $ret[$row['CID']] = $row['Title'];
        }
        return $ret;
    }
}

?>