<?php

namespace Forminator\Stripe\Reporting;

/**
 * Class ReportRun
 *
 * @property string $id
 * @property string $object
 * @property int $created
 * @property string $error
 * @property bool $livemode
 * @property mixed $parameters
 * @property string $report_type
 * @property mixed $result
 * @property string $status
 * @property int $succeeded_at
 *
 * @package Forminator\Stripe\Reporting
 */
class ReportRun extends \Forminator\Stripe\ApiResource
{
    const OBJECT_NAME = "reporting.report_run";

    use \Forminator\Stripe\ApiOperations\All;
    use \Forminator\Stripe\ApiOperations\Create;
    use \Forminator\Stripe\ApiOperations\Retrieve;
}
