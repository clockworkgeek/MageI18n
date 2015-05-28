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
 * Read-only wrapper for git plumbing commands.
 * 
 * Seeking and writing are not possible, only sequential reading is.
 */
class GitFileObject extends SourceFileObject
{

    protected $handle;

    public function __construct($filename)
    {
        $this->setFileClass(__CLASS__);
        $this->setInfoClass(GitFileInfo::class);
        $this->handle = popen('git show '.escapeshellarg($filename), 'r');
    }

    public function __destruct()
    {
        pclose($this->handle);
    }

    public function eof()
    {
        return feof($this->handle);
    }

    public function fgetc()
    {
        return fgetc($this->handle);
    }

    public function fgets()
    {
        return fgets($this->handle);
    }

    public function fgetss($allowable_tags)
    {
        return fgetss($this->handle, null, $allowable_tags);
    }

    public function fpassthru()
    {
        return fpassthru($this->handle);
    }

    public function fread($length)
    {
        return fread($this->handle, $length);
    }

    public function ftell()
    {
        return ftell($this->handle);
    }

    public function getContents()
    {
        return stream_get_contents($this->handle);
    }
}
