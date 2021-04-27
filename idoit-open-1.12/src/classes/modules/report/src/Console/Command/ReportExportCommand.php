<?php

namespace idoit\Module\Report\Console\Command;

use idoit\Console\Command\AbstractCommand;
use idoit\Module\Report\Export\CsvExport;
use idoit\Module\Report\Export\TxtExport;
use idoit\Module\Report\Report;
use isys_component_dao;
use isys_exception_filesystem;
use isys_module_report_pro;
use isys_report_pdf;
use isys_report_xml;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class ReportExportCommand extends AbstractCommand
{
    const NAME = 'report-export';

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var isys_module_report_pro
     */
    private $reportDao;

    /**
     * Get name for command
     *
     * @return string
     */
    public function getCommandName()
    {
        return self::NAME;
    }

    /**
     * Get description for command
     *
     * @return string
     */
    public function getCommandDescription()
    {
        return 'Executes an i-doit report and saves it to a file as CSV, TXT, PDF or XML';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();

        $definition->addOption(new InputOption('reportId', 'r', InputOption::VALUE_REQUIRED, 'ID of the report'));

        $definition->addOption(new InputOption('exportPath', 'd', InputOption::VALUE_REQUIRED, 'Path to export the report into'));

        $definition->addOption(new InputOption('exportFilename', 'f', InputOption::VALUE_REQUIRED,
            "File name of export file, without extension (e.g. .pdf).\nDefault is the title of the report"));

        $definition->addOption(new InputOption('exportFileType', 't', InputOption::VALUE_REQUIRED, 'File Type of the export. Possible options: csv, txt, pdf, xml', 'csv'));

        return $definition;
    }

    /**
     * Checks if a command can have a config file via --config
     *
     * @return bool
     */
    public function isConfigurable()
    {
        return true;
    }

    /**
     * Returns an array of command usages
     *
     * @return string[]
     */
    public function getCommandUsages()
    {
        return [];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        if (!$input->getOption('reportId') || !$input->getOption('exportPath')) {
            throw new MissingOptionsException('Either the reportId or the exportPath are missing!');
        }

        if (!is_writable($input->getOption('exportPath'))) {
            throw new isys_exception_filesystem('The directory is not writable!');
        }

        if (defined("C__ENABLE__LICENCE") && C__ENABLE__LICENCE) {
            $this->reportDao = new isys_module_report_pro();

            $this->createExport($input->getOption('reportId'), $input->getOption('exportPath'), $input->getOption('exportFilename'), $input->getOption('exportFileType'));
        } else {
            $this->output->writeln('<error>I-doit is not licensed for using this Command</error>');
        }
    }

    /**
     * Creates the report export
     *
     * @param $reportId
     * @param $exportPath
     * @param $exportFilename
     * @param $exportType
     *
     * @throws isys_exception_filesystem
     */
    private function createExport($reportId, $exportPath, $exportFilename, $exportType)
    {
        $this->output->writeln('Creating Export.');

        $reportData = $this->reportDao->get_dao()
            ->get_report($reportId);

        $collectedReportData = [
            "report_id"   => $reportData["isys_report__id"],
            "type"        => $reportData["isys_report__type"],
            "title"       => $reportData["isys_report__title"],
            "description" => $reportData["isys_report__description"],
            "query"       => $reportData["isys_report__query"],
            "mandator"    => $reportData["isys_report__mandator"],
            "datetime"    => $reportData["isys_report__datetime"],
            "last_edited" => $reportData["isys_report__last_edited"]
        ];

        if (!$exportFilename) {
            $exportFilename = $reportData["isys_report__title"];
        }

        $report = new Report(new isys_component_dao($this->container->database), $reportData["isys_report__query"], $reportData["isys_report__title"],
            $reportData["isys_report__id"], $reportData["isys_report__type"]);

        switch ($exportType) {
            case 'pdf':
                $report = new isys_report_pdf($collectedReportData);
                break;
            case 'xml':
                $report = new isys_report_xml($collectedReportData);
                break;
            case 'txt':
                TxtExport::factory($report)
                    ->export()
                    ->write($exportPath . DS . $exportFilename . '.' . $exportType);

                $this->output->writeln('Wrote ' . $exportPath . DS . $exportFilename . '.' . $exportType);

                return;
            case 'csv':
            default:
                CsvExport::factory($report)
                    ->export()
                    ->write($exportPath . DS . $exportFilename . '.' . $exportType);

                $this->output->writeln('Wrote ' . $exportPath . DS . $exportFilename . '.' . $exportType);

                return;
        }

        if (isset($report)) {
            $report::$m_as_download = false;

            $report->setTitle($exportFilename);

            $report->export();

            if ($exportType === 'pdf') {
                $report->get_export_output()
                    ->Output($exportPath . DS . $exportFilename . ".pdf", "F");
            } else {
                $fileHandler = fopen($exportPath . DS . $exportFilename . '.' . $exportType, 'w+');

                fwrite($fileHandler, $report->get_export_output());
                fclose($fileHandler);
            }

            $this->output->writeln('Wrote ' . $exportPath . DS . $exportFilename . '.' . $exportType);
        }
    }
}
