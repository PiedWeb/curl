<p align="center"><a href="https://dev.piedweb.com">
<img src="https://raw.githubusercontent.com/PiedWeb/piedweb-devoluix-theme/master/src/img/logo_title.png" width="200" height="200" alt="Open Source Package" />
</a></p>

# Curl OOP Wrapper

[![Latest Version](https://img.shields.io/github/tag/PiedWeb/Curl.svg?style=flat&label=release)](https://github.com/PiedWeb/Curl/tags)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](https://github.com/PiedWeb/Curl/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/PiedWeb/Curl/master.svg?style=flat)](https://travis-ci.org/PiedWeb/Curl)
[![Quality Score](https://img.shields.io/scrutinizer/g/PiedWeb/Curl.svg?style=flat)](https://scrutinizer-ci.com/g/PiedWeb/Curl)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/PiedWeb/Curl.svg?style=flat)](https://scrutinizer-ci.com/g/PiedWeb/Curl/code-structure)
[![Total Downloads](https://img.shields.io/packagist/dt/piedweb/curl.svg?style=flat)](https://packagist.org/packages/piedweb/curl)

Simple PHP Curl OOP wrapper for efficient request.

For a more complex or abstracted curl wrapper, use [Guzzle](https://guzzle.readthedocs.io/en/latest/).

## Install

Via [Packagist](https://img.shields.io/packagist/dt/piedweb/curl.svg?style=flat)

``` bash
$ composer require piedweb/curl
```

## Usage

Quick Example :
``` php
$url = 'https://piedweb.com';
$request = new Request($url);
$request
    ->setDefaultSpeedOptions(true)
    ->setDownloadOnlyIf('text/html')
    ->setDestkopUserAgent()
;
$result = $request->exec();
if ($result instanceof \PiedWeb\Curl\Response) {
    $content = $this->getContent();
}
```

All methods :
``` php
use PiedWeb\Curl\Request;

$r = new CurlRequest(?string $url);
$r
    ->setOpt(CURLOPT_*, mixed 'value')

	// Preselect Options to avoid eternity wait
    ->setDefaultGetOptions($connectTimeOut = 5, $timeOut = 10, $dnsCacheTimeOut = 600, $followLocation = true, $maxRedirs = 5)
    ->setDefaultSpeedOptions(bool $cookie = false) // no header except if setted, no cookie, 1 redir max, no ssl check

    ->setReturnHeader($only = false)
    ->setCookie(string $cookie)
    ->setReferer(string $url)

    ->setUserAgent(string $ua)
    ->setDestkopUserAgent()
    ->setMobileUserAgent()
    ->setLessJsUserAgent()

    ->setDownloadOnlyIf($ContentType = ['html', 'jpg']) // @param $ContentType can be a String or an Array

    ->setPost(array $post)

    ->setEncodingGzip()

    ->setProxy(string '[scheme]proxy-host:port[:username:passwrd]') // Scheme, username and passwrd are facultatives. Default Scheme is http://

    ->setUrl($url, $resetPreviousOptions)

$requested = $r->exec(); // @return PiedWeb\Curl\Requested or int corresponding to the curl error

$requested->getContent(); // @return string
$requested->getHeader($returnArray = true); // @return array Response Header (or in a string if $returnArray is set to false)
$requested->getCookies(); // @return string
$requested->getEffectiveUrl(); // @return string

$r->hasError|getError|getInfo(); // Equivalent to curl function curl_errno|curl_error|curl_getinfo();
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing](https://dev.piedweb.com/contributing)

## Credits

- [PiedWeb](https://piedweb.com)
- [All Contributors](https://github.com/PiedWeb/:package_skake/graphs/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[![Latest Version](https://img.shields.io/github/tag/PiedWeb/Curl.svg?style=flat&label=release)](https://github.com/PiedWeb/Curl/tags)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](https://github.com/PiedWeb/Curl/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/PiedWeb/Curl/master.svg?style=flat)](https://travis-ci.org/PiedWeb/Curl)
[![Quality Score](https://img.shields.io/scrutinizer/g/PiedWeb/Curl.svg?style=flat)](https://scrutinizer-ci.com/g/PiedWeb/Curl)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/PiedWeb/Curl.svg?style=flat)](https://scrutinizer-ci.com/g/PiedWeb/Curl/code-structure)
[![Total Downloads](https://img.shields.io/packagist/dt/piedweb/curl.svg?style=flat)](https://packagist.org/packages/piedweb/curl)
