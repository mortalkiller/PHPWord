<?php

/**
 * This file is part of PHPWord - A pure PHP library for reading and writing
 * word processing documents.
 *
 * PHPWord is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
 *
 * @see         https://github.com/PHPOffice/PHPWord
 *
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpWordTests\Writer\Word2007\Part;

use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Writer\Word2007;
use PhpOffice\PhpWord\Writer\Word2007\Part\Header;
use PhpOffice\PhpWordTests\TestHelperDOCX;

/**
 * Test class for PhpOffice\PhpWord\Writer\Word2007\Part\Header.
 *
 * @runTestsInSeparateProcesses
 */
class HeaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Write header.
     */
    public function testWriteHeader(): void
    {
        $imageSrc = __DIR__ . '/../../../_files/images/PhpWord.png';

        $container = new \PhpOffice\PhpWord\Element\Header(1);
        $container->addText('Test');
        $container->addPreserveText('');
        $container->addTextBreak();
        $container->addTextRun();
        $container->addTable()->addRow()->addCell()->addText('');
        $container->addImage($imageSrc);
        $container->addWatermark($imageSrc);

        $writer = new Word2007();
        $dir = Settings::getTempDir() . DIRECTORY_SEPARATOR . 'phpwordcachefooter';
        if (!is_dir($dir) && !mkdir($dir)) {
            self::fail('Unable to create temp directory');
        }
        $writer->setUseDiskCaching(true, $dir);
        $object = new Header();
        $object->setParentWriter($writer);
        $object->setElement($container);
        $xml = simplexml_load_string($object->write());

        self::assertInstanceOf('SimpleXMLElement', $xml);
        TestHelperDOCX::deleteDir($dir);
    }
}
