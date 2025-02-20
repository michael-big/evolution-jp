<?php

/*
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2005 Frederico Caldeira Knabben
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 * http://www.opensource.org/licenses/lgpl-license.php
 *
 * For further information visit:
 * http://www.fckeditor.net/
 *
 * File Name: DeleteFile.php
 * Implements the DeleteFile command to delete a file
 * in the current directory. Output is in XML.
 *
 * File Authors:
 * Grant French (grant@mcpuk.net)
 */

require_once 'Base.php';

class DeleteFile extends Base
{
    public $fckphp_config;
    public $type;
    public $cwd;
    public $actual_cwd;

    public function __construct($fckphp_config, $type, $cwd)
    {
        $this->fckphp_config = $fckphp_config;
        $this->type = $type;
        $this->raw_cwd = $cwd;
        $this->actual_cwd = sprintf(
            "%s/%s/%s",
            $this->fckphp_config['UserFilesPath'],
            $type,
            trim($this->raw_cwd, '/') ? '/' . trim($this->raw_cwd, '/') : ''
        );
        $this->real_cwd = sprintf(
            '%s/%s',
            rtrim($this->fckphp_config['basedir'], '/'),
            trim($this->actual_cwd, '/') ? trim($this->actual_cwd, '/') : ''
        );
        $this->filename = str_replace(array('../', '/'), '', unescape(getv('FileName')));
    }

    function run()
    {
        $result2 = true;

        $thumb = $this->real_cwd . '/.thumb/' . $this->filename;
        $result1 = unlink($this->real_cwd . '/' . $this->filename);
        if (is_file($thumb)) {
            $result2 = unlink($thumb);
        }
        header('content-type: text/xml');
        echo '<?xml version="1.0" encoding="utf-8" ?>' . "\n";
        ?>
        <Connector command="DeleteFile" resourceType="<?= $this->type ?>">
            <CurrentFolder path="<?= $this->raw_cwd ?>" url="<?= $this->actual_cwd ?>"/>
            <?php
            if ($result1 && $result2) {
                $err_no = 0;
            } else {
                $err_no = 302;
            }
            ?>
            <Error number="<?= '' . $err_no ?>"/>
        </Connector>
        <?php
    }
}
