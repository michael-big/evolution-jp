<?php

class logHandler {
	// Single variable for a log entry
	public $entry = array();

    public function __construct() {
    }

    function logError($msg) {
        include_once MODX_CORE_PATH . 'error.class.inc.php';
        $e = new errorHandler;
        $e->setError(9, "Logging error: " . $msg);
        $e->dumpError();
    }

    public function initAndWriteLog($msg='', $internalKey='', $username='', $action='', $itemid='', $itemname='') {
        $this->setEntry($msg, $internalKey, $username, $itemname);
        $this->writeToLog();
    }

    public function writeToLog() {
        global $modx;

        if($this->entry['internalKey'] == '') {
            $this->logError('internalKey not set.');
            return;
        }
        if($this->entry['msg'] == '') {
            include_once(MODX_CORE_PATH . 'actionlist.inc.php');
            $this->entry['msg'] = getAction(evo()->input_any('a',0), $this->entry['itemId']);
            if($this->entry['msg'] == '') {
                $this->logError("couldn't find message to write to log.");
                return;
            }
        }

        $insert_id = db()->insert(
            array(
                'timestamp'   => time(),
                'internalKey' => $modx->db->escape($this->entry['internalKey']),
                'username'    => $modx->db->escape($this->entry['username']),
                'action'      => evo()->input_any('a',0),
                'itemid'      => evo()->input_any('id', 'x'),
                'itemname'    => $modx->db->escape($this->entry['itemName']),
                'message'     => $modx->db->escape($this->entry['msg'])
            )
            , '[+prefix+]manager_log'
        );
        if(!$insert_id) {
            $this->logError("Couldn't save log to table! ".$modx->db->getLastError());
            return;
        }

        if(($insert_id % (int)$modx->conf_var('manager_log_trim', 100)) !== 0) {
            return;
        }

        $modx->rotate_log(
            'manager_log'
            , (int)$modx->conf_var('manager_log_limit', 2000)
            , (int)$modx->conf_var('manager_log_trim', 100)
        );
    }

    private function setEntry($msg='', $internalKey='', $username='', $itemname='') {
        global $modx;

        $this->entry['msg'] = $msg;	// writes testmessage to the object

        // User Credentials
        if ($internalKey != '') {
            $this->entry['internalKey'] = $internalKey;
        } else {
            $this->entry['internalKey'] = $modx->getLoginUserID();
        }
        if ($username != '') {
            $this->entry['username'] = $username;
        } else {
            $this->entry['username'] = $modx->getLoginUserName();
        }

        if ($itemname != '') {
            $this->entry['itemName'] = $itemname;
        } else {
            $this->entry['itemName'] = evo()->session_var('itemname', '-');
        }    // writes the id to the object
    }
}
