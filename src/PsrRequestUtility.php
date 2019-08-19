<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2019 Niklas Ekman
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

declare(strict_types=1);

namespace Nekman\AwsRingHttpSigner;

/**
 * @internal
 */
class PsrRequestUtility
{
    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
        // Only static methods.
    }
    
    /**
     * Get the URL for a PSR-7 request from a Ring request
     *
     * @param array $ringRequest The Ring request to get the URL of
     * @return string|null A URL or null if it does not exist
     */
    public static function getUrl(array $ringRequest): ?string
    {
        $host = self::getFirstHost($ringRequest);
        
        if (empty($host)) {
            return null;
        }
        
        if (! isset($ringRequest["scheme"]) || empty($ringRequest["scheme"])) {
            return null;
        }
        
        return "{$ringRequest['scheme']}://{$host}{$ringRequest['uri']}";
    }
    
    /**
     * Get the host from the headers of a Ring request
     *
     * @param array $ringRequest The Ring request to get the host of
     * @return string|null A host or null if it does not exist
     */
    public static function getFirstHost(array $ringRequest): ?string
    {
        $hosts = $ringRequest["headers"]["Host"] ?? $ringRequest["headers"]["host"] ?? null;
        
        if (empty($hosts)) {
            return null;
        }
        
        if (! is_array($hosts)) {
            return $hosts;
        }
        
        return $hosts[0];
    }
}
