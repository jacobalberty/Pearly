<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 *
 * This file contains definitions for the RPDF class to handle pdf reporting.
 */
namespace Pearly\Report\PDF;

/**
 * Base PRDF class
 *
 * This class implements any features necessary for the reporting engine.
 */
class RPDF extends \fpdi\tfpdf
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
     * @param Array   $widths Array containing numeric values for the width of each column.
     * @param Array   $data   Multi-dimensional array containing the rows of data to be displayed in the table.
     * @param Array   $header Single header row to describe the contents of the table. It is printed at the begining of each table and immediately following each page break.
     * @param boolean $border Indicates whether or not to draw a border around each cell. Default value is true.
     *
     * @return RPDF Returns this instance of RPDF for chaining.
     */
    public function addTable($widths, $data, $header = null, $border = true)
    {
        $ptable = new PTable($this);

        $ptable->widths = $widths;
        if (!is_null($header)) {
            $ptable->header = $header;
            $this->setFont('', 'B');
            $ptable->Row($header, $border);
            $this->setFont('');
        }

        foreach ($data as $row) {
            $ptable->Row($row, $border);
        }

        return $this;
    }

    /**
     *
     * Prints a cell (rectangular area) with optional borders, background color and character string. The upper-left corner of the cell corresponds to the current position. The text can be aligned or centered. After the call, the current position moves to the right or to the next line. It is possible to put a link on the text. 
     * If automatic page breaking is enabled and the cell goes beyond the limit, a page break is done before outputting.
     *
     * @param float $w Cell width. If 0, the cell extends up to the right margin.
     * @param float $h Cell height. Default value: 0.
     * @param string $txt String to print. Default value: empty string.
     * @param mixed $border Indicates if borders must be drawn around the cell. Default value: 0.
     * @param int $ln Indicates where the current position should go after the call. Default value: 0.
     * @param string $align Allows to center or align the text.
     * @param boolean $fill Indicates if the cell background must be painted (true) or transparent (false). Default value: false.
     * @param mixed $lin kURL or identifier returned by AddLink().
     *
     * @return RPDF Returns this instance of RPDF for chaining.
     */
    public function Cell($w, $h=0, $txt = '', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
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
