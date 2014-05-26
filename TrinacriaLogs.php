<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

class TrinacriaLogs {
    static public $logEntries = array();

    static public function logSQL($logFile, $phpFile, $line, $q, $db) {
        debug('File: '.$phpFile.'; Line: '.$line);
        
        TrinacriaLogs::addEntry($phpFile, $line);
        if(!empty($q)) {
            echo '$q';
            debug($q->errorInfo());
            TrinacriaLogs::add(serialize($q->errorInfo()));
        }
        
        if(!empty($db)) {
            echo '$db';
            debug($db->errorInfo());
            TrinacriaLogs::add(serialize($db->errorInfo()));
        }
        
        TrinacriaLogs::write($logFile);
    }

    static public function log($logFile, $phpFile, $line, $msg) {
        debug('File: '.$phpFile.'; Line: '.$line);
        debug($msg);
        
        TrinacriaLogs::addEntry($phpFile, $line);
        if(is_array($msg)) {
            foreach($msg as $m) {
                TrinacriaLogs::add($m);
            }
        } else {
            TrinacriaLogs::add($msg);
        }
        
        TrinacriaLogs::write($logFile);
    }

    static public function addEntry($file,$line) {
        self::$logEntries[] =
'-----------------------------------------------------------------------------'."\n";
        self::$logEntries[] = date('m-d-Y H:i:s').' - '.$file.' - '.$line;
    }

    static public function add($msg) {
        self::$logEntries[] = $msg;
    }

    static public function write($file = null) {
        if(!empty($file)) {
            $handle = fopen($file, 'a');
            
            if(false === $handle) exit();

            foreach(self::$logEntries as $k => $msg) {
                if(false === fwrite($handle, $msg.PHP_EOL)) {
                    fclose($handle);
                    exit();
                }
            }
            
            self::$logEntries = array();

            fclose($handle);
        } else {
            foreach(self::$logEntries as $k => $msg) {
                error_log($msg);
            }
            
            self::$logEntries = array();
        }
    }
}
?>
