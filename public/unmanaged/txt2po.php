<?php
require_once('1.php');

$header = 
"msgid \"\"
msgstr \"\"
\"Project-Id-Version: 1\\n\"
\"Report-Msgid-Bugs-To: \\n\"
\"POT-Creation-Date: ".date("Y-m-d H:i")."+0300\\n\"
\"PO-Revision-Date: ".date("Y-m-d H:i")."+0300\\n\"
\"Last-Translator: Yuri Novitsky <yura@hypermethod.ru>\\n\"
\"Language-Team: Lex <lex@hypermethod.ru>\\n\"
\"MIME-Version: 1.0\\n\"
\"Content-Type: text/plain; charset=cp1251\\n\"
\"Content-Transfer-Encoding: 8bit\\n\"

";

if (defined('DEBUG_GETTEXT_FILE') && strlen(DEBUG_GETTEXT_FILE)
    && defined('DEBUG_GETTEXT_PO_FILE') && strlen(DEBUG_GETTEXT_PO_FILE)) {
    if (file_exists(DEBUG_GETTEXT_FILE) && is_readable(DEBUG_GETTEXT_FILE)) {
        if ($lines = file(DEBUG_GETTEXT_FILE)) {
            if ($fp = fopen(DEBUG_GETTEXT_PO_FILE,'w+')) {
                fputs($fp,$header);            
                foreach($lines as $line) {
                    $line = substr($line,0,-2);
                    if (strstr($line,"\\n") !== false) {
                        $line = "\"\n\"".str_replace("\\n","\\n\"\n\"",$line);
                    }
                    fputs($fp,"msgid \"$line\"\n");
                    fputs($fp,"msgstr \"$line\"\n\n");
                }
                fclose($fp);
                
                if ($content = file_get_contents(DEBUG_GETTEXT_PO_FILE)) {
                            header("Content-type: application/unknown");
                            header("Content-Disposition: attachment; filename=messages.po" );
                            header("Expires: 0");
                            header("Cache-Control: no-cache");
                            header("Pragma: no-cache");
                    
                            echo $content;
                            exit();
                }
            }            
        }        
    }
}

?>