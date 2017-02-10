<?php
/**
 * Copyright (C) 2015 Daniel Deady
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

namespace MageI18n;

/**
 * This class uses "git" so has it's limitations.
 * File names/paths must be tree-ish and relative to the root of the git project.
 * Working directory must be somewhere within the project directories.
 */
class GitFileInfo extends SourceFileInfo
{

    public function __construct($filename)
    {
        parent::__construct($filename);
        $this->setFileClass(GitFileObject::class);
        $this->setInfoClass(__CLASS__);
    }

    public function getSize()
    {
        return (int) exec('git cat-file -s '.escapeshellarg($this->getPathname()));
    }

    public function getType()
    {
        $type = exec('git cat-file -t '.escapeshellarg($this->getPathname()));
        switch ($type) {
            case 'blob':
                return 'file';
            case 'tree':
                return 'dir';
            case 'commit':
            case 'tag':
                return 'link';
            default:
                throw new \RuntimeException('Cannot determine type of '.$this->getPathname());
        }
    }

    public function isDir()
    {
        return $this->getType() === 'dir';
    }

    public function isFile()
    {
        return $this->getType() === 'file';
    }

    public function isLink()
    {
        return $this->getType() === 'link';
    }

    public function isReadable()
    {
        // true if in repo
        return (bool) $this->getType();
    }

    public function isWritable()
    {
        // not supported
        return false;
    }

    public function getFilename()
    {
        $filename = parent::getFilename();

        // special case when dealing with root dir of reference
        if (($pos = strpos($filename, ':')) !== false) {
            $filename = substr($filename, $pos + 1);
        }

        return $filename;
    }

    public function getPath()
    {
        $path = parent::getPath();

        // special case when dealing with root dir of reference
        if ($path === '') {
            $pathname = $this->getPathname();
            if (($pos = strpos($pathname, ':')) !== false) {
                $path = substr($pathname, 0, $pos + 1);
            }
        }

        return $path;
    }

    public function getRelativePathname($dir = null)
    {
        return $this->getPathname();
    }
}
