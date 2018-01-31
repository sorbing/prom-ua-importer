<?php

namespace Sorbing\PromUaImporter;

class PromUaImporterFacade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'prom_ua_importer';
    }
}