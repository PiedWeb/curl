<?php

namespace PiedWeb\Curl;

use CurlHandle;

class Request
{
    use StaticWrapperTrait;
    use UserAgentTrait;

    public const RETURN_HEADER_ONLY = 2;

    public const RETURN_HEADER = 1;

    private CurlHandle $handle;

    /** @var string contains targeted URL */
    private string $url;

    /** @var string contains current UA */
    private string $userAgent;

    private int $returnHeaders = 0;

    /**
     * @var callable
     */
    private $filter;

    private bool $optChangeDuringRequest = false;

    public function __construct(?string $url = null)
    {
        $this->handle = \Safe\curl_init();
        $this->setOpt(\CURLOPT_RETURNTRANSFER, 1);

        if (null !== $url) {
            $this->setUrl($url);
        }
    }

    public function getHandle(): CurlHandle
    {
        return $this->handle;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Change the URL to cURL.
     *
     * @param string $url to request
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        $this->setOpt(\CURLOPT_URL, $url);

        return $this;
    }

    /**
     * Add a cURL's option.
     *
     * @param int   $option cURL Predefined Constant
     * @param mixed $value
     */
    public function setOpt(int $option, $value): self
    {
        curl_setopt($this->handle, $option, $value);

        return $this;
    }

    /**
     * A short way to set some classic options to cURL a web page.
     */
    public function setDefaultGetOptions(
        int $connectTimeOut = 5,
        int $timeOut = 10,
        int $dnsCacheTimeOut = 600,
        bool $followLocation = true,
        int $maxRedirs = 5,
        bool $autoReferer = true
    ): self {
        $this
            ->setOpt(\CURLOPT_AUTOREFERER, $autoReferer)
            ->setOpt(\CURLOPT_FOLLOWLOCATION, $followLocation)
            ->setOpt(\CURLOPT_MAXREDIRS, $maxRedirs)
            ->setOpt(\CURLOPT_CONNECTTIMEOUT, $connectTimeOut)
            ->setOpt(\CURLOPT_DNS_CACHE_TIMEOUT, $dnsCacheTimeOut)
            ->setOpt(\CURLOPT_TIMEOUT, $timeOut)
        ;

        return $this;
    }

    /**
     * A short way to set some classic options to cURL a web page quickly
     * (but loosing some data like header, cookie...).
     */
    public function setDefaultSpeedOptions(): self
    {
        $this->setOpt(\CURLOPT_SSL_VERIFYHOST, 0);
        $this->setOpt(\CURLOPT_SSL_VERIFYPEER, 0);

        if (! $this->returnHeaders) {
            $this->setOpt(\CURLOPT_HEADER, 0);
        }

        $this->setDefaultGetOptions(5, 10, 600, true, 1);
        $this->setEncodingGzip();

        return $this;
    }

    /**
     * A short way to not follow redirection.
     */
    public function setNoFollowRedirection(): self
    {
        return $this
            ->setOpt(\CURLOPT_FOLLOWLOCATION, false)
            ->setOpt(\CURLOPT_MAXREDIRS, 0)
        ;
    }

    /**
     * Call it if you want header informations.
     * After self::exec(), you would have this informations with getHeader();.
     */
    public function setReturnHeader(bool $only = false): self
    {
        $this->setOpt(\CURLOPT_HEADER, 1);
        $this->returnHeaders = $only ? self::RETURN_HEADER_ONLY : self::RETURN_HEADER;

        if ($only) {
            $this->setOpt(\CURLOPT_RETURNTRANSFER, 0);
            $this->setOpt(\CURLOPT_NOBODY, 1);
        }

        return $this;
    }

    public function mustReturnHeaders(): int
    {
        return $this->returnHeaders;
    }

    /**
     * An self::setOpt()'s alias to add a cookie to your request.
     */
    public function setCookie(string $cookie): self
    {
        $this->setOpt(\CURLOPT_COOKIE, $cookie);

        return $this;
    }

    /**
     * An self::setOpt()'s alias to add a referer to your request.
     */
    public function setReferer(string $referer): self
    {
        $this->setOpt(\CURLOPT_REFERER, $referer);

        return $this;
    }

    /**
     * An self::setOpt()'s alias to add an user-agent to your request.
     */
    public function setUserAgent(string $ua): self
    {
        $this->userAgent = $ua;

        $this->setOpt(\CURLOPT_USERAGENT, $ua);

        return $this;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * A short way to set post's options to cURL a web page.
     *
     * @param mixed $post if it's an array, will be converted via http build query
     *
     * @return self
     */
    public function setPost($post)
    {
        $this->setOpt(\CURLOPT_CUSTOMREQUEST, 'POST');
        $this->setOpt(\CURLOPT_POST, 1);
        $this->setOpt(\CURLOPT_POSTFIELDS, \is_array($post) ? http_build_query($post) : $post);

        return $this;
    }

    /**
     * If you want to request the URL and hope get the result gzipped.
     * The output will be automatically uncompress with exec();.
     */
    public function setEncodingGzip(): self
    {
        $this->setOpt(\CURLOPT_ENCODING, 'gzip, deflate');

        return $this;
    }

    /**
     * If you want to request the URL with a (http|socks...) proxy (public or private).
     *
     * @param string $proxy [scheme]IP:PORT[:LOGIN:PASSWORD]
     *                      Eg. : socks5://98.023.023.02:1098:cUrlRequestProxId:SecretPassword
     */
    public function setProxy(string $proxy): self
    {
        $scheme = Helper::getSchemeFrom($proxy);
        $proxy = explode(':', $proxy);
        $this->setOpt(\CURLOPT_HTTPPROXYTUNNEL, 1);
        $this->setOpt(\CURLOPT_PROXY, $scheme.$proxy[0].':'.$proxy[1]);
        if (isset($proxy[2])) {
            $this->setOpt(\CURLOPT_PROXYUSERPWD, $proxy[2].':'.$proxy[3]);
        }

        return $this;
    }

    /**
     * @param callable $func function wich must return boolean
     */
    public function setDownloadOnlyIf(callable $func): self
    {
        $this->setReturnHeader();

        $this->filter = $func;
        $this->setOpt(\CURLOPT_HEADERFUNCTION, [$this, 'checkHeader']);
        $this->setOpt(\CURLOPT_NOBODY, 1);

        return $this;
    }

    /**
     * @param int $tooBig Default 2000000 = 2000 Kbytes = 2 Mo
     */
    public function setAbortIfTooBig(int $tooBig = 2000000): self
    {
        //$this->setOpt(CURLOPT_BUFFERSIZE, 128); // more progress info
        $this->setOpt(\CURLOPT_NOPROGRESS, false);
        /* @psalm-suppress UnusedClosureParam */
        $this->setOpt(\CURLOPT_PROGRESSFUNCTION, function ($ch, $totalBytes, $receivedBytes) use ($tooBig) {
            if ($receivedBytes > $tooBig) {
                return 1;
            }
        });

        return $this;
    }

    public function setDownloadOnly(string $range = '0-500'): self
    {
        $this->setOpt(\CURLOPT_RANGE, $range);

        return $this;
    }

    public function checkHeader(CurlHandle $handle, string $line): int
    {
            if (\call_user_func($this->filter, $line)) {
                $this->optChangeDuringRequest = true;
                $this->setOpt(\CURLOPT_NOBODY, 0);
            }


        return \strlen($line);
    }

    /**
     * Execute the request.
     *
     * @return Response|int corresponding to the curl error
     */
    public function exec(bool $optChange = false)
    {
        $return = Response::get($this);

        // Permits to transform HEAD request in GET request
        if ($this->optChangeDuringRequest && false === $optChange) {
            $this->optChangeDuringRequest = true;

            return $this->exec(true);
        }

        if ($return instanceof Response && ($effectiveUrl = $return->getEffectiveUrl()) !== null) {
            $this->setReferer($effectiveUrl);
        }

        return $return;
    }

    public function getResponse(): ?Response
    {
        $response = $this->exec();

        if (\is_int($response)) {
            return null;
        }

        return $response;
    }

    /**
     * Return the last error number (curl_errno).
     *
     * @return int the error number or 0 (zero) if no error occurred
     */
    public function hasError(): int
    {
        return curl_errno($this->handle);
    }

    /**
     * Return a string containing the last error for the current session (curl_error).
     *
     * @return string the error message or '' (the empty string) if no error occurred
     */
    public function getError(): string
    {
        return curl_error($this->handle);
    }

    /**
     * Get information regarding the request.
     *
     * @param int $opt This may be one of the following constants:
     *                 http://php.net/manual/en/function.curl-getinfo.php
     *
     * @return string|array<string, string> If opt is given, returns its value as a string. Otherwise, returns an associative array with the following elements (which correspond to opt): "url" "content_type" "http_code" "header_size" "request_size" "filetime" "ssl_verify_result" "redirect_count" "total_time" "namelookup_time" "connect_time" "pretransfer_time" "size_upload" "size_download" "speed_download" "speed_upload" "download_content_length" "upload_content_length" "starttransfer_time" "redirect_time"
     */
    public function getInfo(?int $opt = null)
    {
        return curl_getinfo($this->handle, $opt); // @phpstan-ignore-line
    }

    /**
     * @return string|int
     */
    public function getRequestInfo(int $opt)
    {
        return curl_getinfo($this->handle, $opt); // @phpstan-ignore-line
    }

    /**
     * @return string[]
     */
    public function getRequestInfos(): array
    {
        return curl_getinfo($this->handle);  // @phpstan-ignore-line
    }

    /**
     * Close the connexion
     * Call curl_reset function.
     */
    public function close(): void
    {
        curl_reset($this->handle);
    }
}
