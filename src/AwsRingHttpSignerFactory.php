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

use Aws\Credentials\CredentialProvider;
use Aws\Signature\SignatureInterface;
use Aws\Signature\SignatureV4;
use InvalidArgumentException;
use Nekman\AwsRingHttpSigner\Contract\AwsRingHttpSignerInterface;

class AwsRingHttpSignerFactory
{
    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
        // Only static methods.
    }

    /**
     * Create a new instance of a AWS Ring HTTP signer middleware
     *
     * @param string|SignatureInterface $awsRegionOrSignature AWS region where the instance resides or your own signature.
     * @param callable|null Define how to get the credentials. Defaults to AWS default provider.
     * @return AwsRingHttpSignerInterface Implementation of the AWS Ring HTTP signer middleware
     * @see CredentialProvider::defaultProvider()
     */
    public static function create($awsRegionOrSignature, ?callable $credentialProvider = null): AwsRingHttpSignerInterface
    {
        $signature = null;

        if ($awsRegionOrSignature instanceof SignatureInterface) {
            $signature = $awsRegionOrSignature;
        } elseif (is_string($awsRegionOrSignature)) {
            // Assume usage of Elasticsearch if only region is passed
            $signature = new SignatureV4("es", $awsRegionOrSignature);
        } else {
            throw new InvalidArgumentException(
                sprintf("Expects \$awsRegionOrSignature to be an instance of \"%s\" or a string", SignatureInterface::class)
            );
        }

        return new AwsRingHttpSigner(
            $signature,
            $credentialProvider ?? CredentialProvider::defaultProvider()
        );
    }
}
