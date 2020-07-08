<?php

namespace Forminator\Stripe\Reporting;

/**
 * Class ReportType
 *
 * @property string $id
 * @property string $object
 * @property int $data_available_end
 * @property int $data_available_start
 * @property string[]|null $default_columns
 * @property string $name
 * @property int $updated
 * @property int $version
 *
 * @package Forminator\Stripe\Reporting
 */
class ReportType extends \Forminator\Stripe\ApiResource
{
    const OBJECT_NAME = 'reporting.report_type';

    use \Forminator\Stripe\ApiOperations\All;
    use \Forminator\Stripe\ApiOperations\Retrieve;
}
