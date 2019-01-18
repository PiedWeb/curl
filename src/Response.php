<?php

namespace PiedWeb\Curl;

class Response
{
    /** @var Request */
    protected $request;

    /** @var string */
    protected $headers;
    /** @var string * */
    protected $content;
    /** @var array * */
    protected $info;

    public static function get(Request $request)
    {
        $handle = $request->getHandle();

        $content = curl_exec($handle);

        if (!$content) {
            return curl_errno($handle);
        }

        $self = new self($request);

        if (Request::RETURN_HEADER_ONLY === $request->mustReturnHeaders()) {
            $self->headers = $content;
        } else {
            if (Request::RETURN_HEADER === $request->mustReturnHeaders()) { // Remove headers from response
                $self->headers = substr($content, 0, $sHeaders = curl_getinfo($handle, CURLINFO_HEADER_SIZE));
                $content = substr($content, $sHeaders);
            }

            $self->content = $content;
        }

        $self->info = curl_getinfo($handle); // curl_getinfo(self::$ch, CURLINFO_EFFECTIVE_URL)

        return $self;
    }

    private function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
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
        return $this->request->getUrl();
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
     * @return array|null containing the cookies
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
     * @param string $key to get
     *
     * @return string|array
     */
    public function getInfo(?string $key = null)
    {
        return $key ? (isset($this->info[$key]) ? $this->info[$key] : null) : $this->info;
    }

    public function getStatusCode()
    {
        return $this->info['http_code'];
    }

    public function getContentType()
    {
        return $this->info['content_type'];
    }

    public function getMimeType()
    {
        $headers = Helper::parseHeader($this->getContentType());

        return $headers[0][0] ?? null;
    }
}
