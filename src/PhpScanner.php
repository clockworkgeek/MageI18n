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

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
// emulation is future safe but 5% slower
use PhpParser\Lexer\Emulative as Lexer;
use Symfony\Component\Console\Output\OutputInterface;

class PhpScanner
{

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var NodeTraverser
     */
    protected $traverser;

    /**
     * @var PhpStringFinder
     */
    protected $nodeVisitor;

    public function __construct(OutputInterface $output)
    {
        $this->parser = new Parser(new Lexer);
        $this->traverser = new NodeTraverser();
        $this->nodeVisitor = new PhpStringFinder($output);
        $this->traverser->addVisitor($this->nodeVisitor);
    }

    /**
     * @return \MageI18n\Strings
     */
    public function getStrings()
    {
        return $this->nodeVisitor->strings;
    }

    public function scanCode($code)
    {
        $nodes = $this->parser->parse($code);
        $this->traverser->traverse($nodes);
    }

    public function scanFile($file)
    {
        if (is_string($file)) {
            $this->scanCode(file_get_contents($file));
        }
        elseif ($file instanceof SourceFileObject) {
            $this->scanCode($file->getContents());
        }
    }
}
