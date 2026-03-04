<?php

namespace GiftFlow\Vendor\Stripe;

/**
 * Interface for a Stripe client.
 */
interface StripeStreamingClientInterface extends BaseStripeClientInterface
{
    public function requestStream($method, $path, $readBodyChunkCallable, $params, $opts);
}
