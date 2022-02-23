<?php

namespace PiedWeb\Curl;

trait StaticWrapperTrait
{
    /**
     * @return int|string
     */
    public static function get(string $url)
    {
        $request = new Request($url);
        $request
            ->setDefaultGetOptions()
            ->setDefaultSpeedOptions()
            ->setNoFollowRedirection()
            ->setDesktopUserAgent()
        ;

        $response = $request->exec();

        return \is_int($response) ? $response : $response->getContent();
    }
}
