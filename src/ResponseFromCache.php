<?php

namespace PiedWeb\Curl;

class ResponseFromCache extends Response
{
    /** @var string */
    protected $url;

    /**
     * @param mixed                     $headersSeparator could be a string (the separator between headers and content or FALSE
     * @param array<string, int|string> $info
     */
    public function __construct(
        string $filePathOrContent,
        ?string $url = null,
        array $info = [],
        $headersSeparator = \PHP_EOL.\PHP_EOL
    ) {
        $content = file_exists($filePathOrContent) ? file_get_contents($filePathOrContent) : $filePathOrContent;

        if (! $content) {
            throw new \Exception($filePathOrContent.' doesn\'t exist');
        }

        if (false !== $headersSeparator && \is_string($headersSeparator) && '' !== $headersSeparator) {
            list($this->headers, $this->content) = explode($headersSeparator, $content, 2);
        } else {
            $this->content = $content;
        }

        $this->info = $info;
        $this->url = (string) $url;
    }

    public function getRequest(): ?Request
    {
        return null; // todo serialize request to return it even from cache
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getEffectiveUrl(): ?string
    {
        return $this->url;
    }

    public function getStatusCode(): int
    {
        if ($this->headers) {
            $headers = $this->getHeaders();

            return (int) explode(' ', $headers[0], 2)[1]; // @phpstan-ignore-line
        }

        return (int) $this->getInfo('http_code');
    }

    /**
     * @psalm-suppress InvalidReturnStatement
     * @psalm-suppress NullableReturnStatement
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidNullableReturnType
     */
    public function getContentType(): string
    {
        $headers = $this->getHeaders();
        if (null !== $headers && isset($headers['content-type'])) {
            return $headers['content-type']; // @phpstan-ignore-line
        }

        return $this->getInfo('content_type');  // @phpstan-ignore-line
    }
}
