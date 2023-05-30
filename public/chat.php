<?php

ini_set('display_errors', 0);

class Application
{
    protected $action = null;
    protected $response = array();
    protected $channelId = 0;
    protected $token = null;
    protected $lastModified = 0;
    protected $memcache = null;
    protected $timeOut;
    protected $messagesExpired = 0;
    protected $bannedExpired = 0;
    protected $tokensListUrl = '';

    public function __construct($config)
    {
        if($config['debug']) {
            define('DEBUG', true);
        } else {
            define('DEBUG', false);
        }
        
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            set_time_limit($config['chat_server.timeout']);
        }
        
        $this->checkParams();
        if(!extension_loaded('memcache')) {
            throw new Exception('Extension memcache is not loaded');
        }
        $this->memcache = new Memcache();
        $this->memcache->connect($config['chat_server.memcache_host'], $config['chat_server.memcache_port']);
        $this->timeOut = (int)$config['chat_server.timeout'];
        $this->tokensListUrl = $config['chat_server.tokens_list_url'];
        $this->messagesExpired = (int)$config['chat_server.messages_expired'];
        $this->bannedExpired = (int)$config['chat_server.banned_expired'];
    }
    
    protected function log($msg)
    {
        if(DEBUG === true) {
            file_put_contents (dirname(__FILE__).'/chatlog.txt', date('Y-m-d H:i:s').' => '.$msg."\n\n", FILE_APPEND|LOCK_EX);
        }
    }
    
    protected function checkParams()
    {
        $this->action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
        $this->channelId = isset($_REQUEST['channel_id']) ? $_REQUEST['channel_id'] : null;
        $this->token = isset($_REQUEST['token']) ? $_REQUEST['token'] : null;
        $this->lastModified = isset($_REQUEST['last_modified']) ? (int)$_REQUEST['last_modified'] : 0;
        if($this->lastModified === 0) {
            $this->lastModified = time();
        }
        if(!$this->action) {
            Response::sendError('Action is not set');
        }
        if(!$this->channelId) {
            Response::sendError('Channel Id is not set');
        }
    }
    
    protected function isTokenValid()
    {
        if(!$this->token) {
            return false;
        }
        $tokens = $this->memcache->get('Tokens'.$this->channelId);
        if(!$tokens) {
            $tokens = $this->getTokensList();
            $this->memcache->set('Tokens'.$this->channelId, $tokens, $this->tokensExpired);
        }
        $valid = in_array($this->token, $tokens);
        if(!$valid) {
            $banned = $this->memcache->get('Banned'.$this->channelId);
            if(!$banned) {
                $banned = array();
            }
            if(!in_array($this->token, $banned)) {
                $tokens = $this->getTokensList();
                $this->memcache->set('Tokens'.$this->channelId, $tokens, $this->tokensExpired);
                $valid = in_array($this->token, $tokens);
            }
        }
        if(!$valid) {
            $banned []= $this->token;
            $this->memcache->set('Banned'.$this->channelId, $banned, $this->bannedExpired);
        }
        return $valid;
    }
    
    protected function getTokensList()
    {
        $c = curl_init($this->tokensListUrl . $this->channelId);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        $r = curl_exec($c);
        curl_close($c);
        $res = json_decode($r);
        if(count($res) == 0) {
            $this->log('Empty tokens list for channel '.$this->channelId.'. Server res: '.$r);
            Response::sendError('Empty tokens list for channel '.$this->channelId);
        }
        return json_decode($r);
    }
    
    public function dispatch() 
    {
        switch($this->action) {
            case 'log' :
                if(DEBUG === true) {
                    print nl2br(file_get_contents (dirname(__FILE__).'/chatlog.txt'));
                    exit;
                }
            break;
            case 'env' :
                if(DEBUG === true) {
                    print 'timeOut: '.$this->timeOut."\n<br/>".
                        'messagesExpired: '.$this->messagesExpired."\n<br/>".
                        'bannedExpired: '.$this->bannedExpired."\n<br/>".
                        'tokensListUrl: '.$this->tokensListUrl."\n<br/>";
                    exit;
                }
            break;
            case 'pub' :
                //TODO add checking for POST data and client
                $msg = array();
                $msg['id'] = $_POST['id'];
                $msg['receiver'] = $_POST['receiver'];
                $msg['message'] = $_POST['message'];
                $msg['created'] = $_POST['created'];
                $msg['sender_id'] = $_POST['sender_id'];
                $msg['sender_login'] = $_POST['sender_login'];
                $cannelMessages = $this->memcache->get($this->channelId);
                if(!$cannelMessages) {
                    $cannelMessages = array();
                }
                $cannelMessages[$msg['id']] = time();
                
                $this->memcache->set($this->channelId, $cannelMessages, 0, $this->this->messagesExpired);
                $this->memcache->set($this->channelId.'_'.$msg['id'], $msg, 0, $this->this->messagesExpired);
                $this->memcache->set('LastModified'.$this->channelId, time().'');
                if(DEBUG === true) {
                     $this->response []= $this->channelId.'-> '.count($cannelMessages).', last msg -> '.implode(',', $msg).', LastModified -> '.time().' ';
                }
                Response::sendData($this->response);
            break;
            case 'sub' :
                if(!$this->isTokenValid()) {
                    $tokens = $this->memcache->get('Tokens'.$this->channelId);
                    $this->log(
                        'Invalid access token ('.$this->token.'), tokens('.implode(',', $tokens).'), channelId('.$this->channelId.') -> '.count($cannelMessages).
                        ', lastModified -> '.$this->lastModified
                    );
                    Response::sendError('Invalid access token');
                }
                $c = 0;
                while($c <= $this->timeOut) {
                    $cannelLastModified = (int)$this->memcache->get('LastModified'.$this->channelId);
                    if($cannelLastModified > $this->lastModified) {
                        $cannelMessages = $this->memcache->get($this->channelId);
                        asort($cannelMessages);
                        foreach($cannelMessages as $msgId => $time) {
                            if($time > $this->lastModified) {
                                $msg = $this->memcache->get($this->channelId.'_'.$msgId);
                                if($msg) {
                                    $this->response []= $msg;
                                }
                            }
                        }
                        Response::sendData($this->response);
                    }
                    sleep(1);
                    $c++;
                }
                Response::sendData(array(), Response::STATUS_CODE_OK, $this->lastModified);
            break;
            default:
                Response::sendError('Action '.$this->action.' not found');
        }
    }
}


class Response
{
    const STATUS_CODE_OK = 200;
    
    const STATUS_CODE_ERROR = 500;
    
    const STATUS_CODE_UNAUTHORIZED = 401;

    public static function sendError($message, $lastModified = null)
    {
        self::sendData($message, self::STATUS_CODE_ERROR, $lastModified);
    }

    public static function sendUnauthorized($message = '', $lastModified = null)
    {
        self::sendData($message, self::STATUS_CODE_UNAUTHORIZED, $lastModified);
    }

    public static function sendData($result, $status = self::STATUS_CODE_OK, $lastModified = null)
    {
        self::headerNoCache();
        if($lastModified === null) {
            $lastModified = time();
        }
        
        $response = array(
            'status'=>$status, 
            'result' => $result,
            'last_modified' => (int)$lastModified
        );
        if(isset($_GET['callback'])) {
            header('Content-type: text/javascript; charset=UTF-8');
            print $_GET['callback'] . '('.json_encode($response).');';
        } else {
            header('Content-type: application/json; charset=UTF-8');
            print json_encode($response);
        }
        exit;
    }
    
    public static function headerNoCache()
    {
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');               # Date in the past   
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');   # HTTP/1.1
        header('Cache-Control: pre-check=0, post-check=0, max-age=0');  # HTTP/1.1
        header('Pragma: no-cache');
    }
}

// РЈРєР°Р·Р°РЅРёРµ РїСѓС‚Рё Рє РґРёСЂРµРєС‚РѕСЂРёРё РїСЂРёР»РѕР¶РµРЅРёСЏ
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

try {
    $config = parse_ini_file(APPLICATION_PATH.'/settings/config.ini');
	$a = new Application($config);
	$a->dispatch();
} catch(Exception $ex) {
	Response::sendError($ex->getMessage());
}
