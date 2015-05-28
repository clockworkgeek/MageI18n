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

class RecursiveSourcesIterator implements \RecursiveIterator
{

    /**
     * @var \SplFileInfo[]
     */
    protected $children = array();

    public function __construct(array $sources)
    {
        foreach ($sources as $source) {
            if ($source instanceof \SplFileInfo) {
                $this->children[] = $source;
            }
            elseif (is_string($source) && ($source = realpath($source))) {
                if (is_file($source)) {
                    $child = new SourceFileInfo($source);
                    $child->setInfoClass(SourceFileInfo::class);
                    $this->children[] = $child;
                }
                elseif (is_dir($source)) {
                    $child = new \RecursiveDirectoryIterator($source);
                    $child->setInfoClass(SourceFileInfo::class);
                    $this->children[] = $child;
                }
            }
        }
    }

    /**
     * @return \SplFileInfo
     */
    public function current()
    {
        return current($this->children);
    }

    /**
     * @return int
     */
    public function key()
    {
        return key($this->children);
    }

    public function next()
    {
        next($this->children);
    }

    public function rewind()
    {
        reset($this->children);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return (bool) current($this->children);
    }

    /**
     * @return \RecursiveIterator
     */
    public function getChildren()
    {
        $child = $this->current();
        return $child instanceof \Iterator ? $child : null;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return $this->current()->isDir();
    }
}
