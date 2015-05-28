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

use Symfony\Component\Console\Output\OutputInterface;
class TreeScanner
{

    /**
     * @var PhpScanner
     */
    protected $phpScanner;

    /**
     * @var XmlScanner
     */
    protected $xmlScanner;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(OutputInterface $output)
    {
        $this->phpScanner = new PhpScanner($output);
        $this->xmlScanner = new XmlScanner($output);
        $this->output = $output;
    }

    public function scanTree(\Traversable $tree)
    {
        if ($tree instanceof \RecursiveIterator) {
            $iterator = new \RecursiveIteratorIterator($tree);
        }
        else {
            $iterator = $tree;
        }
        /* @var $file SourceFileInfo */
        foreach ($iterator as $file) {
            $this->scanFile($file);
        }
    }

    public function scanFile(SourceFileInfo $file)
    {
        // tried `finfo` but it was slow
        switch ($file->getExtension()) {
            case 'php':
            case 'phtml':
                $this->notifyFile($file);
                $this->phpScanner->scanFile($file->openFile());
                return;
            case 'xml':
                $this->notifyFile($file);
                $this->xmlScanner->scanFile($file->openFile());
                return;
        }
    }

    protected function notifyFile(SourceFileInfo $file)
    {
        $output = $this->output instanceof ConsoleOutputInterface ? $this->output->getErrorOutput() : $this->output;
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln("<info>Scanning {$file->getRelativePathname()}</info>");
        }
    }

    /**
     * @return \MageI18n\Strings
     */
    public function getStrings()
    {
        $strings = $this->phpScanner->getStrings()->merge($this->xmlScanner->getStrings());
        $strings->natcasesort();
        return $strings;
    }
}
