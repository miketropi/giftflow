<?php
/**
 * Ledger shared by the fake Stripe services.
 *
 * Records every call the code under test makes, so tests can assert exact write
 * counts per environment without contacting Stripe.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Support;

use GiftFlow\Vendor\Stripe\StripeObject;
use GiftFlow\Vendor\Stripe\Exception\ApiErrorException;

/**
 * In-memory Stripe state for unit tests.
 */
class FakeStripeLedger {

	/**
	 * Stored endpoints by id.
	 *
	 * @var array<string, StripeObject>
	 */
	public $endpoints = array();

	/**
	 * Recorded calls: service, method, args.
	 *
	 * @var array<int, array{service: string, method: string, args: array}>
	 */
	public $calls = array();

	/**
	 * Next generated endpoint id.
	 *
	 * @var int
	 */
	public $next_id = 1;

	/**
	 * Account payload returned by accounts->retrieve().
	 *
	 * @var array<string, mixed>
	 */
	public $account = array(
		'id'     => 'acct_test_1',
		'object' => 'account',
	);

	/**
	 * Exception thrown by accounts->retrieve().
	 *
	 * @var ApiErrorException|null
	 */
	public $account_exception = null;

	/**
	 * Exception thrown by webhookEndpoints->create() after recording the endpoint.
	 *
	 * @var \Exception|null
	 */
	public $create_exception = null;

	/**
	 * When true, local option writes are made to fail while an endpoint is created.
	 *
	 * @var bool
	 */
	public $fail_local_writes_on_create = false;

	/**
	 * Parameters received by webhookEndpoints->update(), by endpoint id.
	 *
	 * @var array<string, array<int, array<string, mixed>>>
	 */
	public $update_params = array();

	/**
	 * Deleted endpoint ids.
	 *
	 * @var string[]
	 */
	public $deleted = array();

	/**
	 * Record a call.
	 *
	 * @param string               $service Service name.
	 * @param string               $method  Method name.
	 * @param array<string, mixed> $args    Arguments.
	 * @return void
	 */
	public function record( string $service, string $method, array $args = array() ): void {
		$this->calls[] = array(
			'service' => $service,
			'method'  => $method,
			'args'    => $args,
		);
	}

	/**
	 * Number of recorded calls for a method name across services.
	 *
	 * @param string $method Method name.
	 * @return int
	 */
	public function count_calls( string $method ): int {
		$count = 0;

		foreach ( $this->calls as $call ) {
			if ( $call['method'] === $method ) {
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Total number of state-changing calls.
	 *
	 * @return int
	 */
	public function write_calls(): int {
		return $this->count_calls( 'create' ) + $this->count_calls( 'update' ) + $this->count_calls( 'delete' );
	}

	/**
	 * Service double for a Stripe client property.
	 *
	 * @param string $name Service name.
	 * @return object|null
	 */
	public function service( string $name ) {
		switch ( $name ) {
			case 'webhookEndpoints':
				return new FakeWebhookEndpointService( $this );
			case 'accounts':
				return new FakeAccountService( $this );
			default:
				return null;
		}
	}

	/**
	 * Seed an endpoint.
	 *
	 * @param array<string, mixed> $values Endpoint values.
	 * @param string               $secret Signing secret returned on create.
	 * @return string Endpoint id.
	 */
	public function add_endpoint( array $values, string $secret = 'whsec_seeded' ): string {
		$id = isset( $values['id'] ) ? (string) $values['id'] : 'we_seed_' . $this->next_id++;

		$values['id']     = $id;
		$values['object'] = 'webhook_endpoint';
		$values['secret'] = $secret;

		$this->endpoints[ $id ] = StripeObject::constructFrom( $values, null );

		return $id;
	}

	/**
	 * Create an endpoint from received parameters.
	 *
	 * @param array<string, mixed> $params Create parameters.
	 * @return StripeObject
	 * @throws \Exception When the ledger is configured to simulate a lost response.
	 */
	public function create_endpoint( array $params ): StripeObject {
		$id = 'we_created_' . $this->next_id++;

		$values = array(
			'id'             => $id,
			'object'         => 'webhook_endpoint',
			'status'         => 'enabled',
			'url'            => isset( $params['url'] ) ? (string) $params['url'] : '',
			'enabled_events' => isset( $params['enabled_events'] ) ? (array) $params['enabled_events'] : array(),
			'metadata'       => isset( $params['metadata'] ) ? (array) $params['metadata'] : array(),
			'description'    => isset( $params['description'] ) ? (string) $params['description'] : '',
			'livemode'       => false,
			'api_version'    => '2025-09-30.test',
			'secret'         => 'whsec_created_' . $this->next_id,
		);

		if ( $this->fail_local_writes_on_create ) {
			\GiftFlow_Test_Option_Store::$fail_writes = true;
		}

		$this->endpoints[ $id ] = StripeObject::constructFrom( $values, null );

		if ( null !== $this->create_exception ) {
			throw $this->create_exception;
		}

		return $this->endpoints[ $id ];
	}

	/**
	 * Update an endpoint in place.
	 *
	 * @param string               $id     Endpoint id.
	 * @param array<string, mixed> $params Update parameters.
	 * @return StripeObject
	 */
	public function update_endpoint( string $id, array $params ): StripeObject {
		$endpoint = $this->endpoints[ $id ];
		$values   = $endpoint->toArray();

		foreach ( $params as $key => $value ) {
			$values[ $key ] = $value;
		}

		$this->endpoints[ $id ] = StripeObject::constructFrom( $values, null );

		return $this->endpoints[ $id ];
	}

	/**
	 * Reset before each test.
	 *
	 * @return void
	 */
	public function reset(): void {
		$this->endpoints                  = array();
		$this->calls                      = array();
		$this->next_id                    = 1;
		$this->account                    = array(
			'id'     => 'acct_test_1',
			'object' => 'account',
		);
		$this->account_exception          = null;
		$this->create_exception           = null;
		$this->fail_local_writes_on_create = false;
		$this->update_params              = array();
		$this->deleted                    = array();
	}
}
