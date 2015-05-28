# Stream Utilities

[![Author](http://img.shields.io/badge/author-@chrisleppanen-blue.svg?style=flat-square)](https://twitter.com/chrisleppanen)
[![Build Status](https://img.shields.io/travis/twistor/stream-util/master.svg?style=flat-square)](https://travis-ci.org/twistor/stream-util)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/twistor/stream-util.svg?style=flat-square)](https://scrutinizer-ci.com/g/twistor/stream-util/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/twistor/stream-util.svg?style=flat-square)](https://scrutinizer-ci.com/g/twistor/stream-util)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/twistor/stream-util.svg?style=flat-square)](https://packagist.org/packages/twistor/stream-util)

Helper functions for dealing with streams.

## Installation

```
composer require twistor/stream-util
```

## Usage

```php
use Twistor\StreamUtil;

$stream = fopen('php://temp', 'w+b');

fwrite($stream, 'asdfasfdas');

$cloned = StreamUtil::copy($stream, false); // Passing in true (the default), will close the input stream.

$size = StreamUtil::getSize($stream); // == 10

$appendable = StreamUtil::isAppendable($stream); // == false

$readable = StreamUtil::isReadable($stream); // == true

$seekable = StreamUtil::isSeekable($stream); // == true

$writable = StreamUtil::isWritable($stream); // == true

$success = StreamUtil::tryRewind($stream); // == true

$success = StreamUtil::trySeek($stream, 0, SEEK_END); // == true
```
