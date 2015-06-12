<?php

namespace Twistor\Tests;

use Twistor\StreamUtil;

class StreamUtilTest extends \PHPUnit_Framework_TestCase {

    protected $stream;

    public function setUp()
    {
        $this->stream = fopen('data://text/plain,aaaaaaaaaa', 'r+');
    }

    public function tearDown()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    public function testClone()
    {
        fseek($this->stream, 2);

        $cloned = StreamUtil::copy($this->stream, false);

        // Test seeking, and not closing.
        $this->assertSame(2, ftell($cloned));
        $this->assertSame(ftell($this->stream), ftell($cloned));

        // Test auto-closing.
        $cloned = StreamUtil::copy($this->stream);

        $this->assertSame(2, ftell($cloned));
        $this->assertFalse(is_resource($this->stream));
    }

    public function testGetUri()
    {
        $this->assertSame('data://text/plain,aaaaaaaaaa', StreamUtil::getUri($this->stream));
    }

    public function testGetUsuableUri()
    {
        $this->assertFalse(StreamUtil::getUsableUri($this->stream));

        // Test memory streams.
        $handle = fopen('php://temp', 'w+b');
        $this->assertFalse(StreamUtil::getUsableUri($this->stream));
        fclose($handle);


        // A real file. We can't use tmpfile() since HHVM doesn't set the URI.
        $file = tempnam(sys_get_temp_dir(), 'php-stream-util-');
        $handle = fopen($file, 'w');

        $this->assertSame(StreamUtil::getUri($handle), StreamUtil::getUsableUri($handle));

        fclose($handle);
        unlink($file);
    }

    public function testGetSize()
    {
        // fstat() doesn't work for data streams in HHVM.
        $stream = fopen('php://temp', 'w+b');
        $this->assertSame(0, StreamUtil::getSize($stream));

        fwrite($stream, 'aaaaaaaaaa');
        $this->assertSame(10, StreamUtil::getSize($stream));
        fclose($stream);
    }

    public function testIsAppendable()
    {
        $this->assertFalse(StreamUtil::isAppendable($this->stream));

        $appendable = fopen('data://text/plain,aaaaaaaaaa', 'a');
        $this->assertTrue(StreamUtil::isAppendable($appendable));
        fclose($appendable);
    }

    public function testIsReadable()
    {
        $this->assertTrue(StreamUtil::isReadable($this->stream));

        $readable = fopen('data://text/plain,aaaaaaaaaa', 'r');
        $this->assertTrue(StreamUtil::isReadable($readable));
        fclose($readable);


        $readable = fopen('data://text/plain,aaaaaaaaaa', 'w+');
        $this->assertTrue(StreamUtil::isReadable($readable));
        fclose($readable);

        $not_readable = fopen('data://text/plain,aaaaaaaaaa', 'a');
        $this->assertFalse(StreamUtil::isReadable($not_readable));
        fclose($not_readable);
    }

    public function testIsWritable()
    {
        $this->assertTrue(StreamUtil::isWritable($this->stream));

        $appendable = fopen('data://text/plain,aaaaaaaaaa', 'a');
        $this->assertTrue(StreamUtil::isWritable($appendable));
        fclose($appendable);

        $not_writable = fopen('data://text/plain,aaaaaaaaaa', 'r');
        $this->assertFalse(StreamUtil::isWritable($not_writable));
        fclose($not_writable);
    }

    public function testTryRewind()
    {
        $this->assertTrue(StreamUtil::tryRewind($this->stream));
        $this->assertSame(0, ftell($this->stream));

        fseek($this->stream, 1);
        $this->assertTrue(StreamUtil::tryRewind($this->stream));
        $this->assertSame(0, ftell($this->stream));
    }

    public function testTrySeek()
    {
        $this->assertTrue(StreamUtil::trySeek($this->stream, 0));
        $this->assertSame(0, ftell($this->stream));

        $this->assertTrue(StreamUtil::trySeek($this->stream, 10));
        $this->assertSame(10, ftell($this->stream));

        $this->assertFalse(StreamUtil::trySeek($this->stream, 100));
        $this->assertFalse(ftell($this->stream));

        // Rewind.
        $this->assertTrue(StreamUtil::trySeek($this->stream, 0));

        $this->assertTrue(StreamUtil::trySeek($this->stream, 0, SEEK_END));
        $this->assertSame(10, ftell($this->stream));
    }

    public function testModeIsAppendOnly()
    {
        $this->assertTrue(StreamUtil::modeIsAppendOnly('a'));
        $this->assertTrue(StreamUtil::modeIsAppendOnly('ab'));
        $this->assertFalse(StreamUtil::modeIsAppendOnly('a+'));
        $this->assertFalse(StreamUtil::modeIsAppendOnly('w'));
        $this->assertFalse(StreamUtil::modeIsAppendOnly('rb'));
    }

    public function testModeIsReadOnly()
    {
        $this->assertTrue(StreamUtil::modeIsReadOnly('r'));
        $this->assertFalse(StreamUtil::modeIsReadOnly('r+'));
        $this->assertFalse(StreamUtil::modeIsReadOnly('w'));
        $this->assertFalse(StreamUtil::modeIsReadOnly('w+b'));
    }

    public function testModeIsWriteOnly()
    {
        $this->assertTrue(StreamUtil::modeIsWriteOnly('wb'));
        $this->assertTrue(StreamUtil::modeIsWriteOnly('c'));
        $this->assertTrue(StreamUtil::modeIsWriteOnly('xt'));
        $this->assertTrue(StreamUtil::modeIsWriteOnly('a'));
        $this->assertFalse(StreamUtil::modeIsWriteOnly('w+'));
        $this->assertFalse(StreamUtil::modeIsWriteOnly('r'));
        $this->assertFalse(StreamUtil::modeIsWriteOnly('r+b'));
        $this->assertFalse(StreamUtil::modeIsWriteOnly('w+'));
        $this->assertFalse(StreamUtil::modeIsWriteOnly('c+'));
        $this->assertFalse(StreamUtil::modeIsWriteOnly('a+t'));
    }
}
