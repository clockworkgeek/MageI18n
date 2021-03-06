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

(@include __DIR__.'/../vendor/autoload.php') or (require __DIR__.'/../../../autoload.php');

use Symfony\Component\Console\Application;

$application = new Application('Magento Internationalization', '0.0.2');
$application->add(new ScanCommand);
$application->add(new UpdateCommand);
$application->add(new GitDiffCommand);
$application->run();
