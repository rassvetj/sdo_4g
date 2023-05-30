<?php

include_once __DIR__ . '/CodeSniffer.php';

define('ROOT_DIR', str_replace('\\', '/',realpath(__DIR__.'/../../')));
define('PHP_BIN_FILE_NAME', $_SERVER['argv'][1].'php.exe');
define('PHP_INI_DIR', $_SERVER['argv'][2]);
define('VALIDATE_ONLY_SYNTAX', (int) $_SERVER['argv'][3]);

$files = array();
$return = 0;

exec('git diff-index --name-only --cached --diff-filter=ACMR HEAD -- 2>&1', $files);

$phpFiles = array();

foreach ($files as $file) {
    $file = ROOT_DIR.'/'.$file;
    $info = pathinfo($file);

    switch ($info['extension']) {
        case 'php';
        case 'tpl':
            $phpFiles[] = $file;
            break;
    }
}


chdir(__DIR__);

$standard = realpath(__DIR__.'/CodeSniffer/Standards/Zend/ruleset.xml');
$restrictions = array();

$codeSniffer = new PHP_CodeSniffer(0, 4, 'utf-8');

$rules = $codeSniffer->processRuleset($standard);
$codeSniffer->registerSniffs($rules, $restrictions);
$codeSniffer->populateTokenListeners();

$todo = $codeSniffer->getFilesToProcess($phpFiles, false);

function isIgnored($fileName) {
    $ignorePath = array(
        ROOT_DIR.'/data/',
        ROOT_DIR.'/public/unmanaged/',
        ROOT_DIR.'/tools/CodeSniffer/'
    );

    foreach ($ignorePath as $path) {
        if (substr($fileName, 0, strlen($path)) === $path) {
            return true;
        }
    }

    return false;
}

function checkSyntax($fileName) {
    $output = array();
    $return = 0;

    $cmd = PHP_BIN_FILE_NAME.' -c '.PHP_INI_DIR.' -l '.$fileName.' 2>&1';

    exec($cmd, $output, $return);

    if ($return === -1) {
        throw new Exception(implode("\n", $output));
    }
}

foreach ($todo as $file) {

    $info = pathinfo($file);
    $fileExtention = $info['extension'];

    if (isIgnored($file)) {

        $info = pathinfo($file);

        if ($fileExtention === 'php') {
            try {
                checkSyntax($file);
            } catch (Exception $e) {
                echo $e->getMessage();
                exit(1);
            }
        }

        continue;
    }

    // выполняем проверку синтаксиса
    try {
        checkSyntax($file);
    } catch (Exception $e) {
        echo $e->getMessage();
        exit(1);
    }

    if (VALIDATE_ONLY_SYNTAX || ($fileExtention === 'tpl')) {
        continue;
    }

    // выполняем проверку оформления кода

    $phpcsFile = $codeSniffer->processFile($file);
    $errorList = $phpcsFile->getErrors();

    if (count($errorList)) {

        echo 'CodeSniffer: error(s) in file "'.$file.'":'."\n";

        foreach ($errorList as $line => $errorsColumns) {

            foreach ($errorsColumns as $column => $errors) {
                foreach ($errors as $error) {
                    echo 'Line '.$line.': '.$error['message'].'(severity: '.$error['severity'].')'."\n";
                }
            }

        }

        exit(1);
    }

}

exit(0);