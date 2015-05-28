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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Not as flexible as native git diff command but that would be too complex.
 */
class GitDiffCommand extends Command
{
    protected function configure()
    {
        $this->setName('git-diff')
            ->setDescription('Compares git branches for changed strings and update any CSV files found')
            ->addArgument(
                'before',
                InputArgument::OPTIONAL,
                'Tree-ish state to compare from',
                'HEAD:'
            )
            ->addArgument(
                'after',
                InputArgument::OPTIONAL,
                'Tree-ish state to compare to',
                ':'
            )
            ->addOption(
                'update-dir',
                null,
                InputOption::VALUE_REQUIRED,
                'Set the directory to search for CSV files to update. Defaults to present working directory.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $changed = $this->getChangedFiles(
            $input->getArgument('before'),
            $input->getArgument('after')
        );
        if (! $changed) {
            $output->writeln('<info>Cannot detect any difference</info>');
            return;
        }

        $before = $this->getFiles($input->getArgument('before'));
        $after = $this->getFiles($input->getArgument('after'));

        // attempt to prioritise changed files and quit early if safe
        if (($before instanceof \ArrayAccess) && ($after instanceof \ArrayAccess)) {
            // keys of both should be paths relative to repo
            $beforeScanner = new TreeScanner($output);
            $afterScanner = new TreeScanner($output);
            foreach ($changed as $filename) {
                if (isset($before[$filename])) {
                    $beforeScanner->scanFile($before[$filename]);
                    unset($before[$filename]);
                }
                if (isset($after[$filename])) {
                    $afterScanner->scanFile($after[$filename]);
                    unset($after[$filename]);
                }
            }
            // strings are ArrayObjects so comparison is easy
            if ($beforeScanner->getStrings() == $afterScanner->getStrings()) {
                $output->writeln('<info>Cannot detect significant difference in changed files</info>');
                return;
            }
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln('<comment>Immediate files show a significant difference</comment>');
            }

            // continue with rest of project files
            $beforeScanner->scanTree($before);
            $beforeStrings = $beforeScanner->getStrings();
            $afterScanner->scanTree($after);
            $afterStrings = $afterScanner->getStrings();
        }
        else {
            $beforeStrings = $this->getStrings($before, $output);
            $afterStrings = $this->getStrings($after, $output);
        }

        $add = $afterStrings->subtract($beforeStrings);
        $delete = $beforeStrings->subtract($afterStrings);
        if ($output->getVerbosity() === OutputInterface::VERBOSITY_NORMAL) {
            $output->writeln("<info>{$add->count()} strings to be added and {$delete->count()} to be removed</info>");
        }
        else {
            $output->writeln('<info>Add the following:</info>');
            $output->writeln($add->toCsvArray());
            $output->writeln('<info>Remove the following:</info>');
            $output->writeln($delete->toCsvArray());
        }
        if (!$add->count() && !$delete->count()) {
            // nothing to do
            return;
        }

        // if update-dir is null, realpath defaults to cwd
        $updateDir = realpath($input->getOption('update-dir'));
        $dir = new RecursiveSourcesIterator(array($updateDir));
        $locales = new CsvFilter(new \RecursiveIteratorIterator($dir));
        $csvCount = iterator_count($locales);
        $output->writeln("<info>{$csvCount} CSV file(s) found</info>");

        /* @var $locale SourceFileInfo */
        foreach ($locales as $locale) {
            $csv = new Strings();
            $csv->fromCsvFile($locale->getPathname());
            // careful to not overwrite good translations
            $csvAdd = $add->subtract($csv);
            // count deletions which actually have an effect
            $csvDel = $delete->subtract($csv);

            if ($csvAdd->count() || $csvDel->count()) {
                $output->writeln("<info>Adding {$csvAdd->count()} strings to and removing {$csvDel->count()} strings from {$locale->getRelativePathname()}</info>");
                $csv
                    ->merge($csvAdd)
                    ->subtract($csvDel)
                    ->toCsvFile($locale->getPathname());
                exec('git add '.escapeshellarg($locale->getPathname()));
            }
        }
    }

    protected function getFiles($tree)
    {
        if ($tree === ':') {
            return new GitIndexFiles();
        }
        if (preg_match('#^\w+:#', $tree)) {
            return new GitBranchFiles($tree);
        }
        if (($path = realpath($tree))) {
            return new \RecursiveDirectoryIterator($path);
        }

        throw new \InvalidArgumentException($tree.' could not be interpreted as a path');
    }

    protected function getChangedFiles($tree1, $tree2)
    {
        $index = ($tree1 === ':') || ($tree2 === ':');
        $branch = preg_match('#^\w+:#', $tree1) ? $tree1 : (preg_match('#^\w+:#', $tree2) ? $tree2 : false);
        if ($branch && $index) {
            $command = 'git diff-index --cached --name-only '.escapeshellarg($branch);
        }
        elseif ($branch) {
            $command = 'git diff-index --name-only '.escapeshellarg($branch);
        }
        elseif ($index) {
            $command = 'git diff --cached --name-only';
        }
        else {
            throw new \InvalidArgumentException('"magei18n git-dff" does not know how to diff two non-branches.');
        }
        exec($command, $filenames);
        return $filenames;
    }

    /**
     * Scan $files for translatable content
     * 
     * @param \Traversable $files
     * @param OutputInterface $output
     * @return \MageI18n\Strings
     */
    protected function getStrings(\Traversable $files, OutputInterface $output)
    {
        $scanner = new TreeScanner($output);
        $scanner->scanTree($files);
        return $scanner->getStrings();
    }
}
