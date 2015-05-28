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
     * @param resource $stream The stream to clone.
     * @param bool     $close  Whether to close the input stream.
     *
     * @return resource The cloned stream.
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
        $meta = stream_get_meta_data($stream);

        return $meta['mode'][0] === 'a';
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
        $meta = stream_get_meta_data($stream);

        return $meta['mode'][0] === 'r' || isset($meta['mode'][1]) && $meta['mode'][1] === '+';
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
        $meta = stream_get_meta_data($stream);

        return !empty($meta['seekable']);
    }

    /**
     * Returns whether the stream is writable.
     *
     * This considers append mode as not writable, since seeking is undefined.
     *
     * @param resource $stream The stream.
     *
     * @return bool True if writable, false if not.
     */
    public static function isWritable($stream)
    {
        $meta = stream_get_meta_data($stream);

        if ($meta['mode'][0] === 'r') {
            return isset($meta['mode'][1]) && $meta['mode'][1] === '+';
        }

        return $meta['mode'][0] !== 'a';
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
        return static::isSeekable($stream) && fseek($stream, $offset, $whence) === 0;
    }
}
