<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Driver;

/**
 * This defines an interface for handlers to use for outputing pdf reports, for ex to the screen or to a cups printer. 
 */
interface IPrintDriver
{
    /**
     * Driver invoke function.
     *
     * This function is called when the report is ready to be printed.
     *
     * @param string $filen path to pdf to be printed.
     * @param mixed $conf whatever configuration data is needed to be passed to indicate where the document goes.
     *
     * @return boolean false if no redirect (such as for displaying to the screen) or true to redirect automatically.
     */
    public function invoke($filen, $conf);
}
