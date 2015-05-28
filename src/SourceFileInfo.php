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

class SourceFileInfo extends \SplFileInfo
{

    public function __construct($filename)
    {
        parent::__construct($filename);
        $this->setInfoClass(__CLASS__);
        $this->setFileClass(SourceFileObject::class);
    }

    /**
     * Get shortest travel path from $this to $dir or the current working directory.
     * 
     * @param string $dir
     * @return string
     */
    public function getRelativePathname($dir = null)
    {
        $dir = is_null($dir) ? getcwd() : realpath($dir);
        $from = explode(DIRECTORY_SEPARATOR, $dir);
        $to = explode(DIRECTORY_SEPARATOR, $this->getPathname());
        while ($from && $to && ($from[0] === $to[0])) {
            array_shift($from);
            array_shift($to);
        }
        return str_repeat('..'.DIRECTORY_SEPARATOR, count($from)) . implode(DIRECTORY_SEPARATOR, $to);
    }
}
