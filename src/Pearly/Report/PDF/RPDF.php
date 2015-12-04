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
    public function footer()
    {
        if (!is_null($this->cFooter)) {
            $cFooter = $this->cFooter;
            $cFooter($this);
        }
    }

    /** @ignore */
    public function header()
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
     * @param Array   $header
     *  Single header row to describe the contents of the table.
     *  It is printed at the begining of each table and immediately following each page break.
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
     * Prints a cell (rectangular area) with optional borders, background color and character string.
     * The upper-left corner of the cell corresponds to the current position. The text can be aligned or centered.
     * After the call, the current position moves to the right or to the next line.
     * It is possible to put a link on the text.
     * If automatic page breaking is enabled and the cell goes beyond the limit, a page break is done before outputting.
     *
     * @param float $w Cell width. If 0, the cell extends up to the right margin.
     * @param float $h Cell height. Default value: 0.
     * @param string $txt String to print. Default value: empty string.
     * @param mixed $border Indicates if borders must be drawn around the cell. Default value: 0.
     * @param int $ln Indicates where the current position should go after the call. Default value: 0.
     * @param string $align Allows to center or align the text.
     * @param boolean $fill
     *  Indicates if the cell background must be painted (true) or transparent (false). Default value: false.
     * @param mixed $lin kURL or identifier returned by AddLink().
     *
     * @return RPDF Returns this instance of RPDF for chaining.
     */
    public function cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '')
    {
        parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
        return $this;
    }

    /**
     *
     * Defines the abscissa and ordinate of the current position. If the passed values are negative,
     * they are relative respectively to the right and bottom of the page.
     *
     * @param float $x The value of the abscissa.
     * @param float $y The value of the ordinate.
     * @return RPDF Returns this instance of RPDF for chaining.
     */
    public function setXY($x, $y)
    {
        parent::SetXY($x, $y);

        return $this;
    }

    /**
     *
     * Sets the font used to print character strings.
     *
     * @param string $family Family font.
     *  It can be either a name defined by AddFont() or one of the standard families (case insensitive).
     * @param string $style Font style.
     * @param $size Font size in points.
     * @return RPDF Returns this instance of RPDF for chaining
     */
    public function setFont($family, $style = '', $size = 0)
    {
        parent::SetFont($family, $style, $size);
        return $this;
    }

    /**
     *
     * This method prints text from the current position. When the right margin is reached
     * (or the \n character is met) a line break occurs and text continues from the left margin.
     * Upon method exit, the current position is left just at the end of the text.
     *
     * @param float $h Line height.
     * @param string $txt String to print.
     * @param string $link URL or identifier returned by AddLink()
     * @return RPDF Returns this instance of RPDF for chaining
     */
    public function write($h, $txt, $link = '')
    {
        parent::Write($h, $txt, $link);

        return $this;
    }

    /**
     *
     * Defines the abscissa of the current position.
     * If the passed value is negative, it is relative to the right of the page.
     *
     * @param float $x The value of the abscissa.
     * @return RPDF Returns this instance of RPDF for chaining
     */
    public function setX($x)
    {
        parent::SetX($x);

        return $this;
    }

    /**
     *
     * Moves the current abscissa back to the left margin and sets the ordinate.
     * If the passed value is negative, it is relative to the bottom of the page.
     *
     * @param float $y The value of the ordinate.
     * @return RPDF Returns this instance of RPDF for chaining
     */
    public function setY($y)
    {
        parent::SetY($y);

        return $this;
    }

    /**
     * Set Fill Color function.
     *
     * Defines the color used for all filling operations (filled rectangles and cell backgrounds).
     * It can be expressed in RGB components or gray scale. The method can be called before the first page
     * is created and the value is retained from page to page.
     *
     * @param int $r
     * @param int $g
     * @param int $b
     *
     * @return RPDF Returns this instance of RPDF for chaining.
     */
    public function setFillColor($r, $g = null, $b = null)
    {
        parent::SetFillColor($r, $g, $b);

        return $this;
    }

    /**
     * A quick function to save the pdf to a temporary file.
     *
     * @return string Returns the path to a temporary file containing the output pdf.
     */
    public function toFile()
    {
        $filen = tempnam(sys_get_temp_dir(), 'RPDF');
        $this->output($filen, 'F');

        return $filen;
    }

    /**
     * Computes the number of lines a MultiCell of width w will take
     *
     * @param float  $w   The width of the cell.
     * @param string $txt The text to fille the cell with.
     *
     * @return int The number of lines.
     */
    public function nbLines($w, $txt)
    {
        $unicode = $this->unifontSubset;
        //Computes the number of lines a MultiCell of width w will take
        $cw=&$this->CurrentFont['cw'];
        if ($w==0) {
            $w=$this->w-$this->rMargin-$this->x;
        }
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r", '', $txt);
        $nb=$unicode ? mb_strlen($s) : strlen($s);
        if ($nb>0 and $s[$nb-1]=="\n") {
            $nb--;
        }
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while ($i<$nb) {
            $c = $unicode ? mb_substr($s, $i, 1) : $s[$i];
            if ($c=="\n") {
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if ($c==' ') {
                $sep=$i;
            }
            if ($unicode) {
                $uniar = $this->UTF8StringToArray($c);
                $c = $uniar[0];
                if (isset($cw[$c])) {
                    $l += (ord($cw[2 * $c]) << 8) + ord($cw[2 * $c + 1]);
                } elseif ($c > 0 && $c < 128 && isset($cw[chr($c)])) {
                    $l += $cw[chr($c)];
                } elseif (isset($this->CurrentFont['desc']['MissingWidth'])) {
                    $l += $this->CurrentFont['desc']['MissingWidth'];
                } elseif (isset($this->CurrentFont['MissingWidth'])) {
                    $l += $this->CurrentFont['MissingWidth'];
                } else {
                    $l += 500;
                }
            } else {
                $l+=$cw[$c];
            }
            if ($l>$wmax) {
                if ($sep==-1) {
                    if ($i==$j) {
                        $i++;
                    }
                } else {
                    $i=$sep+1;
                }
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }
    function LineGraph($w, $h, $data, $options='', $colors=null, $maxVal=0, $nbDiv=4){
        /*******************************************
        Explain the variables:
        $w = the width of the diagram
        $h = the height of the diagram
        $data = the data for the diagram in the form of a multidimensional array
        $options = the possible formatting options which include:
            'V' = Print Vertical Divider lines
            'H' = Print Horizontal Divider Lines
            'kB' = Print bounding box around the Key (legend)
            'vB' = Print bounding box around the values under the graph
            'gB' = Print bounding box around the graph
            'dB' = Print bounding box around the entire diagram
        $colors = A multidimensional array containing RGB values
        $maxVal = The Maximum Value for the graph vertically
        $nbDiv = The number of vertical Divisions
        *******************************************/
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(0.2);
        $keys = array_keys($data);
        $ordinateWidth = 10;
        $w -= $ordinateWidth;
        $valX = $this->getX()+$ordinateWidth;
        $valY = $this->getY();
        $margin = 1;
        $titleH = 8;
        $titleW = $w;
        $lineh = 5;
        $keyH = count($data)*$lineh;
        $keyW = $w/5;
        $graphValH = 5;
        $graphValW = $w-$keyW-3*$margin;
        $graphH = $h-(3*$margin)-$graphValH;
        $graphW = $w-(2*$margin)-($keyW+$margin);
        $graphX = $valX+$margin;
        $graphY = $valY+$margin;
        $graphValX = $valX+$margin;
        $graphValY = $valY+2*$margin+$graphH;
        $keyX = $valX+(2*$margin)+$graphW;
        $keyY = $valY+$margin+.5*($h-(2*$margin))-.5*($keyH);
        //draw graph frame border
        if(strstr($options,'gB')){
            $this->Rect($valX,$valY,$w,$h);
        }
        //draw graph diagram border
        if(strstr($options,'dB')){
            $this->Rect($valX+$margin,$valY+$margin,$graphW,$graphH);
        }
        //draw key legend border
        if(strstr($options,'kB')){
            $this->Rect($keyX,$keyY,$keyW,$keyH);
        }
        //draw graph value box
        if(strstr($options,'vB')){
            $this->Rect($graphValX,$graphValY,$graphValW,$graphValH);
        }
        //define colors
        if($colors===null){
            $safeColors = array(0,51,102,153,204,225);
            for($i=0;$i<count($data);$i++){
                $colors[$keys[$i]] = array($safeColors[array_rand($safeColors)],$safeColors[array_rand($safeColors)],$safeColors[array_rand($safeColors)]);
            }
        }
        //form an array with all data values from the multi-demensional $data array
        $ValArray = array();
        foreach($data as $key => $value){
            foreach($data[$key] as $val){
                $ValArray[]=$val;                    
            }
        }
        //define max value
        if($maxVal<ceil(max($ValArray))){
            $maxVal = ceil(max($ValArray));
        }
        //draw horizontal lines
        $vertDivH = $graphH/$nbDiv;
        if(strstr($options,'H')){
            for($i=0;$i<=$nbDiv;$i++){
                if($i<$nbDiv){
                    $this->Line($graphX,$graphY+$i*$vertDivH,$graphX+$graphW,$graphY+$i*$vertDivH);
                } else{
                    $this->Line($graphX,$graphY+$graphH,$graphX+$graphW,$graphY+$graphH);
                }
            }
        }
        //draw vertical lines
        $horiDivW = floor($graphW/(count($data[$keys[0]])-1));
        if(strstr($options,'V')){
            for($i=0;$i<=(count($data[$keys[0]])-1);$i++){
                if($i<(count($data[$keys[0]])-1)){
                    $this->Line($graphX+$i*$horiDivW,$graphY,$graphX+$i*$horiDivW,$graphY+$graphH);
                } else {
                    $this->Line($graphX+$graphW,$graphY,$graphX+$graphW,$graphY+$graphH);
                }
            }
        }
        //draw graph lines
        foreach($data as $key => $value){
            $this->setDrawColor($colors[$key][0],$colors[$key][1],$colors[$key][2]);
            $this->SetLineWidth(0.8);
            $valueKeys = array_keys($value);
            for($i=0;$i<count($value);$i++){
                if($i==count($value)-2){
                    $this->Line(
                        $graphX+($i*$horiDivW),
                        $graphY+$graphH-($value[$valueKeys[$i]]/$maxVal*$graphH),
                        $graphX+$graphW,
                        $graphY+$graphH-($value[$valueKeys[$i+1]]/$maxVal*$graphH)
                    );
                } else if($i<(count($value)-1)) {
                    $this->Line(
                        $graphX+($i*$horiDivW),
                        $graphY+$graphH-($value[$valueKeys[$i]]/$maxVal*$graphH),
                        $graphX+($i+1)*$horiDivW,
                        $graphY+$graphH-($value[$valueKeys[$i+1]]/$maxVal*$graphH)
                    );
                }
            }
            //Set the Key (legend)
            $this->SetFont('Courier','',10);
            if(!isset($n))$n=0;
            $this->Line($keyX+1,$keyY+$lineh/2+$n*$lineh,$keyX+8,$keyY+$lineh/2+$n*$lineh);
            $this->SetXY($keyX+8,$keyY+$n*$lineh);
            $this->Cell($keyW,$lineh,$key,0,1,'L');
            $n++;
        }
        //print the abscissa values
        foreach($valueKeys as $key => $value){
            if($key==0){
                $this->SetXY($graphValX,$graphValY);
                $this->Cell(30,$lineh,$value,0,0,'L');
            } else if($key==count($valueKeys)-1){
                $this->SetXY($graphValX+$graphValW-30,$graphValY);
                $this->Cell(30,$lineh,$value,0,0,'R');
            } else {
                $this->SetXY($graphValX+$key*$horiDivW-15,$graphValY);
                $this->Cell(30,$lineh,$value,0,0,'C');
            }
        }
        //print the ordinate values
        for($i=0;$i<=$nbDiv;$i++){
            $this->SetXY($graphValX-10,$graphY+($nbDiv-$i)*$vertDivH-3);
            $this->Cell(8,6,sprintf('%.1f',$maxVal/$nbDiv*$i),0,0,'R');
        }
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(0.2);
    }
}
