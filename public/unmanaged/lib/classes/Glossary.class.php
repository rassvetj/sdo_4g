<?php
class CGlossaryWord extends CDBObject {
    var $table = 'glossary';

    function getWordsByLetter($cid, $letter) {
        $words = array();

        $sql = "SELECT *
                FROM glossary
                WHERE cid = '".(int) $cid."' AND (name LIKE '".CObject::toUpper($letter)."%'
                OR name LIKE '".CObject::toLower($letter)."%')
                ORDER BY name";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $words[$row['id']] = new CGlossaryWord($row);
        }

        return $words;
    }

    function isWordsExist($cid) {
        $sql = "SELECT COUNT(id) as cnt FROM glossary WHERE cid = '".(int) $cid."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            return $row['cnt'];
        }
        return false;
    }

}

class GlossaryController extends Controller {
    var $action;
    var $letter;
    var $word;
    var $cid;
    var $mini;

    function initialize($enabled = CONTROLLER_OFF) {
        $this->persistent_vars = new PersistentVars();
		$this->persistent_vars->initialize();
		
        $this->setView('DocumentGlossary');
        $this->_prepareVariables();

        switch($_REQUEST['action']) {
            case 'delete':
                $action = 'delete';
            break;
            case 'add':
                $action = 'add';
            break;
            default:
                $action = 'index';
        }

        $method = 'action_'.$action;
        if (method_exists($this,$method)) {
            $this->$method();
        } else {
            $this->action_index();
        }
    }

    function _prepareVariables() {
        $this->action = (isset($_GET['action'])   ? $_GET['action'] : 'index');
        $this->letter = (isset($_GET['letter'])   ? $_GET['letter'] : 0);
        $this->word   = (isset($_GET['word'])     ? $_GET['word']   : 0);
        $this->cid    = (isset($_GET['cid'])      ? $_GET['cid']    : 0);
        $this->id     = (isset($_GET['id'])      ? $_GET['id']     : 0);
        $this->mini   = (isset($_REQUEST['mini']) ? true : false);
    }

    function _getCourses() {
        $courses = array();
        $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
        if (is_array($courseFilter->filtered) && count($courseFilter->filtered)) {
            $sql = "SELECT CID, Title FROM Courses WHERE CID IN ('".join("','",array_keys($courseFilter->filtered))."') ORDER BY Title";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $courses[$row['CID']] = htmlspecialchars($row['Title'],ENT_QUOTES);
            }
        }
        return $courses;
    }

    function action_delete() {
        if ($this->id) {
            $glossary = new CGlossaryWord();
            $glossary->delete(array('name'=>'id','value'=>$this->id));
        }
        refresh('glossary.php?cid='.(int) $this->cid.'&letter='.$this->letter);
        exit();
    }

    function action_add() {
        $formParser = new CFormParser($_POST['form']);
        $data = $formParser->get();
        unset($formParser);
        $data['name']        = trim($data['name']);
        $data['description'] = trim($data['description']);
        if (is_array($data) && count($data) && !empty($data['name']) && !empty($data['description'])) {
            unset($data['action']);

            $word = new CGlossaryWord(array(
                    'name'        => $data['name'],
                    'description' => $data['description'],
                    'cid'         => $data['cid']));
            $word->create();

            refresh('glossary.php?cid='.(int) $data['cid'].'&letter='.ord($data['name'][0]));
            exit();
        } else {
            $GLOBALS['controller']->setMessage(_('Введите термин и его описание'),JS_GO_URL,'glossary.php?cid='.$this->cid.'&letter='.$this->letter);
            $GLOBALS['controller']->terminate();
            exit();
        }

        refresh('glossary.php');
        exit();
    }

    function _getMenu() {
        $menu = array();
        if ($this->cid) {
            $sql = "SELECT name FROM glossary WHERE cid='".(int) $this->cid."' ORDER BY name";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $menu[ord(COBject::toUpper($row['name'][0]))] = COBject::toUpper($row['name'][0]);
            }
        }
        return $menu;
    }

    function action_index() {
        $GLOBALS['controller']->addFilter(_('Курс'),'cid',$this->_getCourses(),$this->cid,true);
        $this->view_root->assign('cid',$this->cid);
        $this->view_root->assign('letter',$this->letter);
        $this->view_root->assign('word',$this->word);
        $this->view_root->assign('mini',$this->mini);
        $this->view_root->assign('menu',$this->_getMenu());
        $this->view_root->assign('permission',$_SESSION['s']['perm']);
        $this->view_root->assign('icon_delete',getIcon('delete'));

        if ($this->letter) {
            $words = CGlossaryWord::getWordsByLetter($this->cid, chr($this->letter));
            $rows =  round(count($words) / 2);
            $count = $index = 0; $arrWords = array();
            foreach($words as $word) {
                $arrWords[$index] = $word;
                $index += 2;
                $count++;
                if ($count == $rows) $index = 1;
            }
            ksort($arrWords);
            $this->view_root->assign('words',$arrWords);
            $this->view_root->assign('rows',round(count($words) / 2));
        }

        if ($this->word) {
            $this->view_root->assign('word',CGlossaryWord::get(array('name' => 'id', 'value' => $this->word), 'glossary', 'CGlossaryWord'));
        } else {
            ob_start();
            require_once('lib/FCKeditor/fckeditor.php');
            $oFCKeditor = new FCKeditor("form[description][html]") ;
            $oFCKeditor->BasePath   = "{$GLOBALS['sitepath']}lib/FCKeditor/";
            $oFCKeditor->Value      = '';
            $oFCKeditor->Width      = '100%';
            $oFCKeditor->Height     = 300;
            $oFCKeditor->Config     = array('ToolbarCanCollapse' => 'false', 'ToolbarStartExpanded' => 'false');
            echo $oFCKeditor->Create();
            $this->view_root->assign('fckEditor',ob_get_contents());
            ob_end_clean();
        }

        $this->view_root->setTemplate('glossary');
    }

    function terminate() {
        echo $this->view_root->fetch();
    }
}


class MiniGlossaryController extends GlossaryController {

    function initialize($enabled = CONTROLLER_OFF) {

    	$this->persistent_vars = new PersistentVars();
		$this->persistent_vars->initialize();

		$this->setView('DocumentGlossary');
        $this->_prepareVariables();
		$this->action_index();
    }

    function action_index() {
        $this->view_root->assign('cid',$this->cid);
        $this->view_root->assign('letter',$this->letter);
        $this->view_root->assign('word',$this->word);
        $this->view_root->assign('mini',$this->mini);
        $this->view_root->assign('menu', $menu = $this->_getMenu());

        if (empty($this->letter) && empty($this->word) && empty($menu)) {
	        $this->view_root->assign('empty', true);
        }

        if ($this->letter) {
            //$this->view_root->assign('words',CGlossaryWord::getWordsByLetter($this->cid, chr($this->letter)));
            $words = CGlossaryWord::getWordsByLetter($this->cid, chr($this->letter));
            $rows =  round(count($words) / 2);
            $count = $index = 0; $arrWords = array();
            foreach($words as $word) {
                $arrWords[$index] = $word;
                $index += 2;
                $count++;
                if ($count == $rows) $index = 1;
            }
            ksort($arrWords);
            $this->view_root->assign('words',$arrWords);
        }

        if ($this->word) {
            $this->view_root->assign('word',CGlossaryWord::get(array('name'=>'id','value'=>$this->word), 'glossary', 'CGlossaryWord'));
        }

        $this->view_root->setTemplate('glossary');
        $this->view_root->setTemplate('glossary_mini');
    }

}

class DocumentGlossary extends DocumentAbstract {

    function setTemplate($template) {
        $this->template = $template;
    }

    function assign($name, $value) {
        $this->smarty->assign($name,$value);
    }

    function fetch() {
        $this->smarty->assign('okbutton',okbutton());
        $this->smarty->assign_by_ref("this",$this);
		return $this->smarty->fetch($this->template . ".tpl");
    }
}

?>