<?php

namespace PiedWeb\Curl;

class Request
{
    use UserAgentTrait, StaticWrapperTrait;

    const  RETURN_HEADER_ONLY = 2;
    const  RETURN_HEADER = 1;

    /**
     * Curl resource handle.
     *
     * @var resource
     */
    private $handle;

    /** @var string contains targeted URL */
    private $url;

    /** @var string contains current UA */
    private $userAgent;

    /** @var int */
    private $returnHeaders = 0;

    /** @var mixed */
    private $filter;

    /** @var bool */
    private $optChangeDuringRequest = false;

    /**
     * Constructor.
     *
     * @param string $ur to request
     */
    public function __construct(?string $url = null)
    {
        $this->handle = curl_init();
        $this->setOpt(CURLOPT_RETURNTRANSFER, 1);

        if (null !== $url) {
            $this->setUrl($url);
        }
    }

    public function getHandle()
    {
        return $this->handle;
    }

    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Change the URL to cURL.
     *
     * @param string $url to request
     *
     * @return self
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
        $this->setOpt(CURLOPT_URL, $url);

        return $this;
    }

    /**
     * Add a cURL's option.
     *
     * @param int   $option cURL Predefined Constant
     * @param mixed $value
     *
     * @return self
     */
    public function setOpt($option, $value)
    {
        curl_setopt($this->handle, $option, $value);

        return $this;
    }

    /**
     * A short way to set some classic options to cURL a web page.
     *
     * @return self
     */
    public function setDefaultGetOptions(
        $connectTimeOut = 5,
        $timeOut = 10,
        $dnsCacheTimeOut = 600,
        $followLocation = true,
        $maxRedirs = 5
    ) {
        $this
            ->setOpt(CURLOPT_AUTOREFERER, 1)
            ->setOpt(CURLOPT_FOLLOWLOCATION, $followLocation)
            ->setOpt(CURLOPT_MAXREDIRS, $maxRedirs)
            ->setOpt(CURLOPT_CONNECTTIMEOUT, $connectTimeOut)
            ->setOpt(CURLOPT_DNS_CACHE_TIMEOUT, $dnsCacheTimeOut)
            ->setOpt(CURLOPT_TIMEOUT, $timeOut)
             //->setOpt(CURLOPT_SSL_VERIFYPEER,    0);
        ;

        return $this;
    }

    /**
     * A short way to set some classic options to cURL a web page quickly
     * (but loosing some data like header, cookie...).
     *
     * @return self
     */
    public function setDefaultSpeedOptions()
    {
        $this->setOpt(CURLOPT_SSL_VERIFYHOST, 0);
        $this->setOpt(CURLOPT_SSL_VERIFYPEER, 0);

        if (!$this->returnHeaders) {
            $this->setOpt(CURLOPT_HEADER, 0);
        }

        $this->setDefaultGetOptions(5, 10, 600, true, 1);
        $this->setEncodingGzip();

        return $this;
    }

    /**
     * A short way to not follow redirection.
     *
     * @return self
     */
    public function setNoFollowRedirection()
    {
        return $this
            ->setOpt(CURLOPT_FOLLOWLOCATION, false)
            ->setOpt(CURLOPT_MAXREDIRS, 0)
        ;
    }

    /**
     * Call it if you want header informations.
     * After self::exec(), you would have this informations with getHeader();.
     *
     * @return self
     */
    public function setReturnHeader($only = false)
    {
        $this->setOpt(CURLOPT_HEADER, 1);
        $this->returnHeaders = $only ? self::RETURN_HEADER_ONLY : self::RETURN_HEADER;

        if ($only) {
            $this->setOpt(CURLOPT_RETURNTRANSFER, 0);
            $this->setOpt(CURLOPT_NOBODY, 1);
        }

        return $this;
    }

    public function mustReturnHeaders()
    {
        return $this->returnHeaders;
    }

    /**
     * An self::setOpt()'s alias to add a cookie to your request.
     *
     * @param string $cookie
     *
     * @return self
     */
    public function setCookie($cookie)
    {
        $this->setOpt(CURLOPT_COOKIE, $cookie);

        return $this;
    }

    /**
     * An self::setOpt()'s alias to add a referer to your request.
     *
     * @param string $referer
     *
     * @return self
     */
    public function setReferer($referer)
    {
        $this->setOpt(CURLOPT_REFERER, $referer);

        return $this;
    }

    /**
     * An self::setOpt()'s alias to add an user-agent to your request.
     *
     * @param string $ua
     *
     * @return self
     */
    public function setUserAgent(string $ua)
    {
        $this->userAgent = $ua;

        $this->setOpt(CURLOPT_USERAGENT, $ua);

        return $this;
    }

    public function getUserAgent()
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
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'POST');
        $this->setOpt(CURLOPT_POST, 1);
        $this->setOpt(CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);

        return $this;
    }

    /**
     * If you want to request the URL and hope get the result gzipped.
     * The output will be automatically uncompress with exec();.
     *
     * @return self
     */
    public function setEncodingGzip()
    {
        $this->setOpt(CURLOPT_ENCODING, 'gzip, deflate');

        return $this;
    }

    /**
     * If you want to request the URL with a (http|socks...) proxy (public or private).
     *
     * @param string $proxy [scheme]IP:PORT[:LOGIN:PASSWORD]
     *                      Eg. : socks5://98.023.023.02:1098:cUrlRequestProxId:SecretPassword
     *
     * @return self
     */
    public function setProxy(string $proxy)
    {
        $scheme = Helper::getSchemeFrom($proxy);
        $proxy = explode(':', $proxy);
        $this->setOpt(CURLOPT_HTTPPROXYTUNNEL, 1);
        $this->setOpt(CURLOPT_PROXY, $scheme.$proxy[0].':'.$proxy[1]);
        if (isset($proxy[2])) {
            $this->setOpt(CURLOPT_PROXYUSERPWD, $proxy[2].':'.$proxy[3]);
        }

        return $this;
    }

    /**
     * @param mixed $func function wich must return boolean
     *
     * @return self
     */
    public function setDownloadOnlyIf(callable $func)
    {
        $this->setReturnHeader();

        $this->filter = $func;
        $this->setOpt(CURLOPT_HEADERFUNCTION, [$this, 'checkHeader']);
        $this->setOpt(CURLOPT_NOBODY, 1);

        return $this;
    }

    /**
     * @param int $tooBig Default 2000000 = 2000 Kbytes = 2 Mo
     *
     * @return self
     */
    public function setAbortIfTooBig(int $tooBig = 2000000)
    {
        //$this->setOpt(CURLOPT_BUFFERSIZE, 128); // more progress info
        $this->setOpt(CURLOPT_NOPROGRESS, false);
        $this->setOpt(CURLOPT_PROGRESSFUNCTION, function ($ch, $totalBytes, $receivedBytes) use ($tooBig) {
            if ($receivedBytes > $tooBig) {
                return 1;
            }
        });

        return $this;
    }

    public function setDownloadOnly($range = '0-500')
    {
        $this->setOpt(CURLOPT_RANGE, $range);

        return $this;
    }

    public function checkHeader($handle, $line)
    {
        if (is_string($line)) {
            if (call_user_func($this->filter, $line)) {
                $this->optChangeDuringRequest = true;
                $this->setOpt(CURLOPT_NOBODY, 0);
            }
        }

        return strlen($line);
    }

    /**
     * Execute the request.
     *
     * @return Response|int corresponding to the curl error
     */
    public function exec($optChange = false)
    {
        $return = Response::get($this);

        // Permits to transform HEAD request in GET request
        if ($this->optChangeDuringRequest && false === $optChange) {
            $this->optChangeDuringRequest = true;

            return $this->exec(true);
        }

        if ($return instanceof Response) {
            $this->setReferer($return->getEffectiveUrl());
        }

        return $return;
    }

    /**
     * Return the last error number (curl_errno).
     *
     * @return int the error number or 0 (zero) if no error occurred
     */
    public function hasError()
    {
        return curl_errno($this->handle);
    }

    /**
     * Return a string containing the last error for the current session (curl_error).
     *
     * @return string the error message or '' (the empty string) if no error occurred
     */
    public function getError()
    {
        return curl_error($this->handle);
    }

    /**
     * Get information regarding the request.
     *
     * @param int $opt This may be one of the following constants:
     *                 http://php.net/manual/en/function.curl-getinfo.php
     *
     * @return array|string|false
     */
    public function getInfo(?int $opt)
    {
        return $opt ? curl_getinfo($this->handle, $opt) : curl_getinfo($this->handle);
    }

    /**
     * Close the connexion
     * Call curl_reset function.
     */
    public function close()
    {
        curl_reset($this->handle);
    }
}
