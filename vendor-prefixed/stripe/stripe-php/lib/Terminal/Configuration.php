<?php

// File generated from our OpenAPI spec
namespace GiftFlow\Vendor\Stripe\Terminal;

/**
 * A Configurations object represents how features should be configured for terminal readers.
 * For information about how to use it, see the <a href="https://docs.stripe.com/terminal/fleet/configurations-overview">Terminal configurations documentation</a>.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property null|(object{splashscreen?: string|\GiftFlow\Vendor\Stripe\File}&\GiftFlow\Vendor\Stripe\StripeObject) $bbpos_wisepad3
 * @property null|(object{splashscreen?: string|\GiftFlow\Vendor\Stripe\File}&\GiftFlow\Vendor\Stripe\StripeObject) $bbpos_wisepos_e
 * @property null|(object{enabled: bool}&\GiftFlow\Vendor\Stripe\StripeObject) $cellular
 * @property null|bool $is_account_default Whether this Configuration is the default for your account
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property null|string $name String indicating the name of the Configuration object, set by the user
 * @property null|(object{enabled: null|bool}&\GiftFlow\Vendor\Stripe\StripeObject) $offline
 * @property null|(object{end_hour: int, start_hour: int}&\GiftFlow\Vendor\Stripe\StripeObject) $reboot_window
 * @property null|(object{splashscreen?: string|\GiftFlow\Vendor\Stripe\File}&\GiftFlow\Vendor\Stripe\StripeObject) $stripe_s700
 * @property null|(object{splashscreen?: string|\GiftFlow\Vendor\Stripe\File}&\GiftFlow\Vendor\Stripe\StripeObject) $stripe_s710
 * @property null|(object{aed?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), aud?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), cad?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), chf?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), czk?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), dkk?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), eur?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), gbp?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), gip?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), hkd?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), huf?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), jpy?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), mxn?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), myr?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), nok?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), nzd?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), pln?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), ron?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), sek?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), sgd?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject), usd?: (object{fixed_amounts?: null|int[], percentages?: null|int[], smart_tip_threshold?: int}&\GiftFlow\Vendor\Stripe\StripeObject)}&\GiftFlow\Vendor\Stripe\StripeObject) $tipping
 * @property null|(object{splashscreen?: string|\GiftFlow\Vendor\Stripe\File}&\GiftFlow\Vendor\Stripe\StripeObject) $verifone_p400
 * @property null|(object{enterprise_eap_peap?: (object{ca_certificate_file?: string, password: string, ssid: string, username: string}&\GiftFlow\Vendor\Stripe\StripeObject), enterprise_eap_tls?: (object{ca_certificate_file?: string, client_certificate_file: string, private_key_file: string, private_key_file_password?: string, ssid: string}&\GiftFlow\Vendor\Stripe\StripeObject), personal_psk?: (object{password: string, ssid: string}&\GiftFlow\Vendor\Stripe\StripeObject), type: string}&\GiftFlow\Vendor\Stripe\StripeObject) $wifi
 */
class Configuration extends \GiftFlow\Vendor\Stripe\ApiResource
{
    const OBJECT_NAME = 'terminal.configuration';
    use \GiftFlow\Vendor\Stripe\ApiOperations\Update;
    /**
     * Creates a new <code>Configuration</code> object.
     *
     * @param null|array{bbpos_wisepad3?: array{splashscreen?: null|string}, bbpos_wisepos_e?: array{splashscreen?: null|string}, cellular?: null|array{enabled: bool}, expand?: string[], name?: string, offline?: null|array{enabled: bool}, reboot_window?: array{end_hour: int, start_hour: int}, stripe_s700?: array{splashscreen?: null|string}, stripe_s710?: array{splashscreen?: null|string}, tipping?: null|array{aed?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, aud?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, cad?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, chf?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, czk?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, dkk?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, eur?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, gbp?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, gip?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, hkd?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, huf?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, jpy?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, mxn?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, myr?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, nok?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, nzd?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, pln?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, ron?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, sek?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, sgd?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, usd?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}}, verifone_p400?: array{splashscreen?: null|string}, wifi?: null|array{enterprise_eap_peap?: array{ca_certificate_file?: string, password: string, ssid: string, username: string}, enterprise_eap_tls?: array{ca_certificate_file?: string, client_certificate_file: string, private_key_file: string, private_key_file_password?: string, ssid: string}, personal_psk?: array{password: string, ssid: string}, type: string}} $params
     * @param null|array|string $options
     *
     * @return Configuration the created resource
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public static function create($params = null, $options = null)
    {
        self::_validateParams($params);
        $url = static::classUrl();
        list($response, $opts) = static::_staticRequest('post', $url, $params, $options);
        $obj = \GiftFlow\Vendor\Stripe\Util\Util::convertToStripeObject($response->json, $opts);
        $obj->setLastResponse($response);
        return $obj;
    }
    /**
     * Deletes a <code>Configuration</code> object.
     *
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @return Configuration the deleted resource
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function delete($params = null, $opts = null)
    {
        self::_validateParams($params);
        $url = $this->instanceUrl();
        list($response, $opts) = $this->_request('delete', $url, $params, $opts);
        $this->refreshFrom($response, $opts);
        return $this;
    }
    /**
     * Returns a list of <code>Configuration</code> objects.
     *
     * @param null|array{ending_before?: string, expand?: string[], is_account_default?: bool, limit?: int, starting_after?: string} $params
     * @param null|array|string $opts
     *
     * @return \GiftFlow\Vendor\Stripe\Collection<Configuration> of ApiResources
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public static function all($params = null, $opts = null)
    {
        $url = static::classUrl();
        return static::_requestPage($url, \GiftFlow\Vendor\Stripe\Collection::class, $params, $opts);
    }
    /**
     * Retrieves a <code>Configuration</code> object.
     *
     * @param array|string $id the ID of the API resource to retrieve, or an options array containing an `id` key
     * @param null|array|string $opts
     *
     * @return Configuration
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public static function retrieve($id, $opts = null)
    {
        $opts = \GiftFlow\Vendor\Stripe\Util\RequestOptions::parse($opts);
        $instance = new static($id, $opts);
        $instance->refresh();
        return $instance;
    }
    /**
     * Updates a new <code>Configuration</code> object.
     *
     * @param string $id the ID of the resource to update
     * @param null|array{bbpos_wisepad3?: null|array{splashscreen?: null|string}, bbpos_wisepos_e?: null|array{splashscreen?: null|string}, cellular?: null|array{enabled: bool}, expand?: string[], name?: string, offline?: null|array{enabled: bool}, reboot_window?: null|array{end_hour: int, start_hour: int}, stripe_s700?: null|array{splashscreen?: null|string}, stripe_s710?: null|array{splashscreen?: null|string}, tipping?: null|array{aed?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, aud?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, cad?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, chf?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, czk?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, dkk?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, eur?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, gbp?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, gip?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, hkd?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, huf?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, jpy?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, mxn?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, myr?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, nok?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, nzd?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, pln?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, ron?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, sek?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, sgd?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}, usd?: array{fixed_amounts?: int[], percentages?: int[], smart_tip_threshold?: int}}, verifone_p400?: null|array{splashscreen?: null|string}, wifi?: null|array{enterprise_eap_peap?: array{ca_certificate_file?: string, password: string, ssid: string, username: string}, enterprise_eap_tls?: array{ca_certificate_file?: string, client_certificate_file: string, private_key_file: string, private_key_file_password?: string, ssid: string}, personal_psk?: array{password: string, ssid: string}, type: string}} $params
     * @param null|array|string $opts
     *
     * @return Configuration the updated resource
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public static function update($id, $params = null, $opts = null)
    {
        self::_validateParams($params);
        $url = static::resourceUrl($id);
        list($response, $opts) = static::_staticRequest('post', $url, $params, $opts);
        $obj = \GiftFlow\Vendor\Stripe\Util\Util::convertToStripeObject($response->json, $opts);
        $obj->setLastResponse($response);
        return $obj;
    }
}