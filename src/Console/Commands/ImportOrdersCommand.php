<?php

namespace Sorbing\PromUaImporter\Console\Commands;

use Illuminate\Console\Command;
use Sorbing\PromUaImporter\Models\OrderProm;

class ImportOrdersCommand extends Command
{
    protected $signature = 'prom-ua-import:orders {--xml=} {--xls=}';

    protected $description = 'Import orders from prom.ua (XML URL or XLS file)';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $xmlUrl = $this->option('xml');
        $xlsFile = $this->option('xls');

        if (strpos($xlsFile, DIRECTORY_SEPARATOR) !== 0) {
            $xlsFile = base_path($xlsFile);
        }

        $importer = app('prom_ua_importer');

        if ($xlsFile) {
            $importer->setOrdersXlsFile($xlsFile);
            $importer->importOrdersFromXls();
        }

        if ($xmlUrl) {
            $importer->setOrdersXmlUrl($xmlUrl);
        }

        $importer->importOrdersFromXml();
    }
}
