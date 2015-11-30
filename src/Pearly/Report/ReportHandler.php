<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Report;

use Pearly\Core\Base;
use Pearly\Driver\IPrintDriver;

/**
 * This class handles passing reports to the appropriate print driver.
 */
class ReportHandler extends Base
{
    /** @var \Pearly\Report\IReport to process */
    private $report;
    /** @var \Pearly\Driver\IPrintDriver to send $this->report to */
    private $driver;

    /**
     * Set report function.
     *
     * This loads the proper report into the class.
     *
     * @param \Pearly\Report\IReport $report The report to use.
     */
    public function setReport(IReport $report)
    {
        $this->report = $report;
    }

    /**
     * Pick driver function.
     *
     * This function takes a simplified driver name and checks to see
     * if the appropriate driver exists within the loaded package and within
     * the Pearly namespace. If it exists it loads the driver with preferance for
     * drivers in the package namespace followed by pearly namespace. If it doesnt exist
     * throws a \Pearly\Report\ReportException.
     *
     * @throws \Pearly\Report\ReportException if $driver does not exist within either namespace.
     *
     * @param string $driver the name of the driver to use.
     */
    public function pickDriver($driver)
    {
        $pkgdriver = "\\{$this->registry->pkg}\\Driver\\{$driver}PrintDriver";
        $pearlydriver = "\\Pearly\\Driver\\{$driver}PrintDriver";
        if (class_exists($pkgdriver)) {
            $this->setDriver(new $pkgdriver());
        } elseif (class_exists($pearlydriver)) {
            $this->setDriver(new $pearlydriver());
        } else {
            throw new ReportException("Could not find $driver within either namespace");
        }
    }

    /**
     * Set driver function.
     *
     * This function simply stores the driver within the class for later use.
     *
     * @param \Pearly\Driver\IPrintDriver $driver The driver to use.
     */
    public function setdriver(IPrintDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Print Report function
     *
     * This function executes the specified report and then passes the results to the specified driver.
     *
     * @todo use dependency injection to inject a printer driver in place of $conf and eliminate
     *       setDriver/pickDrive
     *
     * @param string $conf   The print driver configuration parameters to be used.
     * @param array  $params The report parameters to be used.
     */
    public function printReport($conf, $params)
    {
        $filen = $this->report->invoke($params);

        $retval = $this->driver->invoke($filen, $conf);
        if (file_exists($filen)) {
            unlink($filen);
        }
        return $retval;
    }
}
