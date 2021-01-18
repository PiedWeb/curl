<?php

namespace PiedWeb\Curl;

class Helper
{
    /**
     * Return scheme from proxy string and remove Scheme From proxy.
     *
     * @param string $proxy
     *
     * @return string
     */
    public static function getSchemeFrom(&$proxy)
    {
        if (! preg_match('@^([a-z0-9]*)://@', $proxy, $match)) {
            return 'http://';
        }
        $scheme = $match[1].'://';
        $proxy = substr($proxy, strlen($scheme));

        return $scheme;
    }

    /**
     * Parse HTTP headers (php HTTP functions but generally, this packet isn't installed).
     *
     * @source http://www.php.net/manual/en/function.http-parse-headers.php#112917
     *
     * @param string $raw_headers Contain HTTP headers
     *
     * @return bool|array an array on success or FALSE on failure
     */
    public static function httpParseHeaders($raw_headers)
    {
        if (function_exists('http_parse_headers')) {
            http_parse_headers($raw_headers);
        }
        $headers = [];
        $key = '';
        foreach (explode("\n", $raw_headers) as $i => $h) {
            $h = explode(':', $h, 2);
            if (isset($h[1])) {
                if (! isset($headers[$h[0]])) {
                    $headers[$h[0]] = trim($h[1]);
                } elseif (is_array($headers[$h[0]])) {
                    $headers[$h[0]] = array_merge($headers[$h[0]], [trim($h[1])]);
                } else {
                    $headers[$h[0]] = array_merge([$headers[$h[0]]], [trim($h[1])]);
                }
                $key = $h[0];
            } else {
                if ("\t" == substr($h[0], 0, 1)) {
                    $headers[$key] .= "\r\n\t".trim($h[0]);
                } elseif (! $key) {
                    $headers[0] = trim($h[0]);
                }
                trim($h[0]);
            }
        }

        return $headers;
    }

    /**
     * This is taken from the GuzzleHTTP/PSR7 library,
     * see https://github.com/guzzle/psr7 for more info.
     *
     * Parse an array of header values containing ";" separated data into an
     * array of associative arrays representing the header key value pair
     * data of the header. When a parameter does not contain a value, but just
     * contains a key, this function will inject a key with a '' string value.
     *
     * @param string|array $header header to parse into components
     *
     * @return array returns the parsed header values
     */
    public static function parseHeader($header)
    {
        static $trimmed = "\"'  \n\t\r";
        $params = $matches = [];
        foreach (self::normalizeHeader($header) as $val) {
            $part = [];
            foreach (preg_split('/;(?=([^"]*"[^"]*")*[^"]*$)/', $val) as $kvp) {
                if (preg_match_all('/<[^>]+>|[^=]+/', $kvp, $matches)) {
                    $m = $matches[0];
                    if (isset($m[1])) {
                        $part[trim($m[0], $trimmed)] = trim($m[1], $trimmed);
                    } else {
                        $part[] = trim($m[0], $trimmed);
                    }
                }
            }
            if ($part) {
                $params[] = $part;
            }
        }

        return $params;
    }

    /**
     * This is taken from the GuzzleHTTP/PSR7 library,
     * see https://github.com/guzzle/psr7 for more info.
     *
     * Converts an array of header values that may contain comma separated
     * headers into an array of headers with no comma separated values.
     *
     * @param string|array $header header to normalize
     *
     * @return array returns the normalized header field values
     */
    protected static function normalizeHeader($header)
    {
        if (! is_array($header)) {
            return array_map('trim', explode(',', $header));
        }
        $result = [];
        foreach ($header as $value) {
            foreach ((array) $value as $v) {
                if (false === strpos($v, ',')) {
                    $result[] = $v;

                    continue;
                }
                foreach (preg_split('/,(?=([^"]*"[^"]*")*[^"]*$)/', $v) as $vv) {
                    $result[] = trim($vv);
                }
            }
        }

        return $result;
    }

    public static function checkContentType($line, $expected = 'text/html')
    {
        return 0 === stripos(trim($line), 'content-type') && false !== stripos($line, $expected);
    }

    public static function checkStatusCode($line, $expected = 200)
    {
        return 0 === stripos(trim($line), 'http') && false !== stripos($line, ' '.$expected.' ');
    }
}
