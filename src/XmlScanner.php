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

class XmlScanner
{

    /**
     * @var \MageI18n\Strings
     */
    protected $strings;

    public function __construct()
    {
        $this->strings = new Strings;
    }

    /**
     * @return \MageI18n\Strings
     */
    public function getStrings()
    {
        return $this->strings;
    }

    public function scanXml($source)
    {
        $document = new \DOMDocument();
        $document->loadXML($source);
        $xpath = new \DOMXPath($document);

        /* @var $nodes \DOMNodeList */
        $nodes = $xpath->query('//*[@translate]');
        /* @var $node \DOMNode */
        foreach ($nodes as $node) {
            if ($node instanceof \DOMElement) {
                $translate = $node->getAttribute('translate');
                $childNames = explode(' ', $translate);
                foreach ($node->childNodes as $child) {
                    if (in_array($child->nodeName, $childNames)) {
                        $this->strings[$child->nodeValue] = $child->nodeValue;
                    }
                }
            }
        }
    }

    public function scanFile($file)
    {
        if (is_string($file)) {
            $this->scanXml(file_get_contents($file));
        }
        elseif ($file instanceof SourceFileObject) {
            $this->scanXml($file->getContents());
        }
    }
}
