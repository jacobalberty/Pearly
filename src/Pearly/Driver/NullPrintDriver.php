<?php
/**
 * Pearly 1.0
 *
 * @author    Jacob Alberty <jacob.alberty@gmail.com>
 */

namespace Pearly\Driver;

/**
 * Null print driver class.
 *
 * This class simply discargds any report data and does nothing.
 */
class NullPrintDriver implements IPrintDriver
{
    /**
     * Null printer driver invoke function.
     *
     * @param string $filen path to temporary pdf file to do nothing with.
     * @param string $conf string to do nothing with.
     *
     * @return boolean true to redirect to where the report specified.
     */
    public function invoke($filen, $conf)
    {
        return true;
    }
}
