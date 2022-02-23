<?php

declare(strict_types=1);

namespace PiedWeb\Curl\Test;

use PiedWeb\Curl\StaticRequest as Request;

class StaticWrapperTest extends \PHPUnit\Framework\TestCase
{
    public function testStaticGet()
    {
        $url = 'https://dev.piedweb.com/robots.txt';
        $result = Request::get($url);
        $this->assertTrue(\strlen($result) > 10);
    }
}
