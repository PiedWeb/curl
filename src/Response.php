<?php

namespace PiedWeb\Curl;

use Exception;

class Response
{
    /** @var Request */
    protected $request;

    protected string $headers = '';

    /** @var string * */
    protected string $content;

    /** @var array<string, int|string>  an associative array with the following elements (which correspond to opt): "url" "content_type" "http_code" "header_size" "request_size" "filetime" "ssl_verify_result" "redirect_count" "total_time" "namelookup_time" "connect_time" "pretransfer_time" "size_upload" "size_download" "speed_download" "speed_upload" "download_content_length" "upload_content_length" "starttransfer_time" "redirect_time" */
    protected $info;

    /**
     * @return self|int
     * @psalm-suppress InvalidArgument (for $handle)
     */
    public static function createFromRequest(Request $request)
    {
        $handle = $request->getHandle();

        $content = curl_exec($handle);

        if (false === $content) {
            return curl_errno($handle);
        }

        if (true === $content) {
            return 0; //throw new Exception('CURLOPT_RETURNTRANSFER and CURLOPT_HEADER was set to 0.');
        }

        $self = new self($request);

        if (Request::RETURN_HEADER_ONLY === $request->mustReturnHeaders()) {
            $self->headers = $content;
        } else {
            if (Request::RETURN_HEADER === $request->mustReturnHeaders()) { // Remove headers from response
                $self->headers = substr($content, 0, $sHeaders = (int) $request->getInfo(\CURLINFO_HEADER_SIZE));
                $content = substr($content, $sHeaders);
            }

            $self->content = $content;
        }

        $self->info = $request->getInfos();

        return $self;
    }

    private function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Return headers's data return by the request.
     *
     * @return ?array<int|string, string|string[]> containing headers's data
     */
    public function getHeaders(): ?array
    {
        if ('' === $this->headers) {
            return null;
        }

        $parsed = Helper::httpParseHeaders($this->headers);
        if ([] === $parsed) {
            throw new Exception('Failed to parse Headers `'.$this->headers.'`');
        }

        return $parsed;
    }

    public function getRawHeaders(): string
    {
        return $this->headers;
    }

    /**
     * @return string requested url
     */
    public function getUrl(): string
    {
        return $this->request->getUrl();
    }

    /**
     * Return current effective url.
     *
     * @return string
     */
    public function getEffectiveUrl(): ?string
    {
        return isset($this->info['url']) ? (string) $this->info['url'] : null;
        //curl_getinfo(self::$ch, CURLINFO_EFFECTIVE_URL);
    }

    /**
     * Return the cookie(s) returned by the request (if there are).
     *
     * @return string|null containing the cookies
     */
    public function getCookies()
    {
        $headers = $this->getHeaders();
        if (null !== $headers && isset($headers['Set-Cookie'])) {
            if (\is_array($headers['Set-Cookie'])) {
                return implode('; ', $headers['Set-Cookie']);
            } else {
                return $headers['Set-Cookie'];
            }
        }

        return null;
    }

    /**
     * Get information regarding the request.
     *
     * @param string $key to get
     *
     * @return int|string|array<string, string|int>|null
     */
    public function getInfo(?string $key = null)
    {
        return $key ? (isset($this->info[$key]) ? $this->info[$key] : null) : $this->info;
    }

    public function getStatusCode(): int
    {
        return (int) $this->info['http_code'];
    }

    public function getContentType(): string
    {
        return (string) $this->info['content_type'];
    }

    public function getMimeType(): ?string
    {
        $headers = Helper::parseHeader($this->getContentType());

        return $headers[0][0] ?? null;
    }
}
