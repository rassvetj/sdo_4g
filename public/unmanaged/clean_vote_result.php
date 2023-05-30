<?php
require_once('1.php');
require_once('lib/classes/Position.class.php');
require_once('lib/classes/Poll.class.php');

if (!$s[login]) exitmsg("Пожалуйста, авторизуйтесь",$GLOBALS['sitepath']);
if (!in_array($_SESSION['s']['perm'],array(2,3,4))) login_error();

$people = $_POST['che'];
$action = $_POST['action'];
$polls  = $_POST['polls'];
$mids   = $_POST['mids'];
$tids   = $sheids = array();

$smarty = new Smarty_els();

$GLOBALS['controller']->captureFromOb(CONTENT);
$GLOBALS['controller']->setHeader(_('Удалить результаты опросов'));

switch($action) {
    case 'clean': // processing...    
        if (is_array($polls) && count($polls) && is_array($mids) && count($mids)) {
            foreach($polls as $mid=>$poll) {
                if (!in_array($mid,$mids)) continue;
                if (is_array($poll) && count($poll)) {
                    foreach($poll as $pid) {
                        CPoll::deleteResults($pid,$mid);
                    }

/*                    $sheids = array_keys($poll);
                    array_walk($sheids,'intval');
                    
                    $sql = "SELECT DISTINCT stid FROM logseance WHERE sheid IN ('".join("','",$sheids)."')";
                    // AND tid IN ('".join("','",$tids)."')";

                    $res = sql($sql);
                    while($row = sqlget($res)) {
                    	$stids[] = $row['stid'];
                    }
                    
                    if (is_array($stids) && count($stids)) {
                        sql("DELETE FROM logseance WHERE stid IN ('".join("','",$stids)."')");
                        sql("DELETE FROM loguser WHERE stid IN ('".join("','",$stids)."')");
                    }
*/
                }
            }
        }
        $GLOBALS['controller']->setView('DocumentBlank');
        $GLOBALS['controller']->setMessage(_('Результаты опросов удалены'),JS_GO_URL,$sitepath.'positions.php');
        $GLOBALS['controller']->terminate();
        //refresh($sitepath.'positions.php');
        exit();
    break;
    
    case '9': // preparing
        if (is_array($people) && count($people) && ($action==9)) {
            $people = CPosition::getMidsBySoids(array_keys($people));

            $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
            
            $sql = "
                SELECT People.MID, People.LastName, People.FirstName, People.Login
                FROM People
                INNER JOIN structure_of_organ ON (structure_of_organ.mid = People.MID)
                WHERE People.MID IN ('".join("','",$people)."')";
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                $names[$row['MID']] = $row['LastName'].' '.$row['FirstName'];
            }
            
            $sql = "SELECT polls.id, polls.name, polls_people.mid
                    FROM polls
                    INNER JOIN polls_people ON (polls_people.poll = polls.id)
                    LEFT JOIN logseance ON (logseance.kod = polls_people.kod)
                    WHERE polls_people.mid IN ('".join("','",$people)."')
                    AND logseance.kod IS NOT NULL";
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                if (!$peopleFilter->is_filtered($row['mid'])) continue;
                $persons[$row['mid']]['name'] = $names[$row['mid']];
                $persons[$row['mid']]['polls'][$row['id']] = $row['name'];
            }
            
/*            $sql = "
                SELECT People.MID, People.LastName, People.FirstName, People.polls
                FROM People
                INNER JOIN structure_of_organ ON (structure_of_organ.mid=People.MID)
                WHERE structure_of_organ.soid IN ('".join("','",$people)."')
                AND People.polls IS NOT NULL
            ";
            $res = sql($sql);
            while($row = sqlget($res)) {
            	if (!empty($row['polls'])) {
                	$person['mid']   = $row['MID'];
                	$person['lname'] = $row['LastName'];
                	$person['fname'] = $row['FirstName'];
                	$polls = unserialize($row['polls']);
                	if (is_array($polls) && count($polls)) {
                	   foreach($polls as $poll) {
                	       list($sheid, $tid) = explode('#',$poll);
                	       $person_poll['sheid'] = $sheid;
                	       $person_poll['tid']   = $tid;
                	       $person['polls'][$sheid] = $person_poll;
                	       $tids[$tid] = $tid;
                	       $sheids[$sheid] = $sheid;
                	   }
                	}
            	}
            	$persons[$row['MID']] = $person;
            }
            if (is_array($tids) && count($tids)) {
                $sql = "SELECT tid, title FROM test WHERE tid IN ('".join("','",$tids)."')";
                $res = sql($sql);
                while($row = sqlget($res)) {
                	$tests[$row['tid']] = $row['title'];
                }
            }
            
            if (is_array($sheids) && count($sheids)) {
                $sql = "SELECT SHEID, Title FROM schedule WHERE SHEID IN ('".join("','",$sheids)."')";
                $res = sql($sql);
                while($row = sqlget($res)) {
                	$schedules[$row['SHEID']] = $row['Title'];
                }
            }
*/
        }
    break;
}

$smarty->assign('persons',$persons);
$smarty->assign('action',$action);
$smarty->assign('sitepath',$sitepath);
$smarty->assign('okbutton',okbutton());

echo $smarty->fetch('clean_vote_result.tpl');

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();
?>