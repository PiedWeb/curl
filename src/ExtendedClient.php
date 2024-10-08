<?php

namespace PiedWeb\Curl;

class ExtendedClient extends Client
{
    use UserAgentTrait;

    private ?string $userAgent = null;

    final public const string DEFAULT_USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36	';

    private bool $fakeBrowserHeader = false;

    public ?string $referer = null;

    public ?string $cookie = null;

    private string $language = 'fr-FR,fr;q=0.9';

    /**
     * @var callable
     *
     * @psalm-suppress PropertyNotSetInConstructor */
    private $filter;

    private int $optChangeDuringRequest = 0;

    /**
     * A short way to set some classic options to cURL a web page.
     */
    public function setDefaultGetOptions(
        int $connectTimeOut = 10,
        int $timeOut = 20,
        int $dnsCacheTimeOut = 900,
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
     * A short way to set some classic options to cURL a web page quickly.
     */
    public function setDefaultSpeedOptions(
        int $connectTimeOut = 5,
        int $timeOut = 10,
        int $dnsCacheTimeOut = 900
    ): self {
        $this->setOpt(\CURLOPT_SSL_VERIFYHOST, 0);
        $this->setOpt(\CURLOPT_SSL_VERIFYPEER, 0);
        $this->setDefaultGetOptions($connectTimeOut, $timeOut, $dnsCacheTimeOut, true, 1);
        $this->setEncodingGzip();

        return $this;
    }

    /**
     * Use it in last once.
     */
    public function fakeBrowserHeader(bool $doIt = true): self
    {
        $this->fakeBrowserHeader = $doIt;

        return $this;
    }

    private function setBrowserHeader(): void
    {
        $this->setOpt(\CURLOPT_HTTPHEADER, array_filter([
            'Sec-Ch-Ua: "Chromium";v="116", "Not)A;Brand";v="24", "Google Chrome";v="116"',
            'Sec-Ch-Ua-Mobile: ?0',
            'Sec-Ch-Ua-Platform: "Windows"',
            'Upgrade-Insecure-Requests: 1',
            null !== $this->userAgent ? 'User-Agent: '.$this->getUserAgent() : self::DEFAULT_USER_AGENT,
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Sec-Fetch-Site: none',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-User: ?1',
            'Sec-Fetch-Dest: document',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: '.$this->language,
            null !== $this->referer ? 'Referer: '.$this->referer : '',
            null !== $this->cookie ? 'Cookie: '.$this->cookie : '',
        ]));
    }

    /**
     * A short way to not follow redirection.
     */
    public function setNoFollowRedirection(): self
    {
        $this->setOpt(\CURLOPT_FOLLOWLOCATION, false);
        $this->setOpt(\CURLOPT_MAXREDIRS, 0);

        return $this;
    }

    public function setReturnOnlyHeader(): self
    {
        $this->setOpt(\CURLOPT_NOBODY, 1);

        return $this;
    }

    /**
     * An self::setOpt()'s alias to add a cookie to your request.
     */
    public function setCookie(?string $cookie): self
    {
        $this->cookie = $cookie;
        $this->setOpt(\CURLOPT_COOKIE, $cookie);

        return $this;
    }

    /**
     * An self::setOpt()'s alias to add a referer to your request.
     */
    public function setReferer(string $referer): self
    {
        $this->referer = $referer;
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
        return $this->userAgent ?? throw new \Exception('userAgent must be setted');
    }

    /**
     * A short way to set post's options to cURL a web page.
     *
     * @param mixed $post if it's an array, will be converted via http build query
     */
    public function setPost(mixed $post): self
    {
        $this->setOpt(\CURLOPT_CUSTOMREQUEST, 'POST');
        $this->setOpt(\CURLOPT_POST, 1);
        $this->setOpt(\CURLOPT_POSTFIELDS, \is_array($post) ? http_build_query($post) : $post);

        return $this;
    }

    /**
     * If you want to request the URL and hope get the result gzipped.
     * The output will be automatically uncompress with request();.
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
     *
     * @psalm-suppress RedundantCast
     */
    public function setProxy(string $proxy): self
    {
        if ('' === $proxy) {
            $this->setOpt(\CURLOPT_PROXY, '');

            return $this;
        }

        $scheme = Helper::getSchemeFrom($proxy);
        $proxyArr = explode(':', (string) $proxy);
        $this->setOpt(\CURLOPT_HTTPPROXYTUNNEL, 1);
        $this->setOpt(\CURLOPT_PROXY, $scheme.$proxyArr[0].':'.$proxyArr[1]);
        if (isset($proxyArr[2])) {
            $this->setOpt(\CURLOPT_PROXYUSERPWD, $proxyArr[2].':'.$proxyArr[3]);
        }

        return $this;
    }

    /**
     * @param callable $func function wich must return boolean
     */
    public function setDownloadOnlyIf(callable $func): self
    {
        $this->error = 92832;
        $this->errorMessage = 'Aborted because user check in headers';

        $this->filter = $func;
        $this->setOpt(\CURLOPT_HEADERFUNCTION, $this->checkHeader(...));
        $this->setOpt(\CURLOPT_NOBODY, 1);

        return $this;
    }

    /**
     * @param int $maxBytes Default 2000000 = 2000 Kbytes = 2 Mo
     *
     * @psalm-suppress UnusedClosureParam
     */
    public function setMaximumResponseSize(int $maxBytes = 2_000_000): self
    {
        // $this->setOpt(CURLOPT_BUFFERSIZE, 128); // more progress info
        $this->setOpt(\CURLOPT_NOPROGRESS, false);
        /** @psalm-suppress MissingClosureParamType */
        $this->setOpt(\CURLOPT_PROGRESSFUNCTION, static function ($handle, $totalBytes, $receivedBytes) use ($maxBytes) {
            if ($totalBytes > $maxBytes) {
                return 1;
            }

            if ($receivedBytes > $maxBytes) {
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

    public function checkHeader(\CurlHandle $handle, string $line): int
    {
        if (\call_user_func($this->filter, $line)) {
            $this->resetError();
            ++$this->optChangeDuringRequest;
            $this->setOpt(\CURLOPT_NOBODY, 0);
            // $this->setOpt(\CURLOPT_HEADERFUNCTION, false); // only required if we implement multi-check
        }

        return \strlen($line);
    }

    /**
     * Execute the request.
     */
    public function request(?string $target = null, bool $updateRefererAndCookies = true): bool
    {
        if ($this->fakeBrowserHeader) {
            $this->setBrowserHeader();
        }

        $request = parent::request($target);

        // if ($this->getError() && !in_array($this->getError(), [18], true)) { return false; }

        // Permits to transform HEAD request in GET request
        if (1 === $this->optChangeDuringRequest) {
            return $this->request();
        }

        $this->optChangeDuringRequest = 0;

        if ($updateRefererAndCookies && ($effectiveUrl = $this->getResponse()->getUrl()) !== null) {
            $this->setReferer($effectiveUrl);
        }

        if (! $updateRefererAndCookies) {
            return $request;
        }

        if (($cookies = $this->getResponse()->getCookies()) === null) {
            return $request;
        }

        $this->setCookie($cookies);

        return $request;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }

    /**
     * Set the value of language.
     */
    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }
}
