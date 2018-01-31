<?php

namespace Sorbing\PromUaImporter\Console\Commands;

use Illuminate\Console\Command;

class ImportProductsCommand extends Command
{
    protected $signature = 'prom-ua-import:products {--yml=} {--csv=}';

    protected $description = 'Import all products from prom.ua (Yml URL or/and CSV file)';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $ymlUrl = $this->option('yml');
        $csvFile = $this->option('csv');

        if ($csvFile && strpos($csvFile, DIRECTORY_SEPARATOR) !== 0) {
            $csvFile = base_path($csvFile);
        }

        $importer = app('prom_ua_importer');

        if ($csvFile) {
            $importer->setProductsCsvFile($csvFile);
            $importer->importProductsFromCsv();
        }

        if ($ymlUrl) {
            $importer->setProductsYmlUrl($ymlUrl);
        }

        $importer->importProductsFromYml();
    }
}
