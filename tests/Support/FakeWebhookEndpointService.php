<?php
/**
 * Fake Stripe webhook endpoint service.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Support;

use GiftFlow\Vendor\Stripe\StripeObject;
use GiftFlow\Vendor\Stripe\Exception\InvalidRequestException;

/**
 * Webhook endpoint service double with pagination support.
 */
class FakeWebhookEndpointService {

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
	 * List endpoints with limit/starting_after pagination.
	 *
	 * @param array<string, mixed>|null $params List parameters.
	 * @param mixed                     $opts   Request options.
	 * @return StripeObject
	 */
	public function all( $params = null, $opts = null ) {
		$params = is_array( $params ) ? $params : array();

		$this->ledger->record(
			'webhookEndpoints',
			'all',
			array(
				'params' => $params,
				'opts' => $opts,
			)
		);

		$ids = array_keys( $this->ledger->endpoints );

		if ( isset( $params['starting_after'] ) ) {
			$position = array_search( (string) $params['starting_after'], $ids, true );
			$ids      = false === $position ? array() : array_slice( $ids, $position + 1 );
		}

		$limit    = isset( $params['limit'] ) ? (int) $params['limit'] : 10;
		$has_more = count( $ids ) > $limit;
		$ids      = array_slice( $ids, 0, $limit );
		$data     = array();

		foreach ( $ids as $id ) {
			$data[] = $this->ledger->endpoints[ $id ];
		}

		return StripeObject::constructFrom(
			array(
				'object'   => 'list',
				'url'      => '/v1/webhook_endpoints',
				'data'     => $data,
				'has_more' => $has_more,
			),
			null
		);
	}

	/**
	 * Create an endpoint.
	 *
	 * @param array<string, mixed>|null $params Create parameters.
	 * @param mixed                     $opts   Request options.
	 * @return StripeObject
	 */
	public function create( $params = null, $opts = null ) {
		$params = is_array( $params ) ? $params : array();

		$this->ledger->record(
			'webhookEndpoints',
			'create',
			array(
				'params' => $params,
				'opts' => $opts,
			)
		);

		return $this->ledger->create_endpoint( $params );
	}

	/**
	 * Retrieve an endpoint.
	 *
	 * @param string                    $id     Endpoint id.
	 * @param array<string, mixed>|null $params Retrieve parameters.
	 * @param mixed                     $opts   Request options.
	 * @return StripeObject
	 * @throws InvalidRequestException When the endpoint does not exist.
	 */
	public function retrieve( $id, $params = null, $opts = null ) {
		$this->ledger->record(
			'webhookEndpoints',
			'retrieve',
			array(
				'id' => $id,
				'params' => $params,
				'opts' => $opts,
			)
		);

		if ( ! isset( $this->ledger->endpoints[ $id ] ) ) {
			throw InvalidRequestException::factory(
				'No such webhook endpoint: ' . $id, // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception text is not rendered output.
				404,
				null,
				null,
				null,
				'resource_missing'
			);
		}

		return $this->ledger->endpoints[ $id ];
	}

	/**
	 * Update an endpoint.
	 *
	 * @param string                    $id     Endpoint id.
	 * @param array<string, mixed>|null $params Update parameters.
	 * @param mixed                     $opts   Request options.
	 * @return StripeObject
	 */
	public function update( $id, $params = null, $opts = null ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Signature mirrors the Stripe SDK service.
		$params = is_array( $params ) ? $params : array();

		$this->ledger->record(
			'webhookEndpoints',
			'update',
			array(
				'id' => $id,
				'params' => $params,
			)
		);
		$this->ledger->update_params[ $id ][] = $params;

		return $this->ledger->update_endpoint( (string) $id, $params );
	}

	/**
	 * Delete an endpoint.
	 *
	 * @param string                    $id     Endpoint id.
	 * @param array<string, mixed>|null $params Delete parameters.
	 * @param mixed                     $opts   Request options.
	 * @return StripeObject
	 */
	public function delete( $id, $params = null, $opts = null ) {
		$this->ledger->record(
			'webhookEndpoints',
			'delete',
			array(
				'id' => $id,
				'params' => $params,
				'opts' => $opts,
			)
		);

		unset( $this->ledger->endpoints[ $id ] );
		$this->ledger->deleted[] = $id;

		return StripeObject::constructFrom(
			array(
				'id'      => $id,
				'object'  => 'webhook_endpoint',
				'deleted' => true,
			),
			null
		);
	}
}
