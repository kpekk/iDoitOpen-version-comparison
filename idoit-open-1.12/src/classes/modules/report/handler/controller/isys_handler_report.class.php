<?php

use idoit\Console\IdoitConsoleApplication;
use idoit\Module\Report\Console\Command\ReportExportCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * i-doit
 *
 * Handler for exporting reports for the specified file type.
 *
 * @package     i-doit
 * @subpackage  Handler
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_handler_report extends isys_handler
{
    /**
     * @var string directory where to put the export
     */
    private $m_directory = null;

    /**
     * @var string export type
     */
    private $m_export_type = 'csv';

    /**
     * @var string file name of the export
     */
    private $m_file_name = null;

    /**
     * @var int report id
     */
    private $m_report_id = null;

    /**
     * Output of the usage of this controller
     *
     * @param bool $p_error
     */
    public function usage($p_error = true)
    {
        $l_error = ($p_error ? "Wrong usage!" : "How to use this controller:") . PHP_EOL . PHP_EOL . "Example:" . PHP_EOL .
            "./controller -u USERNAME -p PASSWORD -i TENANT_ID -m report -r REPORT_ID -d ABSOLUTE/PATH/FOR/EXPORT -f FILE_NAME -t csv" . PHP_EOL . PHP_EOL . "Parameters:" .
            PHP_EOL . "-r:   ID of the report." . PHP_EOL . "-d:   Path to export the report into." . PHP_EOL .
            "-f:   Optional parameter for the file name. Default is the title of the report." . PHP_EOL .
            "-t:   Optional parameter for the file type. Possible options are: csv, txt, pdf, xml. Default: csv" . PHP_EOL .
            "Example: /var/www/controller -u admin -p admin -i 1 -m report -r 1 -d /var/www/exports/ -f export_file -t csv";

        error($l_error);
    }

    public function init()
    {
        global $g_comp_session;

        $this->process();

        $application = new IdoitConsoleApplication();
        $application->setAutoExit(false);

        $output = new ConsoleOutput();

        $output->writeln('<error>isys_handler_report is deprecated, please use php console.php report-export instead</error>');

        $commandParams = [
            'command'      => 'report-export',
            '--user'       => 'loginBefore',
            '--password'   => 'loginBefore',
            '--tenantId'   => 'loginBefore',
            '--reportId'   => $this->m_report_id,
            '--exportPath' => $this->m_directory
        ];

        if ($this->m_file_name) {
            $commandParams['--exportFilename'] = $this->m_file_name;
        }

        if ($this->m_export_type) {
            $commandParams['--exportFileType'] = $this->m_export_type;
        }

        /**
         * @var $command \idoit\Console\Command\AbstractCommand
         */
        $command = new ReportExportCommand();
        $command->setSession($g_comp_session);
        $command->setContainer(\isys_application::instance()->container);
        $command->setAuth(\isys_auth_system::instance());

        $application->add($command);

        $application->run(new ArrayInput($commandParams), $output);

        return true;
    }

    /**
     * Setting up the environment for this controller
     */
    private function process()
    {
        global $argv;

        if (is_array($argv)) {
            $l_pos_dir = array_search('-h', $argv);

            if ($l_pos_dir !== false) {
                $this->usage(false);
            }

            $l_pos_dir = array_search('-r', $argv);

            if ($l_pos_dir !== false) {
                $this->m_report_id = $argv[(int)$l_pos_dir + 1];
            } else {
                $this->usage();
            }

            $l_pos_dir = array_search('-d', $argv);

            if ($l_pos_dir !== false) {
                $this->m_directory = $argv[(int)$l_pos_dir + 1];

                if (!is_dir($this->m_directory)) {
                    verbose('Directory "' . $this->m_directory . '" does not exist.');
                    die;
                }
            } else {
                $this->usage();
            }

            $l_pos_dir = array_search('-f', $argv);

            if ($l_pos_dir !== false) {
                $this->m_file_name = $argv[(int)$l_pos_dir + 1];
            }

            $l_pos_dir = array_search('-t', $argv);

            if ($l_pos_dir !== false) {
                $this->m_export_type = $argv[(int)$l_pos_dir + 1];
            }
        } else {
            $this->usage();
        }
    }
}
