<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */

/**
 * This file contains definitions for the PTable class.
 */
namespace Pearly\Report\PDF;

/**
 * Multi page table.
 *
 * Based on code from the fpdf.org website by Olivier.
 *
 * @license http://fpdf.org FPDF
 * @link    http://fpdf.org/en/script/script3.php Table with MultiCells
 */
class PTable
{
    /** @var Array An array containing width of the rows */
    public $widths;
    /** @ignore */
    public $aligns;
    /** @var \Pearly\Report\PDF\RPDF The RPDF to draw the table to */
    private $pdf;
    /** @var Array An array containing the column titles */
    public $header;

    /**
     * The constructor.
     *
     * @param \Pearly\Report\PDF\RPDF $pdf the RPDF to draw to.
     */
    public function __construct($pdf)
    {
        $this->pdf = $pdf;
    }

    /**
     * Draw a new row in the table.
     *
     * @param Array $data array containing the row to draw.
     */
    public function Row($data, $border = true)
    {
        //Calculate the height of the row
        $nb=0;
        for ($i=0; $i<count($data); $i++) {
            $nb=max($nb, $this->pdf->NbLines($this->widths[$i], $data[$i]));
        }
        $h=5*$nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for ($i=0; $i<count($data); $i++) {
            $w=$this->widths[$i];
            $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            //Save the current position
            $x=$this->pdf->GetX();
            $y=$this->pdf->GetY();
            if ($border) {
                //Draw the border
                $this->pdf->Rect($x, $y, $w, $h);
            }
            //Print the text
            $this->pdf->MultiCell($w, 5, $data[$i], 0, $a);
            //Put the position to the right of the cell
            $this->pdf->SetXY($x+$w, $y);
        }
        //Go to the next line
        $this->pdf->Ln($h);
    }

    /**
     * This function checks if we need a page break.
     *
     * If the height h would cause an overflow,
     * add a new page immediately and draw a bolded header.
     *
     * @param float $h height of the row to be drawn.
     */
    private function CheckPageBreak($h)
    {
        if ($this->pdf->GetY()+$h>$this->pdf->PageBreakTrigger) {
            $this->pdf->AddPage($this->pdf->CurOrientation);
            if (!is_null($this->header)) {
                $this->pdf->setFont('', 'B');
                $this->Row($this->header);
                $this->pdf->setFont('');
            }
        }
    }
}
