<?php

declare(strict_types=1);

namespace PiedWeb\Curl\Test;

use PiedWeb\Curl\Helper;

class HelperTest extends \PHPUnit\Framework\TestCase
{

    public function testSchemeFromProxy()
    {
        $proxy = '75.157.242.104:59190';
        $this->assertSame('http://', Helper::getSchemeFrom($proxy));
    }

    public function testCheckContentType()
    {
        $line = 'Content-Type: text/html; charset=utf-8';
        $expected = 'text/html';
        $this->assertTrue(Helper::checkContentType($line, $expected));
    }
}
