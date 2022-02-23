<?php

namespace PiedWeb\Curl;

class StaticRequest
{

    private static ?Request $request = null;

    public static function resetRequest(): void
    {
        self::$request = null;

    }
    public static function  getRequest(): ?Request
    {
        return self::$request;
    }

    public static function get(string $url): ?string
    {
        self::$request = self::$request ?? new Request();

        return self::$request
            ->setUrl($url)
            ->setDefaultGetOptions()
            ->setDefaultSpeedOptions()
            ->setNoFollowRedirection()
            ->setDesktopUserAgent()
            ->getContent();
    }
}