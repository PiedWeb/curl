<?php

declare(strict_types=1);

namespace PiedWeb\Curl\Test;

use PiedWeb\Curl\Request;

class RequestTest extends \PHPUnit\Framework\TestCase
{

    public function testDownloadIfHtml()
    {
        $url = 'https://piedweb.com/';
        $request = new Request($url);
        $request
            ->setDefaultGetOptions()
            ->setDownloadOnlyIf('html')
            ->setReturnHeader()
            ->setDestkopUserAgent()
            ->setEncodingGzip()
        ;
        $result = $request->exec();

        if ($result instanceof \PiedWeb\Curl\Response) {
            $this->assertSame(200, $result->getStatusCode());

            $headers = $result->getHeaders();
            $this->assertTrue(is_array($headers));

            $this->assertSame('text/html; charset=UTF-8', $result->getContentType());
            $this->assertTrue(strlen($result->getContent())>10);
        } else {
            var_dump($request->getError());
            $this->assertTrue(is_int($result));
        }

    }

    public function testNotDownload()
    {
        $url = 'https://piedweb.com/assets/img/xl/bg.jpg';
        $request = new Request($url);
        $request
            ->setDefaultGetOptions()
            ->setDownloadOnlyIf('html')
            ->setReturnHeader()
            ->setDestkopUserAgent()
            ->setEncodingGzip()
        ;
        $result = $request->exec();

        if ($result instanceof \PiedWeb\Curl\Response) {
            $this->assertSame(200, $result->getStatusCode());
            $this->assertSame('', $result->getContent());
        } else {
            var_dump($request->getError());
            $this->assertTrue(is_int($result));
        }
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

        if ($result instanceof \PiedWeb\Curl\Response) {
            $this->assertSame('https://piedweb.com/', $result->getEffectiveUrl());
            $this->assertSame($url, $result->getUrl());
            $this->assertTrue(strlen($result->getContent())>10);
        } else {
            var_dump($request->getError());
            $this->assertTrue(is_int($result));
        }

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

        if ($result instanceof \PiedWeb\Curl\Response) {
            $this->assertSame(404, $result->getStatusCode());
        } else {
            var_dump($request->getError());
            $this->assertTrue(is_int($result));
        }
    }

    public function testAllMethods()
    {
        $url = 'https://piedweb.com';
        $request = new Request($url);
        $request
            ->setDefaultGetOptions()
            ->setDefaultSpeedOptions()
            ->setCookie('hello=1')
            ->setReferer('https://piedweb.com')
            ->setUserAgent('Hello :)')
            ->setDestkopUserAgent()
            ->setMobileUserAgent()
            ->setLessJsUserAgent()
            ->setDownloadOnlyIf($ContentType = ['html', 'jpg']) // @param $ContentType can be a String or an Array
            ->setUrl($url)
        ;

        $result = $request->exec();
        if ($result instanceof \PiedWeb\Curl\Response) {
            $this->assertSame(200, $result->getStatusCode());

            $headers = $result->getHeaders();
            $this->assertTrue(is_array($headers));

            $this->assertSame('text/html; charset=UTF-8', $result->getContentType());

        } else {
            var_dump($request->getError());
            $this->assertTrue(is_int($result));
        }
    }
}
