<?php

declare(strict_types=1);

namespace PiedWeb\Curl\Test;

use PiedWeb\Curl\Request;

class RequestTest extends \PHPUnit\Framework\TestCase
{

    public function testNotDownload()
    {
        $url = 'https://www.google.fr/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png';//'https://piedweb.com/assets/img/xl/bg.jpg';
        $request = new Request($url);
        $request
            ->setDefaultGetOptions()
            ->setDownloadOnlyIf('html')
            ->setReturnHeader()
            ->setDestkopUserAgent()
            ->setEncodingGzip()
        ;
        $result = $request->exec();

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('', $result->getContent());
    }

    public function testEffectiveUrl()
    {
        $url = 'http://www.piedweb.com/';
        $request = new Request($url);
        $request
            ->setDefaultGetOptions()
            ->setDownloadOnlyIf('html')
            ->setReturnHeader()
            ->setDestkopUserAgent()
            ->setEncodingGzip()
        ;
        $result = $request->exec();

        $this->assertSame('https://piedweb.com/', $result->getEffectiveUrl());
        $this->assertSame($url, $result->getUrl());
        $this->assertTrue(strlen($result->getContent())>10);

    }


    public function testCurlError()
    {
        $url = 'http://www.readze'.rand(100000,99999999).'.com/';
        $request = new Request($url);
        $request
            ->setDefaultGetOptions()
            ->setReturnHeader()
            ->setDestkopUserAgent()
            ->setEncodingGzip()
        ;
        $result = $request->exec();

        $this->assertSame(6, $result);

    }

    public function test404()
    {
        $url = 'https://piedweb.com/404-error';
        $request = new Request($url);
        $request
            ->setDefaultGetOptions()
            ->setDownloadOnlyIf('html')
            ->setDestkopUserAgent()
            ->setEncodingGzip()
        ;
        $result = $request->exec();

        $this->assertSame(404, $result->getStatusCode());
    }
}
