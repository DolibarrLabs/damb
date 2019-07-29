<?php

/**
 * This file is a part of DAMB
 *
 * An advanced module builder for Dolibarr ERP/CRM
 *
 *
 * @package     DAMB
 * @author      AXeL
 * @copyright   Copyright (c) 2019 - 2020, AXeL-dev
 * @license     GPL
 * @link        https://github.com/AXeL-dev/damb
 *
 */

/**
 * Class to work with zip files (using ZipArchive)
 */
class Zipper
{
    private $zip;
    public function __construct()
    {
        $this->zip = new ZipArchive();
    }
    /**
     * Create archive with name $filename and files $files (RELATIVE PATHS!)
     * @param string $filename
     * @param array|string $files
     * @param array $exclude_files
     * @return bool
     */
    public function create($filename, $files, $exclude_files = array())
    {
        $res = $this->zip->open($filename, ZipArchive::CREATE);
        if ($res !== true) {
            return false;
        }
        if (is_array($files)) {
            foreach ($files as $f) {
                if (!$this->addFileOrDir($f, $exclude_files)) {
                    $this->zip->close();
                    return false;
                }
            }
            $this->zip->close();
            return true;
        } else {
            if ($this->addFileOrDir($files, $exclude_files)) {
                $this->zip->close();
                return true;
            }
            return false;
        }
    }
    /**
     * Extract archive $filename to folder $path (RELATIVE OR ABSOLUTE PATHS)
     * @param string $filename
     * @param string $path
     * @return bool
     */
    public function unzip($filename, $path)
    {
        $res = $this->zip->open($filename);
        if ($res !== true) {
            return false;
        }
        if ($this->zip->extractTo($path)) {
            $this->zip->close();
            return true;
        }
        return false;
    }
    /**
     * Add file/folder to archive
     * @param string $filename
     * @return bool
     */
    private function addFileOrDir($filename, $exclude_files = array())
    {
        if (is_file($filename) && !in_array($filename, $exclude_files)) {
            return $this->zip->addFile($filename);
        } elseif (is_dir($filename) && !in_array($filename, $exclude_files)) {
            return $this->addDir($filename, $exclude_files);
        }
        return false;
    }
    /**
     * Add folder recursively
     * @param string $path
     * @return bool
     */
    private function addDir($path, $exclude_files = array())
    {
        if (!$this->zip->addEmptyDir($path)) {
            return false;
        }
        $objects = scandir($path);
        if (is_array($objects)) {
            foreach ($objects as $file) {
                if ($file != '.' && $file != '..') {
                    if (is_dir($path . '/' . $file) && !in_array($file, $exclude_files)) {
                        if (!$this->addDir($path . '/' . $file, $exclude_files)) {
                            return false;
                        }
                    } elseif (is_file($path . '/' . $file) && !in_array($file, $exclude_files)) {
                        if (!$this->zip->addFile($path . '/' . $file)) {
                            return false;
                        }
                    }
                }
            }
            return true;
        }
        return false;
    }
}
