<?php

/**
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_report_export_fpdi extends TCPDF
{
    /**
     * PDF default font.
     *
     * @var  string
     */
    protected $m_defaultFont = 'helvetica';

    /**
     * Variable defines the default orientation: L: Landscape, P: Portrait.
     *
     * @var  string
     */
    protected $m_defaultPageOrientation = 'L';

    /**
     * Default page unit.
     *
     * @var  string
     */
    protected $m_defaultPageUnit = 'mm';

    /**
     * @param string $p_orientation
     * @param string $p_unit
     * @param string $p_format
     * @param bool   $p_unicode
     * @param string $p_encoding
     * @param bool   $p_diskcache
     * @param bool   $p_pdfa
     *
     * @return isys_report_export_fpdi
     */
    public static function factory($p_orientation = 'P', $p_unit = 'mm', $p_format = 'A4', $p_unicode = true, $p_encoding = 'UTF-8', $p_diskcache = false, $p_pdfa = false)
    {
        return new self($p_orientation, $p_unit, $p_format, $p_unicode, $p_encoding, $p_diskcache, $p_pdfa);
    }

    /**
     * Draw footer
     */
    public function Footer()
    {
        $this->SetY(-15);

        $this->SetFont($this->m_defaultFont, 'I', 8);

        $this->writeHTML('{:pnp:} / {:ptp:}', true, false, true, false, 'C');
    }

    /**
     * Render header.
     */
    public function Header()
    {
        $this->SetFont($this->m_defaultFont, 'B', 15);

        $this->SetY(15);

        $this->Cell(0, 10, 'i-doit Report - ' . $this->title, 1, 2, 'C');
    }

    /**
     * Initialize formatter
     *
     * @param   array $p_options
     *
     * @return  $this
     */
    public function initialize($p_options)
    {
        $this->setFontSubsetting(false);

        // Page orientation
        $this->setPageOrientation($this->m_defaultPageOrientation);

        // Set default page unit
        $this->setPageUnit($this->m_defaultPageUnit);

        // Default margins
        $this->SetMargins(20, 30, 20, true);

        // Set PDF title
        if (isset($p_options['pdf.title'])) {
            $this->SetTitle(utf8_encode($p_options['pdf.title']));
        }

        // Set PDF subject
        if (isset($p_options['pdf.subject'])) {
            $this->SetSubject($p_options['pdf.subject']);
        }

        $this->AddPage();

        return $this;
    }

    /**
     * Render the colored table.
     *
     * @param   array $header
     * @param   array $p_data
     *
     * @return  $this
     */
    public function reportTable($header, $p_data)
    {
        $this->SetFont($this->m_defaultFont, '', 10);

        if (is_array($header) && count($header) && is_array($p_data) && count($p_data)) {
            $dataChunks = array_chunk($p_data, 250);

            foreach ($dataChunks as $chunk) {
                $this->renderTableChunk($header, $chunk);
            }
        }

        return $this;
    }

    /**
     * Method for rendering a table chunk to the PDF file.
     *
     * @param array $header
     * @param array $data
     */
    private function renderTableChunk(array $header, array $data)
    {
        $language = isys_application::instance()->container->get('language');

        $dom = new DOMDocument('1.0', 'utf-8');
        $domTable = $dom->createElement('table');
        $domTable->setAttribute('style', 'border:2px solid #888;');
        $domTable->setAttribute('cellspacing', '0');
        $domTable->setAttribute('cellpadding', '0');

        // Create a row for the table header.
        $domTableRow = $dom->createElement('tr');
        $domTableRow->setAttribute('style', 'background-color:#ccc; text-align:center; font-weight:bold;');

        foreach ($header as $content) {
            $domTableHead = $dom->createElement('th', isys_glob_htmlentities($content));
            $domTableRow->appendChild($domTableHead);
        }

        // Create the table header.
        $domTableHeader = $dom->createElement('thead');
        $domTableHeader->appendChild($domTableRow);

        // Create the table body.
        $domTableBody = $dom->createElement('tbody');

        foreach ($data as $index => $row) {
            $domTableRow = $dom->createElement('tr');
            $domTableRow->setAttribute('style', 'background-color:#' . (($index % 2) ? 'eee' : 'fff') . ';');

            foreach ($row as $key => $value) {
                if (strpos($key, '__') === 0 && substr($key, -2) === '__') {
                    continue;
                }

                // Create table data node
                $domTableData = $dom->createElement('td');

                // Create sanitized content
                $content = trim($language->get(strip_tags(preg_replace('/<script[^>]*>[^<]*<[^>]script>/  ', '', $value))));

                /**
                 * Try to convert new lines
                 *
                 * @see ID-5664
                 */
                try {
                    // Create document fragment
                    $contentDom = $dom->createDocumentFragment();

                    // Convert new lines to br`s
                    $contentDom->appendXML('<![CDATA[' . nl2br($content) . ']]>');

                    // Insert it into table data
                    $domTableData->appendChild($contentDom);
                } catch (Exception $e) {
                    // Fallback: Append string into table data
                    $domTableData->textContent = $content;
                }

                $domTableRow->appendChild($domTableData);
            }

            $domTableBody->appendChild($domTableRow);
        }

        $domTable->appendChild($domTableHeader);
        $domTable->appendChild($domTableBody);

        $dom->appendChild($domTable);

        // Write our DOM to the PDF.
        $this->writeHTML(trim($dom->saveHTML()));
    }
}
