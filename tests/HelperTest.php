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

}
