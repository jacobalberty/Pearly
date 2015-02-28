<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Report;

use Pearly\Core\IBase;

/**
 * This interface is to be used by reports that output pdf files for printing.
 * Most reports would use \Pearly\Core\RPDF to handle pdf generation.
 */
interface IReport extends IBase
{
    /**
     * Invoke function.
     *
     * This function takes an array of parameters as input, typically you would
     * pass either $_GET or $_POST directly however you can build your own array
     * in the calling class if needed.
     *
     * @param array $params Array containing parameters to execute the report.
     *
     * @return string Path to a temporary file containing the report output in pdf format.
     */
    public function invoke(array $params);
}
