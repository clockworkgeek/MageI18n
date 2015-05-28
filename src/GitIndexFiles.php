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
 * Instead of a recursive iterator it is quicker to process an array of relative paths.
 * This is because each git command is forking a new process.
 * Only files (blobs) are listed, not directories (trees).
 */
class GitIndexFiles extends \ArrayObject
{

    public function __construct()
    {
        parent::__construct();

        exec('git ls-files --cached', $output);
        foreach ($output as $filename) {
            $this[$filename] = new GitFileInfo(':' . $filename);
        }
    }
}
