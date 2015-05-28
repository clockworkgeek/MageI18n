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
 * Collection of translatable strings.
 * 
 * Keys are internationalised strings, values are localised strings.
 * Using strings as keys ensures there are no duplicates.
 */
class Strings extends \ArrayObject
{

    /**
     * Load valid string pairs from CSV file, overwriting strings in this object where necessary.
     * 
     * @param string $filename
     */
    public function fromCsvFile($filename)
    {
        $file = fopen($filename, 'r');
        if ($file !== false) {
            while (($row = fgetcsv($file)) !== false) {
                if (count($row) === 2) {
                    list($key, $val) = $row;
                    $this[$key] = $val;
                }
            }
            fclose($file);
        }
    }

    /**
     * Write all these strings to $filename.
     * 
     * Set $mode to 'a' to append instead of overwriting.
     * 
     * @param string $filename
     * @param string $mode
     */
    public function toCsvFile($filename, $mode = 'w')
    {
        $file = fopen($filename, $mode);
        if ($file !== false) {
            foreach ($this as $col1 => $col2) {
                fwrite($file, self::quoteCol($col1) . ',' . self::quoteCol($col2) . "\n");
            }
            fclose($file);
        }
    }

    public function toCsvArray()
    {
        $csv = array();
        foreach ($this as $col1 => $col2) {
            $csv[] = self::quoteCol($col1) . ',' . self::quoteCol($col2);
        }
        return $csv;
    }

    protected static function quoteCol($string)
    {
        return '"' . str_replace('"', '""', $string) . '"';
    }

    /**
     * Create a new object based on this and another object.
     * 
     * The input overrides equivalent values from this object.
     * 
     * @param \MageI18n\Strings $input
     * @return \MageI18n\Strings
     */
    public function merge(Strings $input)
    {
        $output = new Strings($this->getArrayCopy());
        foreach ($input as $key => $val) {
            $output[$key] = $val;
        }
        $output->natcasesort();
        return $output;
    }

    /**
     * Create a new object based on this and another object.
     * 
     * The values from this object not in $input will be returned.
     * 
     * @param \MageI18n\Strings $input
     * @return \MageI18n\Strings
     */
    public function subtract(Strings $input)
    {
        $output = new Strings($this->getArrayCopy());
        foreach ($input as $key => $val) {
            if (isset($output[$key])) {
                unset($output[$key]);
            }
        }
        return $output;
    }
}
