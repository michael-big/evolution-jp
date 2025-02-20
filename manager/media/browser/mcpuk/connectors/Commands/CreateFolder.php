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
 * File Name: CreateFolder.php
 * Implements the CreateFolder command to make a new folder
 * in the current directory. Output is in XML.
 *
 * File Authors:
 * Grant French (grant@mcpuk.net)
 */

require_once 'Base.php';

class CreateFolder extends Base
{
    public $fckphp_config;
    public $type;
    public $cwd;
    public $actual_cwd;
    public $newfolder;

    function __construct($fckphp_config, $type, $cwd)
    {
        $this->fckphp_config = $fckphp_config;
        $this->type = $type;
        $this->raw_cwd = $cwd;
        $this->actual_cwd = str_replace(
            '//',
            '/',
            sprintf(
                '%s/%s/%s',
                $this->fckphp_config['UserFilesPath'],
                $type, $this->raw_cwd
            )
        );
        $this->real_cwd = str_replace(
            '//',
            '/',
            sprintf('%s/%s', $this->fckphp_config['basedir'], $this->actual_cwd)
        );
        $this->newfolder = str_replace(
            array('..', '/'),
            '',
            getv('NewFolderName')
        );
    }

    public function checkFolderName($folderName)
    {
        //Check the name is not too long
        if (strlen($folderName) > $this->fckphp_config['MaxDirNameLength']) {
            return false;
        }

        //Check that it only contains valid characters
        for ($i = 0, $iMax = strlen($folderName); $i < $iMax; $i++) {
            if (!in_array(substr($folderName, $i, 1), $this->fckphp_config['DirNameAllowedChars'])) {
                return false;
            }
        }
        //If it got this far all is ok
        return true;
    }

    public function run()
    {
        header("content-type: text/xml");
        echo '<?xml version="1.0" encoding="utf-8" ?>' . "\n";
        ?>
        <Connector command="CreateFolder" resourceType="<?= $this->type ?>">
            <CurrentFolder path="<?= $this->raw_cwd ?>" url="<?= $this->actual_cwd ?>"/>
            <?php
            $newdir = str_replace(
                "//",
                "/",
                $this->real_cwd . "/" . $this->newfolder
            );
            //Check the new name
            if ($this->checkFolderName($this->newfolder)) {
                //Check if it already exists
                if (is_dir($newdir)) {
                    $err_no = 101;
                } else {
                    //Check if we can create the directory here
                    if (is_writable($this->real_cwd)) {
                        //Make the directory
                        if (mkdir($newdir, 0777)) {
                            $err_no = 0; //Success
                            @chmod(
                                $newdir,
                                octdec(evo()->config('new_folder_permissions', 0777))
                            ); //added for MODx
                        } else {
                            $err_no = 110;
                        } //Unknown error
                    } else {
                        $err_no = 103;
                    } //No permissions to create
                }
            } else {
                $err_no = 102;
            } //Invalid Folder Name
            ?>
            <Error number="<?= "" . $err_no ?>"/>
        </Connector>
        <?php
    }
}
