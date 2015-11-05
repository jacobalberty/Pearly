<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */

namespace Pearly\Driver;

/**
 * Cups print driver class.
 *
 * This class passes the output of a report to cups using the specified options to
 * allow server side printing of reports.
 */
class CupsPrintDriver implements IPrintDriver
{
    /**
     * Cups printer driver invoke function.
     *
     * @param string $filen path to temporary pdf file to print.
     * @param string $conf string containing options to pass to lp.
     *
     * @return boolean true to redirect to where the report specified.
     */
    public function invoke($filen, $conf)
    {
        $sfilen = escapeshellarg($filen);
        $sconf  = escapeshellcmd($conf);
        exec("/usr/bin/lp {$sconf} -- {$sfilen}");
        return true;
    }
}
