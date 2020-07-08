<?php

namespace Forminator\Stripe;

/**
 * Class SKU
 *
 * @property string $id
 * @property string $object
 * @property bool $active
 * @property \Forminator\Stripe\StripeObject $attributes
 * @property int $created
 * @property string $currency
 * @property string|null $image
 * @property \Forminator\Stripe\StripeObject $inventory
 * @property bool $livemode
 * @property \Forminator\Stripe\StripeObject $metadata
 * @property \Forminator\Stripe\StripeObject|null $package_dimensions
 * @property int $price
 * @property string $product
 * @property int $updated
 *
 * @package Stripe
 */
class SKU extends ApiResource
{
    const OBJECT_NAME = 'sku';

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
}
