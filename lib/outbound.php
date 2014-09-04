<?php

if (!function_exists('json_encode')) {
    throw new Exception('Outbound requires the JSON extension to work.');
}

class OutboundApiException extends Exception {}
class OutboundDataException extends Exception {}
class OutboundConnectionException extends Exception {}

class Outbound {
    const VERSION = 0.1;

    const TRACK = 1;
    const IDENTIFY = 2;

    const URL_ROOT = 'https://api.outbound.io/v2';

    private static $api_key;
    private static $connect_timeout;
    private static $timeout;

    public static function init($api_key, $connect_timeout=5, $timeout=30) {
        self::$api_key = $api_key;
        self::$connect_timeout = $connect_timeout;
        self::$timeout = $timeout;
    }

    /**
     * This method exists solely for the purpose of testing and should not be
     * be called outside of the tests.
     */
    public static function reset() {
        self::$api_key = null;
    }

    /**
     * Make an identify API call to Outbound for a user.
     *
     * @param string|number user_id - ID of the user who needs to be identified.
     * @param Array user_info - Special user attributes such as first name,
     *      email, phone number, etc. Unsupported fields will be removed.
     * @param Array user_attrs [OPTIONAL] - Any extra attributes of the user that
     *      needs to be tracked.
     * @throws OutboundApiException, OutboundConnectionException, OutboundDataException, Exception
     */
    public static function identify($user_id, Array $user_info=null, Array $user_attrs=null)  {
        self::_ensure_init();
        $user = self::_build_user($user_id, $user_info, $user_attrs);
        self::_execute(self::IDENTIFY, $user);
    }

    /**
     * Make a track API call to Outbound for a given event.
     *
     * @param string|number user_id - ID of the user who triggered the event.
     * @param string event - The name of the event that was triggered.
     * @param Array properties [OPTIONAL] - Any event specific properties to be tracked.
     * @throws OutboundApiException, OutboundConnectionException, OutboundDataException, Exception
     */
    public static function track($user_id, $event, Array $properties=null) {
        self::_ensure_init();
        $user = self::_build_user($user_id);

        if (!is_string($event)) {
            throw new OutboundDataException('Event must be a string. Received ' . (!is_callable($event) ? gettype($event) : 'function') . '.');
        }

        $data = array(
            'event' => $event,
            'user_id' => $user['user_id'],
        );
        if ($properties) {
            $data['properties'] = $properties;
        }
        self::_execute(self::TRACK, $data);
    }

    private static function _ensure_init() {
        if (!self::$api_key) {
            throw new Exception('init() must be called before anything else.');
        }
    }

    /**
     * Validate a user id is of a supported type.
     *
     * @param string|number user_id - User ID to validate. Anything other than
     *      string or number results in exception.
     * @throws OutboundDataException
     * @return bool
     */
    private static function _validate_user_id($user_id) {
        if (is_string($user_id) || is_numeric($user_id)) {
            return true;
        }
        throw new OutboundDataException('User ID must be string or integer. Received ' . (!is_callable($user_id) ? gettype($user_id) : 'function') . '.');
    }

    /**
     * Build a user data array with the given info.
     *
     * @param string|number user_id - User ID to include in data.
     * @param Array user_info - Special user attributes such as first name,
     *      email, phone number, etc. Unsupported fields will be removed.
     * @param Array user_attrs [OPTIONAL] - Any extra attributes of the user that
     *      needs to be tracked.
     * @throws OutboundDataException
     * @return Array
     */
    private static function _build_user($user_id, Array $user_info=null, Array $user_attrs=null) {
        self::_validate_user_id($user_id);

        $user_info = $user_info ? $user_info : array();
        $user_info = array_intersect_key(
            $user_info,
            array(
                'first_name' => null,
                'last_name' => null,
                'email' => null,
                'phone_number' => null,
                'apns' => null,
                'gcm' => null,
            )
        );
        $user_info['user_id'] = $user_id;

        if ($user_attrs) {
            $user_info['attributes'] = $user_attrs;
        }
        return $user_info;
    }

    private static function _build_url($call) {
        $url = self::URL_ROOT;
        if ($call == self::TRACK) {
            $url .= '/track';
        } elseif ($call == self::IDENTIFY) {
            $url .= '/identify';
        } else {
            throw new Exception('Unsupported API call (' . $call . ') given.');
        }
        return $url;
    }

    /**
     * Excute API call to Outbound.
     *
     * @param int call - One of the supported API call constants.
     * @param Array data - Data to be sent to Outbound.
     * @throws OutboundConnectionException, ApiError, Exception
     */
    private static function _execute($call, Array $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::_build_url($call));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-type: application/json',
            'X-Outbound-Key: ' . self::$api_key,
            'X-Outbound-Client: PHP/' . self::VERSION,
        ));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$connect_timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::$timeout);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);

        if (false === $response) {
            $curl_error = curl_error($ch);
            $curl_errno = curl_errno($ch);
            curl_close($ch);

            throw new OutboundConnectionException('Unknown cURL error: ' . $curl_errno . ' - ' . $curl_error);
        } else {
            curl_close($ch);

            $response = trim($response);
            if ($response == '') {
                return true;
            } else {
                $err = json_decode($response, true);
                throw new OutboundApiException($err['error']['Message'], $err['error']['Code']);
            }
        }
    }
}
