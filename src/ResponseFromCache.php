<?php

namespace PiedWeb\Curl;

class ResponseFromCache extends Response
{
    /** @var string */
    protected $url;

    /**
     * @param string $filePath
     * @param mixed  $headers  could be a string (the separator between headers and content or FALSE
     */
    public function __construct(
        string $filePathOrContent,
        ?string $url = null,
        array $info = [],
        $headers = PHP_EOL.PHP_EOL
    ) {
        $content = file_exists($filePathOrContent) ? file_get_contents($filePathOrContent) : $filePathOrContent;

        if (! $content) {
            throw new \Exception($filePathOrContent.' doesn\'t exist');
        }

        if (false !== $headers) {
            list($this->headers, $this->content) = explode($headers, $content, 2);
        } else {
            $this->content = $content;
        }

        $this->info = $info;
        $this->url = $url;
    }

    public function getRequest()
    {
        return null;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getEffectiveUrl(): ?string
    {
        return $this->url;
    }

    public function getStatusCode()
    {
        if ($this->headers) {
            $headers = $this->getHeaders();

            return explode(' ', $headers[0], 2)[1];
        }

        return $this->getInfo('http_code');
    }

    public function getContentType()
    {
        if ($this->headers) {
            $headers = $this->getHeaders();
            if (isset($headers['content-type'])) {
                return $headers['content-type'];
            }
        }

        return $this->getInfo('content_type');
    }
}
