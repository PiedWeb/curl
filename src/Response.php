<?php

namespace PiedWeb\Curl;

class Response
{
    private $exec;

    private $url;

    /** @var string * */
    private $headers;
    /** @var string * */
    private $content;
    /** @var array * */
    private $info;

    public static function get($handle, string $url, int $returnHeaders)
    {
        $content = curl_exec($handle);

        if (!$content) {
            return curl_errno($handle);
        }

        $self = new self();

        if (2 === $returnHeaders) {
            $self->headers = $content;
        } else {
            if ($returnHeaders) { // Remove headerss from response
                $self->headers = substr($content, 0, $sHeaders = curl_getinfo($handle, CURLINFO_HEADER_SIZE));
                $content = substr($content, $sHeaders);
            }

            $self->content = $content;
        }

        $self->info = curl_getinfo($handle); // curl_getinfo(self::$ch, CURLINFO_EFFECTIVE_URL)
        $self->url = $url;

        return $self;
    }

    private function __construct()
    {
    }

    public function getContent()
    {
        return $this->content;
    }

    /**
     * Return headers's data return by the request.
     *
     * @param bool $returnArray True to get an array, false to get a string
     *
     * @return array|string|null containing headers's data
     */
    public function getHeaders(bool $returnArray = true)
    {
        if (isset($this->headers)) {
            return true === $returnArray ? Helper::httpParseHeaders($this->headers) : $this->headers;
        }
    }

    /**
     * @return string requested url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Return current effective url.
     *
     * @return string
     */
    public function getEffectiveUrl()
    {
        return isset($this->info['url']) ? $this->info['url'] : null; //curl_getinfo(self::$ch, CURLINFO_EFFECTIVE_URL);
    }

    /**
     * Return the cookie(s) returned by the request (if there are).
     *
     * @return null|array containing the cookies
     */
    public function getCookies()
    {
        if (isset($this->headers)) {
            $headers = $this->getHeaders();
            if (isset($headers['Set-Cookie'])) {
                if (is_array($headers['Set-Cookie'])) {
                    return implode('; ', $headers['Set-Cookie']);
                } else {
                    return $headers['Set-Cookie'];
                }
            }
        }
    }

    /**
     * Get information regarding the request.
     *
     * @return array an associative array with the following elements (which correspond to opt), or FALSE on failure
     */
    public function getInfo()
    {
        return $this->info;
    }

    public function getStatusCode()
    {
        return $this->info['http_code'];
    }

    public function getContentType()
    {
        return $this->info['content_type'];
    }
}
