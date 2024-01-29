<?php

/*
 * PHP-HTTP 
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace Rini\Core\Http;

/** HTTP response headers sent by the server */
final class ResponseHeader {

	private function __construct() { }

	/**
	 * Returns the header with the specified name (and optional value prefix)
	 *
	 * @param string $name the name of the header
	 * @param string $value Prefix the optional string to match at the beginning of the header's value
	 * @return string|null the header (if found) or `null`
	 */
	public static function get($name, $valuePrefix = '') {
		if (empty($name)) {
			return null;
		}

		$nameLength = \strlen($name);
		$headers = \headers_list();

		foreach ($headers as $header) {
			if (\strcasecmp(\substr($header, 0, $nameLength + 1), ($name . ':')) === 0) {
				$headerValue = \trim(\substr($header, $nameLength + 1), "\t ");

				if (empty($valuePrefix) || \substr($headerValue, 0, \strlen($valuePrefix)) === $valuePrefix) {
					return $header;
				}
			}
		}

		return null;
	}

	/**
	 * Returns the value of the header with the specified name (and optional value prefix)
	 *
	 * @param string $name the name of the header
	 * @param string $valuePrefix the optional string to match at the beginning of the header's value
	 * @return string|null the value of the header (if found) or `null`
	 */
	public static function getValue($name, $valuePrefix = '') {
		$header = static::get($name, $valuePrefix);

		if (!empty($header)) {
			$nameLength = \strlen($name);
			$headerValue = \substr($header, $nameLength + 1);
			$headerValue = \trim($headerValue, "\t ");

			return $headerValue;
		}
		else {
			return null;
		}
	}

	/**
	 * Sets the header with the specified name and value
	 *
	 * If another header with the same name has already been set previously, that header will be overwritten
	 *
	 * @param string $name the name of the header
	 * @param string $value the corresponding value for the header
	 */
	public static function set($name, $value) {
		\header($name . ': ' . $value, true);
	}

	/**
	 * Adds the header with the specified name and value
	 *
	 * If another header with the same name has already been set previously, both headers (or header values) will be sent
	 *
	 * @param string $name the name of the header
	 * @param string $value the corresponding value for the header
	 */
	public static function add($name, $value) {
		\header($name . ': ' . $value, false);
	}

	/**
	 * Removes the header with the specified name (and optional value prefix)
	 *
	 * @param string $name the name of the header
	 * @param string $valuePrefix the optional string to match at the beginning of the header's value
	 * @return bool whether a header, as specified, has been found and removed
	 */
	public static function remove($name, $valuePrefix = '') {
		return static::take($name, $valuePrefix) !== null;
	}

	/**
	 * Returns and removes the header with the specified name (and optional value prefix)
	 *
	 * @param string $name the name of the header
	 * @param string $valuePrefix the optional string to match at the beginning of the header's value
	 * @return string|null the header (if found) or `null`
	 */
	public static function take($name, $valuePrefix = '') {
		if (empty($name)) {
			return null;
		}

		$nameLength = \strlen($name);
		$headers = \headers_list();

		$first = null;
		$homonyms = [];

		foreach ($headers as $header) {
			if (\strcasecmp(\substr($header, 0, $nameLength + 1), ($name . ':')) === 0) {
				$headerValue = \trim(\substr($header, $nameLength + 1), "\t ");

				if ((empty($valuePrefix) || \substr($headerValue, 0, \strlen($valuePrefix)) === $valuePrefix) && $first === null) {
					$first = $header;
				}
				else {
					$homonyms[] = $header;
				}
			}
		}

		if ($first !== null) {
			\header_remove($name);

			foreach ($homonyms as $homonym) {
				\header($homonym, false);
			}
		}

		return $first;
	}

	/**
	 * Returns the value of and removes the header with the specified name (and optional value prefix)
	 *
	 * @param string $name the name of the header
	 * @param string $valuePrefix the optional string to match at the beginning of the header's value
	 * @return string|null the value of the header (if found) or `null`
	 */
	public static function takeValue($name, $valuePrefix = '') {
		$header = static::take($name, $valuePrefix);

		if (!empty($header)) {
			$nameLength = \strlen($name);
			$headerValue = \substr($header, $nameLength + 1);
			$headerValue = \trim($headerValue, "\t ");

			return $headerValue;
		}
		else {
			return null;
		}
	}

	/**
	 * Set HTTP Status Header
	 *
	 * @param	int	the status code
	 * @param	string
	 * @return	void
	 */
	public static function set_status_header($code = 200, $text = '')
	{

		if (empty($code) OR ! is_numeric($code))
		{
			die('Status codes must be numeric');
		}

		if (empty($text))
		{
			is_int($code) OR $code = (int) $code;
			$stati = array(
				100	=> 'Continue',
				101	=> 'Switching Protocols',

				200	=> 'OK',
				201	=> 'Created',
				202	=> 'Accepted',
				203	=> 'Non-Authoritative Information',
				204	=> 'No Content',
				205	=> 'Reset Content',
				206	=> 'Partial Content',

				300	=> 'Multiple Choices',
				301	=> 'Moved Permanently',
				302	=> 'Found',
				303	=> 'See Other',
				304	=> 'Not Modified',
				305	=> 'Use Proxy',
				307	=> 'Temporary Redirect',

				400	=> 'Bad Request',
				401	=> 'Unauthorized',
				402	=> 'Payment Required',
				403	=> 'Forbidden',
				404	=> 'Not Found',
				405	=> 'Method Not Allowed',
				406	=> 'Not Acceptable',
				407	=> 'Proxy Authentication Required',
				408	=> 'Request Timeout',
				409	=> 'Conflict',
				410	=> 'Gone',
				411	=> 'Length Required',
				412	=> 'Precondition Failed',
				413	=> 'Request Entity Too Large',
				414	=> 'Request-URI Too Long',
				415	=> 'Unsupported Media Type',
				416	=> 'Requested Range Not Satisfiable',
				417	=> 'Expectation Failed',
				422	=> 'Unprocessable Entity',
				426	=> 'Upgrade Required',
				428	=> 'Precondition Required',
				429	=> 'Too Many Requests',
				431	=> 'Request Header Fields Too Large',

				500	=> 'Internal Server Error',
				501	=> 'Not Implemented',
				502	=> 'Bad Gateway',
				503	=> 'Service Unavailable',
				504	=> 'Gateway Timeout',
				505	=> 'HTTP Version Not Supported',
				511	=> 'Network Authentication Required',
			);

			if (isset($stati[$code]))
			{
				$text = $stati[$code];
			}
			else
			{
				die('No status text available. Please check your status code number or supply your own message text.');
			}
		}

		if (strpos(PHP_SAPI, 'cgi') === 0)
		{
			header('Status: '.$code.' '.$text, TRUE);
			return;
		}

		$server_protocol = (isset($_SERVER['SERVER_PROTOCOL']) && in_array($_SERVER['SERVER_PROTOCOL'], array('HTTP/1.0', 'HTTP/1.1', 'HTTP/2'), TRUE))
			? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
		header($server_protocol.' '.$code.' '.$text, TRUE, $code);
	}

}
