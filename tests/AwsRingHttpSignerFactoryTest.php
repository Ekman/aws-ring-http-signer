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

namespace Nekman\AwsRingHttpSigner\Test;

use Aws\Credentials\CredentialProvider;
use Aws\Credentials\Credentials;
use Aws\Signature\SignatureV4;
use InvalidArgumentException;
use Nekman\AwsRingHttpSigner\AwsRingHttpSignerFactory;
use PHPUnit\Framework\TestCase;

class AwsRingHttpSignerFactoryTest extends TestCase
{
	public function testCreate()
	{
		$this->assertNotNull(AwsRingHttpSignerFactory::create("eu-central-1"));
	}

	public function testCreate_credential_provider()
	{
		$credentials = new Credentials("key", "secret");
		$credentialProvider = CredentialProvider::fromCredentials($credentials);

		$this->assertNotNull(AwsRingHttpSignerFactory::create("eu-central-1", $credentialProvider));
	}

	public function testCreate_signature()
	{
		$credentials = new Credentials("key", "secret");
		$credentialProvider = CredentialProvider::fromCredentials($credentials);

		$signature = new SignatureV4("es", "eu-central-1");

		$this->assertNotNull(AwsRingHttpSignerFactory::create($signature, $credentialProvider));
	}

	public function testCreate_invalid_signature()
	{
		$this->expectException(InvalidArgumentException::class);

		$credentials = new Credentials("key", "secret");
		$credentialProvider = CredentialProvider::fromCredentials($credentials);

		AwsRingHttpSignerFactory::create(123, $credentialProvider);
	}
}
