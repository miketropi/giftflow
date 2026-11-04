<?php
/**
 * Fake Stripe account service.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Support;

use GiftFlow\Vendor\Stripe\StripeObject;

/**
 * Account service double returning local fixtures only.
 */
class FakeAccountService {

	/**
	 * Ledger.
	 *
	 * @var FakeStripeLedger
	 */
	private $ledger;

	/**
	 * Constructor.
	 *
	 * @param FakeStripeLedger $ledger Ledger.
	 */
	public function __construct( FakeStripeLedger $ledger ) {
		$this->ledger = $ledger;
	}

	/**
	 * Retrieve the authenticated account.
	 *
	 * @param string|null               $id     Account id.
	 * @param array<string, mixed>|null $params Retrieve parameters.
	 * @param mixed                     $opts   Request options.
	 * @return StripeObject
	 * @throws \Exception When the fake ledger is configured to fail.
	 */
	public function retrieve( $id = null, $params = null, $opts = null ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Signature mirrors the Stripe SDK service.
		$this->ledger->record( 'accounts', 'retrieve', array( 'id' => $id ) );

		if ( null !== $this->ledger->account_exception ) {
			throw $this->ledger->account_exception;
		}

		return StripeObject::constructFrom( $this->ledger->account, null );
	}
}
