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

class UpdateCommand extends Command
{
    protected function configure()
    {
        $this->setName('update')
            ->setDescription('Scan directories/files for translatable strings and update any CSV files found')
            ->addArgument(
                'files',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'List of files to scan.  Defaults to current directory'
            )
            ->addOption(
                'dotfiles',
                null,
                InputOption::VALUE_NONE,
                'Files and directories starting with a "." will be scanned too'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $files = $input->getArgument('files');
        if (! $files) {
            $files = array(getcwd());
        }

        $iterator = new RecursiveSourcesIterator($files);
        if (! $input->getOption('dotfiles')) {
            $iterator = new DotfilesFilter($iterator);
        }

        $scanner = new TreeScanner($output);
        $scanner->scanTree($iterator);
        $strings = $scanner->getStrings();
        $output->writeln("<info>{$strings->count()} translatable strings detected</info>");

        $locales = new CsvFilter(new \RecursiveIteratorIterator($iterator));
        $csvCount = iterator_count($locales);
        $output->writeln("<info>{$csvCount} CSV file(s) found</info>");

        /* @var $locale SourceFileInfo */
        foreach ($locales as $locale) {
            $csv = new Strings();
            $csv->fromCsvFile($locale->getPathname());

            $add = $strings->subtract($csv);

            $delete = $csv->subtract($strings);

            if ($add->count() || $delete->count()) {
                $output->writeln("<info>Adding {$add->count()} strings to and removing {$delete->count()} strings from {$locale->getRelativePathname()}</info>");
                $csv
                    ->merge($add)
                    ->subtract($delete)
                    ->toCsvFile($locale->getPathname());
            }
        }
    }
}
