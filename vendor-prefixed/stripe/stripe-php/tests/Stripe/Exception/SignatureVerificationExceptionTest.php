<?php

namespace GiftFlow\Vendor\Stripe\Exception;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Exception\SignatureVerificationException
 */
final class SignatureVerificationExceptionTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    public function testGetters()
    {
        $e = SignatureVerificationException::factory('message', 'payload', 'sig_header');
        self::assertSame('message', $e->getMessage());
        self::assertSame('payload', $e->getHttpBody());
        self::assertSame('sig_header', $e->getSigHeader());
    }
}