<?php
/**
 * Stripe client double backed by a ledger.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Support;

use GiftFlow\Vendor\Stripe\StripeClient;

/**
 * Stripe client that never performs network calls.
 */
class FakeStripeClient extends StripeClient {

	/**
	 * Shared ledger.
	 *
	 * @var FakeStripeLedger
	 */
	public $ledger;

	/**
	 * Environment this client was created for.
	 *
	 * @var string
	 */
	public $environment;

	/**
	 * Constructor.
	 *
	 * @param FakeStripeLedger $ledger      Ledger.
	 * @param string           $environment Environment id.
	 * @param string           $api_key     API key used for the client.
	 */
	public function __construct( FakeStripeLedger $ledger, string $environment, string $api_key ) {
		parent::__construct( $api_key );

		$this->ledger      = $ledger;
		$this->environment = $environment;
	}

	/**
	 * Resolve a service double.
	 *
	 * @param string $name Service name.
	 * @return mixed
	 */
	public function __get( $name ) {
		$service = $this->ledger->service( $name );

		if ( null === $service ) {
			return parent::__get( $name );
		}

		return $service;
	}
}
