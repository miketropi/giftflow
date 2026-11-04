<?php
/**
 * PayPal HTTP transport test double.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Support;

/**
 * Records requests and returns deterministic WordPress-style responses.
 */
final class FakePayPalHttp {

	/**
	 * Recorded HTTP calls.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	private $calls = array();

	/**
	 * Queued deterministic responses.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	private $queued = array();

	/**
	 * Fallback response when no queued response matches.
	 *
	 * @var array<string, mixed>
	 */
	private $default_response = array(
		'response' => array( 'code' => 404 ),
		'body' => '{}',
	);

	/**
	 * Optional in-memory PayPal backend.
	 *
	 * @var FakePayPalLedger|null
	 */
	private $ledger;

	/**
	 * Constructor.
	 *
	 * @param FakePayPalLedger|null $ledger Optional ledger.
	 */
	public function __construct( ?FakePayPalLedger $ledger = null ) {
		$this->ledger = $ledger;
	}

	/**
	 * Handle one transport request.
	 *
	 * @param string $method HTTP method.
	 * @param string $url    Full URL.
	 * @param array  $args   Request arguments.
	 * @return array|\WP_Error
	 */
	public function __invoke( string $method, string $url, array $args ) {
		$decoded = isset( $args['body'] ) && is_string( $args['body'] ) ? json_decode( $args['body'], true ) : array();
		$decoded = is_array( $decoded ) ? $decoded : array();
		$call    = array(
			'method' => strtoupper( $method ),
			'url' => $url,
			'args' => $args,
			'body' => $decoded,
		);
		$this->calls[] = $call;

		foreach ( $this->queued as $index => $item ) {
			if ( $item['method'] === $call['method'] && false !== strpos( $url, $item['path'] ) ) {
				unset( $this->queued[ $index ] );
				$this->queued = array_values( $this->queued );
				return $item['response'];
			}
		}

		if ( null !== $this->ledger ) {
			return $this->ledger->respond( $call['method'], $url, $args, $decoded );
		}
		return $this->default_response;
	}

	/**
	 * Queue a response for requests matching a method and path.
	 *
	 * @param string $method   HTTP method.
	 * @param string $path     URL path fragment to match.
	 * @param mixed  $response WordPress-style HTTP response.
	 * @return void
	 */
	public function queue( string $method, string $path, $response ): void {
		$this->queued[] = array(
			'method' => strtoupper( $method ),
			'path' => $path,
			'response' => $response,
		);
	}

	/**
	 * Set the fallback response.
	 *
	 * @param mixed $response WordPress-style HTTP response.
	 * @return void
	 */
	public function set_default( $response ): void {
		$this->default_response = $response;
	}

	/**
	 * Count recorded requests matching a method and path.
	 *
	 * @param string $method HTTP method.
	 * @param string $path   URL path fragment to match.
	 * @return int
	 */
	public function count( string $method, string $path ): int {
		$count = 0;
		foreach ( $this->calls as $call ) {
			if ( strtoupper( $method ) === $call['method'] && false !== strpos( $call['url'], $path ) ) {
				++$count;
			}
		}
		return $count;
	}

	/**
	 * Return all recorded request URLs.
	 *
	 * @return string[]
	 */
	public function urls(): array {
		return array_column( $this->calls, 'url' );
	}

	/**
	 * Return all recorded calls.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function calls(): array {
		return $this->calls;
	}

	/**
	 * Clear recorded calls and queued responses.
	 *
	 * @return void
	 */
	public function reset(): void {
		$this->calls = array();
		$this->queued = array();
	}
}
