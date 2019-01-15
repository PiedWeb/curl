<?php

namespace PiedWeb\Curl;

trait StaticWrapperTrait
{
    public static function get(string $url)
    {
        $request = new self($url);
        $request
            ->setDefaultGetOptions()
            ->setDefaultSpeedOptions()
            ->setNoFollowRedirection()
            ->setDesktopUserAgent()
        ;

        $response = $request->exec();

        return is_int($response) ? $response : $response->getContent();
    }
}
