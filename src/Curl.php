<?php

namespace PiedWeb\Curl;
use CurlHandle;

class Curl
{
    private ?CurlHandle $handle = null;

    public function getHandle(): CurlHandle
    {
        if ($this->handle === null)
            $this->handle =\Safe\curl_init();

        return $this->handle;
    }

    /**
     * Add a cURL's option.
     *
     * @param int   $option cURL Predefined Constant
     * @param mixed $value
     * @psalm-suppress InvalidArgument (for $handle)
     */
    public function setOpt(int $option, $value): self
    {
        curl_setopt($this->getHandle(), $option, $value);

        return $this;
    }

    /**
     * Get information regarding the request.
     *
     * @param ?int $opt This may be one of the following constants:
     *                 http://php.net/manual/en/function.curl-getinfo.php
     *
     * @return string|array<string, string> If opt is given, returns its value as a string. Otherwise, returns an associative array with the following elements (which correspond to opt): "url" "content_type" "http_code" "header_size" "request_size" "filetime" "ssl_verify_result" "redirect_count" "total_time" "namelookup_time" "connect_time" "pretransfer_time" "size_upload" "size_download" "speed_download" "speed_upload" "download_content_length" "upload_content_length" "starttransfer_time" "redirect_time"
     * @psalm-suppress InvalidArgument (for $handle)
     */

    public function getCurlInfo(?int $opt = null)
    {
        return curl_getinfo($this->getHandle(), $opt); // @phpstan-ignore-line
    }

    /**
     * Close the connexion
     * Call curl_reset function.
     *
     * @psalm-suppress InvalidArgument (for $handle)
     */
    public function close(): void
    {
        curl_reset($this->getHandle());
    }


    /**
     * Return the last error number (curl_errno).
     *
     * @return int the error number or 0 (zero) if no error occurred
     * @psalm-suppress InvalidArgument (for $handle)
     */
    public function hasError(): int
    {
        return curl_errno($this->getHandle());
    }

    /**
     * Return a string containing the last error for the current session (curl_error).
     *
     * @return string the error message or '' (the empty string) if no error occurred
     * @psalm-suppress InvalidArgument (for $handle)
     */
    public function getError(): string
    {
        return curl_error($this->getHandle());
    }
}
