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

use Aws\Signature\SignatureInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Ring\Future\FutureArray;
use GuzzleHttp\Ring\Future\FutureArrayInterface;
use Nekman\AwsRingHttpSigner\Contract\AwsRingHttpSignerInterface;
use Psr\Http\Message\RequestInterface;

class AwsRingHttpSigner implements AwsRingHttpSignerInterface
{
    /** @var SignatureInterface */
    private $signature;
    
    /** @var callable */
    private $credentialsProvider;
    
    public function __construct(SignatureInterface $signature, callable $credentialsProvider)
    {
        $this->signature = $signature;
        $this->credentialsProvider = $credentialsProvider;
    }
    
    public function __invoke(callable $handler): callable
    {
        return function (array $request) use ($handler) : FutureArrayInterface {
            // Fetch the AWS credentials
            $credentials = call_user_func($this->credentialsProvider)->wait();
            
            // Sign the request using the AWS credentials
            $psrRequest = $this->convertRingToPsr($request);
            $signedPsrRequest = $this->signature->signRequest($psrRequest, $credentials);
            
            // Convert the request back to Ring HTTP and continue
            $request = $this->convertPsrToRing($signedPsrRequest);
            return $handler($request);
        };
    }
    
    /**
     * Converts a HTTP Ring request into a PSR-7 request
     *
     * @param array $request The Ring request
     * @return RequestInterface A PSR-7 request from the Ring request
     */
    public function convertRingToPsr(array $request): RequestInterface
    {
        $url = PsrRequestUtility::getUrl($request);
        
        if (empty($url)) {
            throw new \InvalidArgumentException("Could not find a URL in the request");    
        }
        
        return new Request(
            $request["http_method"],
            $url,
            $request["headers"],
            $request["body"] ?? null
        );
    }
    
    /**
     * Converts a PSR-7 request into a Ring request
     *
     * @param RequestInterface $request The PSR-7 request
     * @return array A Ring request from the PSR-7 request
     */
    public function convertPsrToRing(RequestInterface $request): array
    {        
        if (! $request->hasHeader("Host")) {
            $request = $request->withHeader("Host", $request->getUri()->getHost());
        }
        
        $body = $request->getBody();
        $contentLength = $body->getSize();
        
        return [
            "http_method" => $request->getMethod(),
            "uri" => "/{$request->getUri()->getPath()}",
            "headers" => $request->getHeaders(),
            "body" => ! empty($contentLength) ? $body : null,
            "scheme" => $request->getUri()->getScheme()
        ];
    }
}
