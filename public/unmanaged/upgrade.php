<?php
require_once('1.php');
require_once('lib/PEAR/Archive/Zip.php');

if (!$s['login'] || ($s['perm'] != 4)) login_error();

$GLOBALS['controller']->captureFromOb(CONTENT);

function dropError($message) {
    $GLOBALS['controller']->setView('DocumentBlank');
    $GLOBALS['controller']->setMessage($message, JS_GO_URL, $GLOBALS['sitepath'].'upgrade.php');
    $GLOBALS['controller']->terminate();
    exit();
}

switch($_REQUEST['action']) {
    case 'upgrade':
        
        if (isset($_FILES['file']) && $_FILES['file']['tmp_name'] && (strtolower($_FILES['file']['type']) == 'application/zip')) {
            
            $filename = $GLOBALS['wwf'].'/temp/upgrade.zip';
            $path     = $GLOBALS['wwf'].'/temp/upgrade';
            if (move_uploaded_file($_FILES['file']['tmp_name'], $filename)) {
                if (is_readable($filename)) {
                    if (!file_exists($path)) {
                        if (!mkdir($path)) dropError(_('Невозможно создать каталог: ').$path);
                        if (!chmod($path, 0770)) dropError(_('Невозможно применить права на каталог: ').$path);
                    }
                    
                    if (is_dir($path) && is_writable($path)) {
                        if ($zip = new Archive_Zip($filename)) {
                            $files = $zip->listContent();
                            if (is_array($files) && count($files)) {
                                foreach($files as $file) {
                                    if ($file['folder']) {
                                        if (!file_exists($path.'/'.$file['filename'])) {
                                            if (!mkdir($path.'/'.$file['filename'])) dropError(_('Невозможно создать каталог: ').$path.'/'.$file['filename']);
                                            if (!chmod($path.'/'.$file['filename'], 0770)) dropError(_('Невозможно применить права на каталог: '.$path.'/'.$file['filename']));                                                                                        
                                        }
                                    } else {
                                        if (file_exists($GLOBALS['wwf'].'/'.$file['filename'])) {                                            
                                            if (!is_readable($GLOBALS['wwf'].'/'.$file['filename'])) dropError(_('Не доступен на чтение: ').$GLOBALS['wwf'].'/'.$file['filename']);
                                            if (!is_writeable($GLOBALS['wwf'].'/'.$file['filename'])) dropError(_('Не доступен на запись: ').$GLOBALS['wwf'].'/'.$file['filename']);                                            
                                            if (file_exists($path.'/'.$file['filename'].'.'.date('Y_m_d'))) unlink($path.'/'.$file['filename'].'.'.date('Y_m_d'));
                                            if (!copy($GLOBALS['wwf'].'/'.$file['filename'], $path.'/'.$file['filename'].'.'.date('Y_m_d'))) dropError(_('Невозможно создать резервную копию файла: ').$GLOBALS['wwf'].'/'.$file['filename']);
                                        }
                                    }
                                }
                            }

                            $files = $zip->extract(array('add_path' => $GLOBALS['wwf']));
                            if (is_array($files) && count($files)) {
                                foreach($files as $file) {
                                    if ($file['status'] == 'ok') $file['status'] = '<font color=green><strong>'.$file['status'].'</strong></font>';
                                    echo join(' | ', $file).'<br>';                                    
                                }
                            }
                        }
                    }
                }
                
                @unlink($filename);
                
            }
        }
        
        break;    
    default:
        echo '<form action="" method="post" enctype="multipart/form-data"';
        echo '<input type="hidden" name="action" value="upgrade">';
        echo '<table class=main cellspacing=0>';
        echo '<tr>';
        echo '<td><input type="file" name="file"> (.zip)</td>';
        echo '<td>'.okbutton().'</td>';
        echo '</tr>';
        echo '</table>';
        echo "</form>";
        break;
}

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

?>