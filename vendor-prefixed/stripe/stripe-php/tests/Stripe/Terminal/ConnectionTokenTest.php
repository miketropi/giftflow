<?php

namespace GiftFlow\Vendor\Stripe\Terminal;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Terminal\ConnectionToken
 */
final class ConnectionTokenTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    public function testIsCreatable()
    {
        $this->expectsRequest('post', '/v1/terminal/connection_tokens');
        $resource = ConnectionToken::create();
        self::assertInstanceOf(ConnectionToken::class, $resource);
    }
}