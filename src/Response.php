<?php

namespace PiedWeb\Curl;

class Response
{
    private $exec;

    private $url;

    /** @var string * */
    private $header;
    /** @var string * */
    private $content;
    /** @var array * */
    private $info;

    public function get($handle, string $url, int $returnHeader, bool $gzip)
    {
        $content = curl_exec($handle);

        if (!$content) {
            return curl_errno($handle);
        }

        $self = new self();

        if (2 === $returnHeader) {
            $self->header = $content;
        } else {
            if ($returnHeader) { // Remove headers from response
                $self->header = substr($content, 0, $sHeader = curl_getinfo($handle, CURLINFO_HEADER_SIZE));
                $content = substr($content, $sHeader);
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
     * Return header's data return by the request.
     *
     * @param bool $returnArray True to get an array, false to get a string
     *
     * @return array|string|null containing header's data
     */
    public function getHeader(bool $returnArray = true)
    {
        if (isset($this->header)) {
            return true === $arrayFormatted ? Helper::http_parse_headers($this->header) : $this->header;
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
        if (isset($this->header)) {
            $header = $this->getHeader();
            if (isset($header['Set-Cookie'])) {
                return is_array($header['Set-Cookie']) ? implode('; ', $header['Set-Cookie']) : $header['Set-Cookie'];
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
