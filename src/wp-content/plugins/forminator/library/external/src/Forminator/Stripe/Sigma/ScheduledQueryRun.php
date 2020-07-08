<?php

namespace Forminator\Stripe\Sigma;

/**
 * Class ScheduledQueryRun
 *
 * @property string $id
 * @property string $object
 * @property int $created
 * @property int $data_load_time
 * @property \Forminator\Stripe\StripeObject $error
 * @property \Forminator\Stripe\File|null $file
 * @property bool $livemode
 * @property int $result_available_until
 * @property string $sql
 * @property string $status
 * @property string $title
 *
 * @package Forminator\Stripe\Sigma
 */
class ScheduledQueryRun extends \Forminator\Stripe\ApiResource
{
    const OBJECT_NAME = 'scheduled_query_run';

    use \Forminator\Stripe\ApiOperations\All;
    use \Forminator\Stripe\ApiOperations\Retrieve;

    public static function classUrl()
    {
        return "/v1/sigma/scheduled_query_runs";
    }
}
