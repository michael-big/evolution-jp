<?php

class EXPORT_SITE
{
    public $total;
    public $exportstart;
    public $repl_before;
    public $repl_after;
    public $maxtime;
    private $targetDir;
    private $count;
    private $allow_ids;
    private $ignore_ids;
    private $output = [];
    private $lock_file_path;

    public function __construct()
    {
        global $modx;

        if (!defined('MODX_BASE_PATH')) {
            return;
        }

        $this->exportstart = $this->get_mtime();
        $this->count = 0;
        $this->setUrlMode();
        $this->targetDir = $modx->config['base_path'] . 'temp/export';
        $this->maxtime = 60;
        $modx->config['site_status'] = '1';
        if (!isset($this->total)) {
            $this->getTotal();
        }
        $this->lock_file_path = MODX_BASE_PATH . 'temp/cache/export.lock';
    }

    private function getPastTime()
    {
        return time() - request_time();
    }

    public function setExportDir($dir)
    {
        $dir = str_replace('\\', '/', $dir);
        $dir = rtrim($dir, '/');
        $this->targetDir = $dir;
    }

    public function get_mtime()
    {
        $mtime = microtime();
        $mtime = explode(' ', $mtime);
        $mtime = $mtime[1] + $mtime[0];
        return $mtime;
    }

    private function setUrlMode()
    {
        global $modx;

        if ($modx->config['friendly_urls'] == 0) {
            $modx->config['friendly_urls'] = 1;
            $modx->config['use_alias_path'] = 1;
            $modx->clearCache();
        }
    }

    public function getTotal($allow_ids = '', $ignore_ids = '', $noncache = '0')
    {
        if ($allow_ids !== '') {
            $allow_ids = explode(',', $allow_ids);
            foreach ($allow_ids as $i => $v) {
                $v = db()->escape(trim($v));
                $allow_ids[$i] = "'{$v}'";
            }
            $allow_ids = join(',', $allow_ids);
            $allow_ids = "AND id IN ({$allow_ids})";
        }
        if ($ignore_ids !== '') {
            $ignore_ids = explode(',', $ignore_ids);
            foreach ($ignore_ids as $i => $v) {
                $v = db()->escape(trim($v));
                $ignore_ids[$i] = "'{$v}'";
            }
            $ignore_ids = join(',', $ignore_ids);
            $ignore_ids = "AND NOT id IN ({$ignore_ids})";
        }

        $ids = $allow_ids ? $allow_ids : $ignore_ids;
        $this->allow_ids = $allow_ids;
        $this->ignore_ids = $ignore_ids;

        $where_cacheable = $noncache == 1 ? '' : 'AND cacheable=1';
        $where = "deleted=0 AND ((published=1 AND type='document') OR (isfolder=1)) {$where_cacheable} {$ids}";
        $rs = db()->select('count(id) as total', '[+prefix+]site_content', $where);
        $row = db()->getRow($rs);
        $this->total = $row['total'];
        return $row['total'];
    }

    private function removeDirectoryAll($directory = '')
    {
        if (empty($directory)) {
            $directory = $this->targetDir;
        }
        $directory = rtrim($directory, '/');
        // if the path is not valid or is not a directory ...
        if (empty($directory)) {
            return false;
        }
        if (strpos($directory, MODX_BASE_PATH) !== 0) {
            return false;
        }

        if (!is_dir($directory)) {
            return false;
        }

        if (!is_readable($directory)) {
            return false;
        }

        $files = glob($directory . '/*');
        if (!empty($files)) {
            foreach ($files as $path) {
                if (is_dir($path)) {
                    $this->removeDirectoryAll($path);
                } else {
                    $rs = unlink($path);
                }
            }
        }

        if ($directory !== $this->targetDir) {
            $rs = rmdir($directory);
        }

        return $rs;
    }

    private function makeFile($docid, $filepath)
    {
        global $modx, $_lang;

        $pastTime = $this->getPastTime();
        if (!empty($this->maxtime) && $this->maxtime < $pastTime) {
            $msg = $modx->parseText($_lang['export_site_exit_maxtime'],
                ['count' => $this->count, 'total' => $this->total, 'maxtime' => $this->maxtime]);
            exit($msg);
        }

        $url = $modx->makeUrl($docid, '', '', 'full');
        $src = $this->get_contents($url);

        if ($src === false || is_file(dirname($filepath))) {
            return 'failed_no_open';
        }

        if ($this->repl_before !== $this->repl_after) {
            $src = str_replace($this->repl_before, $this->repl_after, $src);
        }

        $result = file_put_contents($filepath, $src);

        if ($result === false) {
            return 'failed_no_write';
        }

        $file_permission = octdec($modx->config('new_file_permissions'));
        @chmod($filepath, 0666);
        return 'success';
    }

    private function getFileName($docid, $alias = '', $prefix, $suffix)
    {
        global $modx;

        if ($alias === '') {
            $filename = $prefix . $docid . $suffix;
        } else {
            if ($modx->config['suffix_mode'] === '1' && strpos($alias, '.') !== false) {
                $suffix = '';
            }
            $filename = $prefix . $alias . $suffix;
        }
        return $filename;
    }

    public function run()
    {
        global $_lang;
        global $modx;

        $rs = db()->select(
            "id, alias, pagetitle, isfolder, (content = '' AND template = 0) AS wasNull, published",
            '[+prefix+]site_content',
            sprintf(
                "deleted=0 AND ((published=1 AND type='document') OR (isfolder=1)) %s %s",
                ($modx->config('export_includenoncache') == 1
                    ? ''
                    : 'AND cacheable=1'),
                $this->allow_ids ?: $this->ignore_ids
            )
        );

        $ph = [];
        $ph['total'] = $this->total;
        $folder_permission = octdec($modx->config['new_folder_permissions']);

        $msg_failed_no_write = $this->makeMsg('failed_no_write', 'fail');
        $msg_failed_no_open = $this->makeMsg('failed_no_open', 'fail');
        $msg_failed_no_retrieve = $this->makeMsg('failed_no_retrieve', 'fail');
        $msg_success = $this->makeMsg('success');
        $msg_success_skip_doc = $this->makeMsg('success_skip_doc');
        $msg_success_skip_dir = $this->makeMsg('success_skip_dir');

        if (!is_file($this->lock_file_path)) {
            $this->removeDirectoryAll($this->targetDir);
        }
        touch($this->lock_file_path);

        $mask = umask();
        while ($row = db()->getRow($rs)) {
            $_ = $modx->getAliasListing($row['id'], 'path');
            $target_base_path = $_ == ''
                ? sprintf('%s/', $this->targetDir)
                : sprintf('%s/%s/', $this->targetDir, $_)
            ;
            unset($_);
            $_ = rtrim($target_base_path, '/');
            umask(000);
            if (!file_exists($_)) {
                mkdir($_, 0777, true);
            }
            umask($mask);
            unset($_);

            $this->count++;
            $row['count'] = $this->count;

            if (!$row['wasNull']) { // needs writing a document
                $docname = $this->getFileName(
                    $row['id'],
                    $row['alias'],
                    $modx->config('friendly_url_prefix'),
                    $modx->config('friendly_url_suffix')
                );
                $filename = $target_base_path . $docname;
                if (is_dir($filename)) {
                    $filename = rtrim($filename, '/') . '/index.html';
                }
                if (!is_file($filename) || substr($filename, -10) === 'index.html') {
                    if ($row['published'] == 1) {
                        $status = $this->makeFile($row['id'], $filename);
                        switch ($status) {
                            case 'failed_no_write' :
                                $row['status'] = $msg_failed_no_write;
                                break;
                            case 'failed_no_open'  :
                                $row['status'] = $msg_failed_no_open;
                                break;
                            default                :
                                $row['status'] = $msg_success;
                        }
                    } else {
                        $row['status'] = $msg_failed_no_retrieve;
                    }
                } else {
                    $row['status'] = $msg_success_skip_doc;
                }
                $this->output[] = $modx->parseText($_lang['export_site_exporting_document'], $row);
            } else {
                $row['status'] = $msg_success_skip_dir;
                $this->output[] = $modx->parseText($_lang['export_site_exporting_document'], $row);
            }

            if ($row['isfolder'] != 1) {
                continue;
            }

            if ($modx->config('suffix_mode') == 1 && strpos($row['alias'], '.') !== false) {
                continue;
            }

            $end_dir = ($row['alias'] !== '') ? $row['alias'] : $row['id'];
            $folder_path = $target_base_path . $end_dir;
            if (strpos($folder_path, MODX_BASE_PATH) !== 0) {
                return false;
            }

            if (!is_dir($folder_path)) {
                if (is_file($folder_path)) {
                    @unlink($folder_path);
                }
                umask(000);
                mkdir($folder_path, 0777);
                umask($mask);
            }

            if ($modx->config['make_folders'] != 1 || $row['published'] != 1) {
                continue;
            }

            if (is_file($filename)) {
                rename($filename, $folder_path . '/index.html');
            }
        }
        if (is_file($this->lock_file_path)) {
            unlink($this->lock_file_path);
        }
        return join("\n", $this->output);
    }

    private function get_contents($url, $timeout = 10)
    {
        if (!extension_loaded('curl')) {
            return @file_get_contents($url);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        if (ini_get('open_basedir') == '' && ini_get('safe_mode') === 'Off') {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        if (defined('CURLOPT_AUTOREFERER')) {
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        if ($_SERVER['HTTP_USER_AGENT']) {
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        }
        $result = curl_exec($ch);
        if (!$result) {
            $i = 0;
            while ($i < 2) {
                usleep(300000);
                $result = curl_exec($ch);
                $i++;
            }
        }
        curl_close($ch);
        return $result;
    }

    private function makeMsg($cond, $status = 'success')
    {
        global $modx, $_lang;

        $tpl = ' <span class="[+status+]">[+msg1+]</span> [+msg2+]</span>';
        $ph = [];
        $ph['status'] = $status;
        $ph['msg1'] = ($status === 'success')
            ? $_lang['export_site_success']
            : $_lang['export_site_failed'];

        if ($cond === 'failed_no_write') {
            $ph['msg2'] = $_lang["export_site_failed_no_write"] . ' - ' . $this->targetDir . '/';
        } elseif ($cond === 'failed_no_retrieve') {
            $ph['msg2'] = $_lang["export_site_failed_no_retrieve"];
        } elseif ($cond === 'failed_no_open') {
            $ph['msg2'] = $_lang["export_site_failed_no_open"];
        } elseif ($cond === 'success_skip_doc') {
            $ph['msg2'] = $_lang['export_site_success_skip_doc'];
        } elseif ($cond === 'success_skip_dir') {
            $ph['msg2'] = $_lang['export_site_success_skip_dir'];
        } else {
            $ph['msg2'] = '';
        }

        return $modx->parseText($tpl, $ph);
    }
}

$this->export = new EXPORT_SITE;
