<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */

namespace Pearly\Driver;

/**
 * Screen print driver class.
 *
 * This is a simple \Pearly\Driver\IPrintDriver implementation that simply
 * outputs the report to the web browser with the appropriate application/pdf
 * Content-type for inline display.
 */
class ScreenPrintDriver implements IPrintDriver
{
    /**
     * Invoke function.
     *
     * This function displays the report output.
     *
     * @param string $filen The path to the pdf to be displayed.
     * @param null   $conf  This parameter is unused for this driver.
     */
    public function invoke($filen, $conf)
    {
        header("Content-type: application/pdf");
        header("Content-Length: " . filesize($filen));
        header("Content-Disposition: inline; filename=document.pdf");
        readfile($filen);
    }
}
