<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 *
 * This file contains definitions for the RPDF class to handle pdf reporting.
 */
namespace Pearly\Report\PDF;

/** This file depends on fpdf */
require '3rdparty/fpdf/fpdf.php';

/**
 * Base PRDF class
 *
 * This class implements any features necessary for the reporting engine.
 */
class RPDF extends \FPDF
{
    /** @var callback Footer callback to allow defining a footer without inheriting from this class */
    public $cFooter = null;
    /** @var callback Header callback to allow defining a footer without inheriting from this class */
    public $cHeader = null;

    /** @ignore */
    public function Footer()
    {
        if (!is_null($this->cFooter)) {
            $cFooter = $this->cFooter;
            $cFooter($this);
        }
    }

    /** @ignore */
    public function Header()
    {
        if (!is_null($this->cHeader)) {
            $cHeader = $this->cHeader;
            $cHeader($this);
        }
    }

    /**
     *
     * A simple helper function to interface with PTable and display data in a page width multi-page table.
     *
     * @param Array $widths Array containing numeric values for the width of each column.
     * @param Array $data Multi-dimensional array containing the rows of data to be displayed in the table.
     * @param Array $header Single header row to describe the contents of the table. It is printed at the begining of each table and immediately following each page break.
     * @return RPDF Returns this instance of RPDF for chaining.
     */
    public function addTable($widths, $data, $header = null)
    {
        $ptable = new PTable($this);

        $ptable->widths = $widths;
        if (!is_null($header)) {
            $ptable->header = $header;
            $this->setFont('', 'B');
            $ptable->Row($header);
            $this->setFont('');
        }

        foreach ($data as $row) {
            $ptable->Row($row);
        }

        return $this;
    }

    /**
     *
     * Defines the abscissa and ordinate of the current position. If the passed values are negative, they are relative respectively to the right and bottom of the page.
     *
     * @param float $x The value of the abscissa.
     * @param float $y The value of the ordinate.
     * @return RPDF Returns this instance of RPDF for chaining.
     */
    public function SetXY($x, $y)
    {
        parent::SetXY($x, $y);

        return $this;
    }

    /**
     *
     * Sets the font used to print character strings.
     *
     * @param string $family Family font. It can be either a name defined by AddFont() or one of the standard families (case insensitive).
     * @param string $style Font style.
     * @param $size Font size in points.
     * @return RPDF Returns this instance of RPDF for chaining
     */
    public function SetFont($family, $style = '', $size = 0)
    {
        parent::SetFont($family, $style, $size);
        return $this;
    }

    /**
     *
     * This method prints text from the current position. When the right margin is reached (or the \n character is met) a line break occurs and text continues from the left margin. Upon method exit, the current position is left just at the end of the text. 
     *
     * @param float $h Line height.
     * @param string $txt String to print.
     * @param string $link URL or identifier returned by AddLink() 
     * @return RPDF Returns this instance of RPDF for chaining
     */
    public function Write($h, $txt, $link = '')
    {
        parent::Write($h, $txt, $link);

        return $this;
    }

    /**
     *
     * Defines the abscissa of the current position. If the passed value is negative, it is relative to the right of the page. 
     *
     * @param float $x The value of the abscissa.
     * @return RPDF Returns this instance of RPDF for chaining
     */
    public function SetX($x)
    {
        parent::SetX($x);

        return $this;
    }

    /**
     *
     * Moves the current abscissa back to the left margin and sets the ordinate. If the passed value is negative, it is relative to the bottom of the page. 
     *
     * @param float $y The value of the ordinate.
     * @return RPDF Returns this instance of RPDF for chaining
     */
    public function SetY($y)
    {
        parent::SetY($y);

        return $this;
    }

    /**
     * Set Fill Color function.
     *
     * Defines the color used for all filling operations (filled rectangles and cell backgrounds). It can be expressed in RGB components or gray scale. The method can be called before the first page is created and the value is retained from page to page. 
     *
     * @param int $r
     * @param int $g
     * @param int $b
     *
     * @return RPDF Returns this instance of RPDF for chaining.
     */
    public function SetFillColor($r, $g = null, $b = null)
    {
        parent::SetFillColor($r, $g, $b);

        return $this;
    }

    /**
     * A quick function to save the pdf to a temporary file.
     *
     * @return string Returns the path to a temporary file containing the output pdf.
     */
    public function ToFile()
    {
        $filen = tempnam(sys_get_temp_dir(), 'RPDF');
        $this->output($filen, 'F');

        return $filen;
    }
}
