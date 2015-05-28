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

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeVisitorAbstract;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Expr\Variable;

class PhpStringFinder extends NodeVisitorAbstract
{
    /**
     * @var \MageI18n\Strings
     */
    public $strings;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(OutputInterface $output)
    {
        $this->strings = new Strings;
        $this->output = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
    }

    public function enterNode(Node $node)
    {
        // translations like `$this->__('...')` or `Mage::helper('core')->__('...')`
        if ($node instanceof MethodCall && ($node->name === '__') && $node->args) {
            $arg = $node->args[0];
            if ($arg->value instanceof String_) {
                $this->strings[$arg->value->value] = $arg->value->value;
            }
            elseif ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $this->reportArgument($arg->value);
            }
        }
    }

    protected function reportArgument(Expr $arg)
    {
        if ($arg instanceof BinaryOp) {
            $this->reportArgument($arg->left);
            $this->reportArgument($arg->right);
        }
        elseif ($arg instanceof Encapsed) {
            foreach ($arg->parts as $part) {
                if ($part instanceof Variable) {
                    $this->reportArgument($part);
                }
            }
        }
        elseif ($arg instanceof Variable) {
            $warn = "Translatable text contains variable '{$arg->name}' on line {$arg->getLine()}, consider using literal string instead.";
            $this->output->writeln("<comment>{$warn}</comment>");
        }
    }
}
