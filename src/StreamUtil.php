<?php

namespace Twistor;

/**
 * Helper functions for dealing with streams.
 */
class StreamUtil
{
    /**
     * Copies a stream.
     *
     * @param resource $stream The stream to copy.
     * @param bool     $close  Whether to close the input stream.
     *
     * @return resource The copied stream.
     */
    public static function copy($stream, $close = true)
    {
        $cloned = fopen('php://temp', 'w+b');
        $pos = ftell($stream);

        static::tryRewind($stream);
        stream_copy_to_stream($stream, $cloned);

        if ($close) {
            fclose($stream);
        } else {
            static::trySeek($stream, $pos);
        }

        fseek($cloned, $pos);

        return $cloned;
    }

    /**
     * Returns a key from stream_get_meta_data().
     *
     * @param resource $stream The stream.
     * @param string   $key    The key to return.
     *
     * @return mixed The value from stream_get_meta_data().
     *
     * @see stream_get_meta_data()
     */
    public static function getMetaDataKey($stream, $key)
    {
        $meta = stream_get_meta_data($stream);

        return isset($meta[$key]) ? $meta[$key] : null;
    }

    /**
     * Returns the URI of a stream.
     *
     * @param resource $stream The stream.
     *
     * @return string|null The URI of the stream, or null if not set.
     */
    public static function getUri($stream)
    {
        return static::getMetaDataKey($stream, 'uri');
    }

    /**
     * Returns a URI that is usable via fopen().
     *
     * @param resource $stream The stream.
     *
     * @return string|false A usuable URI, or false on failure.
     */
    public static function getUsableUri($stream)
    {
        $uri = static::getMetaDataKey($stream, 'uri');

        return isset($uri) && $uri !== '' && file_exists($uri) ? $uri : false;
    }

    /**
     * Returns the size of a stream.
     *
     * If the size is 0, it could mean that the stream isn't reporting its size.
     *
     * @param resource $stream The stream.
     *
     * @return int The size of the stream.
     */
    public static function getSize($stream)
    {
        $stat = fstat($stream);

        return $stat['size'];
    }

    /**
     * Returns whether the stream is in append mode.
     *
     * @param resource $stream The stream.
     *
     * @return bool True if appendable, false if not.
     */
    public static function isAppendable($stream)
    {
        return static::modeIsAppendable(static::getMetaDataKey($stream, 'mode'));
    }

    /**
     * Returns whether the stream is readable.
     *
     * @param resource $stream The stream.
     *
     * @return bool True if readable, false if not.
     */
    public static function isReadable($stream)
    {
        return static::modeIsReadable(static::getMetaDataKey($stream, 'mode'));
    }

    /**
     * Returns whether the stream is seekable.
     *
     * @param resource $stream The stream.
     *
     * @return bool True if seekable, false if not.
     */
    public static function isSeekable($stream)
    {
        return (bool) static::getMetaDataKey($stream, 'seekable');
    }

    /**
     * Returns whether the stream is writable.
     *
     * @param resource $stream The stream.
     *
     * @return bool True if writable, false if not.
     */
    public static function isWritable($stream)
    {
        return static::modeIsWritable(static::getMetaDataKey($stream, 'mode'));
    }

    /**
     * Returns whether a mode is appendable.
     *
     * @param string $mode The mode string.
     *
     * @return bool True if appendable, false if not.
     */
    public static function modeIsAppendable($mode)
    {
        return $mode[0] === 'a';
    }

    /**
     * Returns whether a mode is append only.
     *
     * @param string $mode The mode string.
     *
     * @return bool True if append only, false if not.
     */
    public static function modeIsAppendOnly($mode)
    {
        return $mode[0] === 'a' && strpos($mode, '+') === false;
    }

    /**
     * Returns whether a mode is readable.
     *
     * @param string $mode The mode string.
     *
     * @return bool True if readable, false if not.
     */
    public static function modeIsReadable($mode)
    {
        return $mode[0] === 'r' || strpos($mode, '+') !== false;
    }

    /**
     * Returns whether a mode is read only.
     *
     * @param string $mode The mode string.
     *
     * @return bool True if read only, false if not.
     */
    public static function modeIsReadOnly($mode)
    {
        return $mode[0] === 'r' && strpos($mode, '+') === false;
    }

    /**
     * Returns whether a mode is writable.
     *
     * @param string $mode The mode string.
     *
     * @return bool True if writable, false if not.
     */
    public static function modeIsWritable($mode)
    {
        return !static::modeIsReadOnly($mode);
    }

    /**
     * Returns whether a mode is write only.
     *
     * @param string $mode The mode string.
     *
     * @return bool True if write only, false if not.
     */
    public static function modeIsWriteOnly($mode)
    {
        return static::modeIsWritable($mode) && !static::modeIsReadable($mode);
    }

    /**
     * Tries to rewind a stream.
     *
     * @param resource $stream The stream.
     *
     * @return bool True on success, false on failure.
     *
     * @see rewind()
     */
    public static function tryRewind($stream)
    {
        return ftell($stream) === 0 || static::isSeekable($stream) && rewind($stream);
    }

    /**
     * Tries to seek a stream.
     *
     * @param resource $stream The stream.
     * @param int      $offset The offset.
     * @param int      $whence One of SEEK_SET, SEEK_CUR, SEEK_END.
     *
     * @return bool True on success, false on failure.
     *
     * @see fseek()
     */
    public static function trySeek($stream, $offset, $whence = SEEK_SET)
    {
        $offset = (int) $offset;

        // If SEEK_SET, we can avoid a seek if we're at the right location.
        if ($whence === SEEK_SET && ftell($stream) === $offset) {
            return true;
        }

        return static::isSeekable($stream) && fseek($stream, $offset, $whence) === 0;
    }
}
