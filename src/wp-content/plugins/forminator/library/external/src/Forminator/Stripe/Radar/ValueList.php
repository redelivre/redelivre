<?php

namespace Forminator\Stripe\Radar;

/**
 * Class ValueList
 *
 * @property string $id
 * @property string $object
 * @property string $alias
 * @property int $created
 * @property string $created_by
 * @property string $item_type
 * @property \Forminator\Stripe\Collection $list_items
 * @property bool $livemode
 * @property \Forminator\Stripe\StripeObject $metadata
 * @property string $name
 *
 * @package Forminator\Stripe\Radar
 */
class ValueList extends \Forminator\Stripe\ApiResource
{
    const OBJECT_NAME = 'radar.value_list';

    use \Forminator\Stripe\ApiOperations\All;
    use \Forminator\Stripe\ApiOperations\Create;
    use \Forminator\Stripe\ApiOperations\Delete;
    use \Forminator\Stripe\ApiOperations\Retrieve;
    use \Forminator\Stripe\ApiOperations\Update;
}
