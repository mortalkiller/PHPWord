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

namespace PhpOffice\PhpWord\Writer\Word2007\Part;

use PhpOffice\PhpWord\Element\Chart as ChartElement;
use PhpOffice\PhpWord\Shared\XMLWriter;

/**
 * Word2007 chart part writer: word/charts/chartx.xml.
 *
 * @since 0.12.0
 * @see  http://www.datypic.com/sc/ooxml/e-draw-chart_chartSpace.html
 */
class Chart extends AbstractPart
{
    /**
     * Chart element.
     *
     * @var ChartElement
     */
    private $element;

    /**
     * Type definition.
     *
     * @var array
     */
    private $types = [
        'pie' => ['type' => 'pie', 'colors' => 1],
        'doughnut' => ['type' => 'doughnut', 'colors' => 1, 'hole' => 75, 'no3d' => true],
        'bar' => ['type' => 'bar', 'colors' => 0, 'axes' => true, 'bar' => 'bar', 'grouping' => 'clustered'],
        'stacked_bar' => ['type' => 'bar', 'colors' => 0, 'axes' => true, 'bar' => 'bar', 'grouping' => 'stacked'],
        'percent_stacked_bar' => ['type' => 'bar', 'colors' => 0, 'axes' => true, 'bar' => 'bar', 'grouping' => 'percentStacked'],
        'column' => ['type' => 'bar', 'colors' => 0, 'axes' => true, 'bar' => 'col', 'grouping' => 'clustered'],
        'stacked_column' => ['type' => 'bar', 'colors' => 0, 'axes' => true, 'bar' => 'col', 'grouping' => 'stacked'],
        'percent_stacked_column' => ['type' => 'bar', 'colors' => 0, 'axes' => true, 'bar' => 'col', 'grouping' => 'percentStacked'],
        'line' => ['type' => 'line', 'colors' => 0, 'axes' => true],
        'area' => ['type' => 'area', 'colors' => 0, 'axes' => true],
        'radar' => ['type' => 'radar', 'colors' => 0, 'axes' => true, 'radar' => 'standard', 'no3d' => true],
        'scatter' => ['type' => 'scatter', 'colors' => 0, 'axes' => true, 'scatter' => 'marker', 'no3d' => true],
    ];

    private array $legendOptions = [
        'showLegend' => true,
        'deleteLegendEntry' => [],
        'overlay' => '0'
    ];

    /**
     * Chart options.
     *
     * @var array
     */
    private $options = [];

    /**
     * Set chart element.
     */
    public function setElement(ChartElement $element): void
    {
        $this->element = $element;
    }

    /**
     * Write part.
     *
     * @return string
     */
    public function write()
    {
        $xmlWriter = $this->getXmlWriter();

        $xmlWriter->startDocument('1.0', 'UTF-8', 'yes');
        $xmlWriter->startElement('c:chartSpace');
        $xmlWriter->writeAttribute('xmlns:c', 'http://schemas.openxmlformats.org/drawingml/2006/chart');
        $xmlWriter->writeAttribute('xmlns:a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
        $xmlWriter->writeAttribute('xmlns:r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

        $xmlWriter->writeElementBlock('c:lang', 'val', 'en-CA');
        $xmlWriter->writeElementBlock('roundedCorners', 'val', '0');

        $this->writeChart($xmlWriter);
        $this->writeShape($xmlWriter, false);

        $xmlWriter->endElement(); // c:chartSpace

        return $xmlWriter->getData();
    }

    /**
     * Write chart.
     *
     * @see  http://www.datypic.com/sc/ooxml/t-draw-chart_CT_Chart.html
     */
    private function writeChart(XMLWriter $xmlWriter): void
    {
        $xmlWriter->startElement('c:chart');

        $this->writePlotArea($xmlWriter);

        $xmlWriter->writeElementBlock('c:plotVisOnly', 'val', '1');
        $xmlWriter->writeElementBlock('c:dispBlanksAs', 'val', 'zero');

        $xmlWriter->endElement(); // c:chart
    }

    /**
     * Write plot area.
     *
     * @see  http://www.datypic.com/sc/ooxml/t-draw-chart_CT_PlotArea.html
     * @see  http://www.datypic.com/sc/ooxml/t-draw-chart_CT_PieChart.html
     * @see  http://www.datypic.com/sc/ooxml/t-draw-chart_CT_DoughnutChart.html
     * @see  http://www.datypic.com/sc/ooxml/t-draw-chart_CT_BarChart.html
     * @see  http://www.datypic.com/sc/ooxml/t-draw-chart_CT_LineChart.html
     * @see  http://www.datypic.com/sc/ooxml/t-draw-chart_CT_AreaChart.html
     * @see  http://www.datypic.com/sc/ooxml/t-draw-chart_CT_RadarChart.html
     * @see  http://www.datypic.com/sc/ooxml/t-draw-chart_CT_ScatterChart.html
     */
    private function writePlotArea(XMLWriter $xmlWriter): void
    {
        $type = $this->element->getType();
        $style = $this->element->getStyle();
        $this->options = $this->types[$type];

        $title = $style->getTitle();



        //Chart title
        if ($title) {
            $xmlWriter->startElement('c:title');
            $xmlWriter->startElement('c:tx');
            $xmlWriter->startElement('c:rich');
            $xmlWriter->writeRaw('
                <a:bodyPr/>
                <a:lstStyle/>
                <a:p>
                <a:pPr>
                <a:defRPr/></a:pPr><a:r><a:rPr/><a:t>' . $title . '</a:t></a:r>
                <a:endParaRPr/>
                </a:p>');
            $xmlWriter->endElement(); // c:rich
            $xmlWriter->endElement(); // c:tx
            $xmlWriter->endElement(); // c:title
        } else {
            $xmlWriter->writeElementBlock('c:autoTitleDeleted', 'val', 1);
        }

        $xmlWriter->startElement('c:plotArea');
//        $xmlWriter->writeElement('c:layout');

        // Chart

        // Series
        $this->writeSeries($xmlWriter, isset($this->options['scatter']));

        // Axes
        if (isset($this->options['axes'])) {
            $this->writeAxis($xmlWriter, 'cat');
            $this->writeAxis($xmlWriter, 'val');
        }
        $this->writeShape($xmlWriter);
        $xmlWriter->endElement(); // c:plotArea

        //Chart legend
        $this->legendOptions['showLegend'] = $style->isShowLegend();
        if ($this->legendOptions['showLegend']) {
            $this->legendOptions['legendPosition'] = $style->getLegendPosition();

            $this->writeLegends($xmlWriter);
        }
    }

    private function writeLegends(XMLWriter $xmlWriter): void
    {
        $style = $this->element->getStyle();
        $dataLegendFormat = $style->getDataLegendFormat();

        $xmlWriter->startElement('c:legend');
        $xmlWriter->writeElementBlock('c:legendPos', 'val', $this->legendOptions['legendPosition']);


        if(count($this->legendOptions['deleteLegendEntry'])) {
            foreach ($this->legendOptions['deleteLegendEntry'] as $index) {
                $xmlWriter->startElement('c:legendEntry');
                $xmlWriter->writeElementBlock('c:idx', 'val', $index);
                $xmlWriter->writeElementBlock('c:delete', 'val', true);
                $xmlWriter->endElement();
            }
        }
        $xmlWriter->writeElementBlock('c:overlay', 'val', $this->legendOptions['overlay']);

        $xmlWriter->writeRaw('
            <c:spPr>
                <a:noFill/>
                <a:ln w="0">
                    <a:noFill/>
                </a:ln>
            </c:spPr>
            <c:txPr>
                <a:bodyPr/>
                <a:lstStyle/>
                <a:p>
                    <a:pPr>
                        <a:defRPr b="0" sz="'.$dataLegendFormat['fontSize'].'" strike="noStrike" u="none">
                            <a:solidFill>
                                <a:srgbClr val="000000"/>
                            </a:solidFill>
                            <a:uFillTx/>
                            <a:latin typeface="'.$dataLegendFormat['typeface'].'"/>
                        </a:defRPr>
                    </a:pPr>
                </a:p>
            </c:txPr>
        ');


        $xmlWriter->endElement(); // c:legend
    }
    /**
     * Write series.
     *
     * @param bool $scatter
     */
    private function writeSeries(XMLWriter $xmlWriter, $scatter = false): void
    {
        $series = $this->element->getSeries();
        $style = $this->element->getStyle();
        $colors = $style->getColors();

        $index = 0;
        $colorIndex = 0;

        foreach ($series as $type => $items) {
            // Chart
            $this->startChart($xmlWriter, $type);

            foreach ($items as $seriesItem) {

                $style = $seriesItem['style'] ?? $style;
                $colors = $style->getColors();

                $categories = $seriesItem['categories'];
                $values = $seriesItem['values'];

                $xmlWriter->startElement('c:ser');

                $xmlWriter->writeElementBlock('c:idx', 'val', $index);
                $xmlWriter->writeElementBlock('c:order', 'val', $index);

                if (!is_null($seriesItem['name']) && $seriesItem['name'] != '') {
                    $xmlWriter->startElement('c:tx');
                    $xmlWriter->startElement('c:strRef');
                    $xmlWriter->startElement('c:strCache');
                    $xmlWriter->writeElementBlock('c:ptCount', 'val', 1);
                    $xmlWriter->startElement('c:pt');
                    $xmlWriter->writeAttribute('idx', 0);
                    $xmlWriter->startElement('c:v');
                    $xmlWriter->writeRaw($seriesItem['name']);
                    $xmlWriter->endElement(); // c:v
                    $xmlWriter->endElement(); // c:pt
                    $xmlWriter->endElement(); // c:strCache
                    $xmlWriter->endElement(); // c:strRef
                    $xmlWriter->endElement(); // c:tx
                }
                // The c:dLbls was added to make word charts look more like the reports in SurveyGizmo
                // This section needs to be made configurable before a pull request is made
                $xmlWriter->startElement('c:dLbls');

                $DataLabelOptions = $style->getDataLabelOptions();

                if(! $DataLabelOptions['showLegendKey']) {
                    $this->legendOptions['deleteLegendEntry'][$index] = $index;
                }

                $DataLabelFormat = $style->getDataLabelFormat();
                if(isset($DataLabelFormat['formatCode'])) {
                    $xmlWriter->writeRaw('<c:numFmt formatCode="'.$DataLabelFormat['formatCode'].'" sourceLinked="0"/>');
                }
                $xmlWriter->writeRaw('<c:txPr>
                            <a:bodyPr wrap="square"/>
                            <a:lstStyle/>
                            <a:p>
                                <a:pPr>
                                    <a:defRPr b="0" sz="'.$DataLabelFormat['fontSize'].'" strike="noStrike" u="none">
                                        <a:solidFill>
                                            <a:srgbClr val="000000"/>
                                        </a:solidFill>
                                        <a:latin typeface="Calibri"/>
                                    </a:defRPr>
                                </a:pPr>
                            </a:p>
                        </c:txPr>');

                $xmlWriter->writeRaw('<c:extLst>
                            <c:ext uri="{CE6537A1-D6FC-4f65-9D91-7224C49458BB}"
                                   xmlns:c15="http://schemas.microsoft.com/office/drawing/2012/chart">
                                <c15:showLeaderLines val="1"/>
                            </c:ext>
                        </c:extLst>');
                foreach ($DataLabelOptions as $option => $val) {
                    $xmlWriter->writeElementBlock("c:{$option}", 'val', (int) $val);
                }

                $xmlWriter->endElement(); // c:dLbls

                if (isset($this->options['scatter'])) {
                    $this->writeShape($xmlWriter);
                }

                if ($scatter === true) {
                    $this->writeSeriesItem($xmlWriter, 'xVal', $categories);
                    $this->writeSeriesItem($xmlWriter, 'yVal', $values);
                } else {
                    $this->writeSeriesItem($xmlWriter, 'cat', $categories);
                    $this->writeSeriesItem($xmlWriter, 'val', $values);

                    // check that there are colors
                    if (is_array($colors) && count($colors) > 0) {
//                        if($type === 'line') {
//                            dd($colors[$colorIndex++ % count($colors)]);
//                        }
                        // assign a color to each value
                        $xmlWriter->startElement('c:spPr');
                        $xmlWriter->startElement('a:solidFill');
                        $xmlWriter->writeElementBlock('a:srgbClr', 'val', $colors[$colorIndex++ % count($colors)]);
                        $xmlWriter->endElement(); // a:solidFill
                        if($type === 'line') {
                            // assign a color to each value
                            $xmlWriter->startElement('a:ln');
                            $xmlWriter->writeAttribute('w', '28440');
                            $xmlWriter->startElement('a:solidFill');
                            $xmlWriter->writeElementBlock('a:srgbClr', 'val', $colors[$colorIndex++ % count($colors)]);
                            $xmlWriter->endElement(); // a:solidFill
                            $xmlWriter->endElement(); // a:ln
                        }
                        $xmlWriter->endElement(); // c:spPr
                    }
                }

                if($type === 'line') {
                    // assign a color to each value
                    $xmlWriter->startElement('c:marker');
                    $xmlWriter->writeElementBlock('c:symbol', 'val', $style->getLineSymbol());
                    $xmlWriter->endElement(); // a:solidFill
                    $xmlWriter->writeElementBlock('c:smooth','val', 0); // a:solidFill
                }



                $xmlWriter->endElement(); // c:ser
                ++$index;
            }
            $this->endChart($xmlWriter);
        }
    }

    /**
     * Write series items.
     *
     * @param string $type
     * @param array $values
     */
    private function writeSeriesItem(XMLWriter $xmlWriter, $type, $values): void
    {
        $types = [
            'cat' => ['c:cat', 'c:strLit'],
            'val' => ['c:val', 'c:numLit'],
            'xVal' => ['c:xVal', 'c:strLit'],
            'yVal' => ['c:yVal', 'c:numLit'],
        ];
        [$itemType, $itemLit] = $types[$type];

        $xmlWriter->startElement($itemType);
        $xmlWriter->startElement($itemLit);
        if ($type == 'val') {
            $style = $this->element->getStyle();
            $DataLabelFormat = $style->getDataLabelFormat();
            $xmlWriter->writeElement('c:formatCode', $DataLabelFormat['formatCode']);
        }

        $xmlWriter->writeElementBlock('c:ptCount', 'val', count($values));

        $index = 0;

        foreach ($values as $value) {
            $xmlWriter->startElement('c:pt');
            $xmlWriter->writeAttribute('idx', $index);
            if (\PhpOffice\PhpWord\Settings::isOutputEscapingEnabled()) {
                $xmlWriter->writeElement('c:v', $value);
            } else {
                $xmlWriter->startElement('c:v');
                $xmlWriter->writeRaw($value);
                $xmlWriter->endElement(); // c:v
            }
            $xmlWriter->endElement(); // c:pt
            ++$index;
        }

        $xmlWriter->endElement(); // $itemLit
        $xmlWriter->endElement(); // $itemType
    }

    /**
     * Write axis.
     *
     * @see  http://www.datypic.com/sc/ooxml/t-draw-chart_CT_CatAx.html
     *
     * @param string $type
     */
    private function writeAxis(XMLWriter $xmlWriter, $type): void
    {
        $style = $this->element->getStyle();
        $types = [
            'cat' => ['c:catAx', 1, 'b', 2],
            'val' => ['c:valAx', 2, 'l', 1],
        ];
        [$axisType, $axisId, $axisPos, $axisCross] = $types[$type];

        $xmlWriter->startElement($axisType);

        $xmlWriter->writeElementBlock('c:axId', 'val', $axisId);
        $xmlWriter->writeElementBlock('c:axPos', 'val', $axisPos);

        $categoryAxisTitle = $style->getCategoryAxisTitle();
        $valueAxisTitle = $style->getValueAxisTitle();

        if ($axisType == 'c:catAx') {
            if (null !== $categoryAxisTitle) {
                $this->writeAxisTitle($xmlWriter, $categoryAxisTitle);
            }
            $this->writeShape($xmlWriter, $style->getCategoryAxisLine());
        } elseif ($axisType == 'c:valAx') {
            if (null !== $valueAxisTitle) {
                $this->writeAxisTitle($xmlWriter, $valueAxisTitle);
            }
            $this->writeShape($xmlWriter, $style->getValueAxisLine());
        }

        $xmlWriter->writeElementBlock('c:crossAx', 'val', $axisCross);
        $xmlWriter->writeElementBlock('c:auto', 'val', 1);

        if (isset($this->options['axes'])) {
            $xmlWriter->writeElementBlock('c:delete', 'val', 0);
            $xmlWriter->writeElementBlock('c:majorTickMark', 'val', $style->getMajorTickPosition());
            $xmlWriter->writeElementBlock('c:minorTickMark', 'val', 'none');
            if ($style->showAxisLabels()) {
                if ($axisType == 'c:catAx') {
                    $xmlWriter->writeElementBlock('c:tickLblPos', 'val', $style->getCategoryLabelPosition());
                } else {
                    $xmlWriter->writeElementBlock('c:tickLblPos', 'val', $style->getValueLabelPosition());
                }
            } else {
                $xmlWriter->writeElementBlock('c:tickLblPos', 'val', 'none');
            }
            $xmlWriter->writeElementBlock('c:crosses', 'val', 'autoZero');
        }
        if (isset($this->options['radar']) || ($type == 'cat' && $style->showGridX()) || ($type == 'val' && $style->showGridY())) {
            $xmlWriter->startElement('c:majorGridlines');
            $this->writeShape($xmlWriter, true);
            $xmlWriter->endElement(); // c:majorGridlines
        }

        $xmlWriter->startElement('c:scaling');
        $xmlWriter->writeElementBlock('c:orientation', 'val', 'minMax');
        $xmlWriter->endElement(); // c:scaling


        if ($axisType == 'c:catAx') {
            $CategoryLabelFormat = $style->getCategoryLabelFormat();
            if(isset($CategoryLabelFormat['formatCode'])) {
                $xmlWriter->writeRaw('<c:numFmt formatCode="'.$CategoryLabelFormat['formatCode'].'" sourceLinked="0"/>');
            }
            $xmlWriter->writeRaw('<c:txPr>
                            <a:bodyPr wrap="square"/>
                            <a:lstStyle/>
                            <a:p>
                                <a:pPr>
                                    <a:defRPr b="0" sz="'.$CategoryLabelFormat['fontSize'].'" strike="noStrike" u="none">
                                        <a:solidFill>
                                            <a:srgbClr val="000000"/>
                                        </a:solidFill>
                                        <a:uFillTx/>
                                        <a:latin typeface="'.$CategoryLabelFormat['typeface'].'"/>
                                    </a:defRPr>
                                </a:pPr>
                            </a:p>
                        </c:txPr>');
        } else if ($axisType == 'c:valAx') {
            $DataLabelFormat = $style->getDataLabelFormat();
            if(isset($DataLabelFormat['formatCode'])) {
                $xmlWriter->writeRaw('<c:numFmt formatCode="'.$DataLabelFormat['formatCode'].'" sourceLinked="0"/>');
            }
            $xmlWriter->writeRaw('<c:txPr>
                            <a:bodyPr wrap="square"/>
                            <a:lstStyle/>
                            <a:p>
                                <a:pPr>
                                    <a:defRPr b="0" sz="'.$DataLabelFormat['fontSize'].'" strike="noStrike" u="none">
                                        <a:solidFill>
                                            <a:srgbClr val="000000"/>
                                        </a:solidFill>
                                        <a:uFillTx/>
                                        <a:latin typeface="'.$DataLabelFormat['typeface'].'"/>
                                    </a:defRPr>
                                </a:pPr>
                            </a:p>
                        </c:txPr>');
        }

        $xmlWriter->endElement(); // $axisType
    }

    /**
     * Write shape.
     *
     * @see  http://www.datypic.com/sc/ooxml/t-a_CT_ShapeProperties.html
     *
     * @param bool $line
     */
    private function writeShape(XMLWriter $xmlWriter, $line = false, $color=null, $backgroudcolor = null): void
    {
        $xmlWriter->startElement('c:spPr');

        if($line === true && $backgroudcolor) {
            $xmlWriter->startElement('a:solidFill');
            $xmlWriter->writeElementBlock('a:srgbClr', 'val', $backgroudcolor);
            $xmlWriter->endElement(); // a:solidFill
        } else {
            $xmlWriter->writeElement('a:noFill');
        }


        $xmlWriter->startElement('a:ln');
        if ($line === true) {
            if(! $color) {
                $xmlWriter->writeElement('a:solidFill');
            } else {
                $xmlWriter->startElement('a:solidFill');
                $xmlWriter->writeElementBlock('a:srgbClr', 'val', $color);
                $xmlWriter->endElement(); // a:solidFill
            }

        } else {
            $xmlWriter->writeElement('a:noFill');
        }
        $xmlWriter->endElement(); // a:ln
        $xmlWriter->endElement(); // c:spPr
    }

    private function writeAxisTitle(XMLWriter $xmlWriter, $title): void
    {
        $xmlWriter->startElement('c:title'); //start c:title
        $xmlWriter->startElement('c:tx'); //start c:tx
        $xmlWriter->startElement('c:rich'); // start c:rich
        $xmlWriter->writeElement('a:bodyPr');
        $xmlWriter->writeElement('a:lstStyle');
        $xmlWriter->startElement('a:p');
        $xmlWriter->startElement('a:pPr');
        $xmlWriter->writeElement('a:defRPr');
        $xmlWriter->endElement(); // end a:pPr
        $xmlWriter->startElement('a:r');
        $xmlWriter->writeElementBlock('a:rPr', 'lang', 'en-US');

        $xmlWriter->startElement('a:t');
        $xmlWriter->writeRaw($title);
        $xmlWriter->endElement(); //end a:t

        $xmlWriter->endElement(); // end a:r
        $xmlWriter->endElement(); //end a:p
        $xmlWriter->endElement(); //end c:rich
        $xmlWriter->endElement(); // end c:tx
        $xmlWriter->writeElementBlock('c:overlay', 'val', '0');
        $xmlWriter->endElement(); // end c:title
    }

    private function startChart(XMLWriter $xmlWriter, $type)
    {

        $options = $this->types[$type];
        $chartType = $options['type'];
        $chartType .= $this->element->getStyle()->is3d() && !isset($this->options['no3d']) ? '3D' : '';
        $chartType .= 'Chart';
        $xmlWriter->startElement("c:{$chartType}");

        $xmlWriter->writeElementBlock('c:varyColors', 'val', $options['colors']);
        if ($type == 'area') {
            $xmlWriter->writeElementBlock('c:grouping', 'val', 'standard');
        }
        if (isset($options['hole'])) {
            $xmlWriter->writeElementBlock('c:holeSize', 'val', $options['hole']);
        }
        if (isset($options['bar'])) {
            $xmlWriter->writeElementBlock('c:barDir', 'val', $options['bar']); // bar|col
            $xmlWriter->writeElementBlock(
                'c:grouping',
                'val',
                $options['grouping']
            ); // 3d; standard = percentStacked
        }
        if (isset($options['radar'])) {
            $xmlWriter->writeElementBlock('c:radarStyle', 'val', $options['radar']);
        }
        if (isset($options['scatter'])) {
            $xmlWriter->writeElementBlock('c:scatterStyle', 'val', $options['scatter']);
        }
    }

    private function endChart(XMLWriter $xmlWriter)
    {
        // don't overlap if grouping is 'clustered'
        if (!isset($this->options['grouping']) || $this->options['grouping'] != 'clustered') {
            $xmlWriter->writeElementBlock('c:overlap', 'val', '100');
        }

        // Axes
        if (isset($this->options['axes'])) {
            $xmlWriter->writeElementBlock('c:axId', 'val', 1);
            $xmlWriter->writeElementBlock('c:axId', 'val', 2);
        }

        $xmlWriter->endElement(); // chart type
    }
}
