# AWS Ring HTTP Signer

[![Build Status](https://travis-ci.org/Ekman/aws-ring-http-signer.svg?branch=master)](https://travis-ci.org/Ekman/aws-ring-http-signer)
[![Coverage Status](https://coveralls.io/repos/github/Ekman/aws-ring-http-signer/badge.svg)](https://coveralls.io/github/Ekman/aws-ring-http-signer)

Sign Ring HTTP requests with AWS credentials. Can be used if you have a hosted Elasticsearch domain on AWS and want to configure permissions and roles on it. In order to AWS to know who/what is making the request it needs to be signed. The [Elasticsearch PHP package](https://github.com/elastic/elasticsearch-php) is using [Ring HTTP](https://github.com/guzzle/RingPHP) as its underlying HTTP transport. Using this package, you can sign each Ring HTTP request with the AWS credentials it needs.

**This package works with any Ring HTTP request and is not in any way tied to Elasticsearch**. 

## Installation

Install with [Composer](https://getcomposer.org):

```bash
composer require nekman/aws-ring-http-signer
```

## Usage

In order to instantiate a new instance of the library, use the factory:

```php
use Nekman\AwsRingHttpSigner\AwsRingHttpSignerFactory;

$awsRingHttpSigner = AwsRingHttpSignerFactory::create($awsRegion);
```

Wrap your Ring HTTP handler with the middleware and use it as normal:

```php
use GuzzleHttp\Ring\Client\CurlHandler;

$defaultHandler = new CurlHandler();
$awsSignedHandler = $awsRingHttpSigner($defaultHandler);

$response = $awsSignedHandler([
    "http_method" => "GET",
    "headers" => ["Host" => ["example.com"]]
]);
```

### AWS Credentials Provider

By default the library will use the default credentials provider provided by AWS. Lets say you want to provide static credentials from environment variables instead:

```php
use Nekman\AwsRingHttpSigner\AwsRingHttpSignerFactory;
use Aws\Credentials\CredentialProvider;
use Aws\Credentials\Credentials;

$credentials = new Credentials(getenv("AWS_KEY"), getenv("AWS_SECRET"));
$credentialProvider = CredentialProvider::fromCredentials($credentials);

$awsRingHttpSigner = AwsRingHttpSignerFactory::create($awsRegion, $credentialProvider);
```

There are many other ways to load credentials. You can [read more about loading credentials in the AWS documentation](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials_provider.html).

### Usage with Elasticsearch

Install Elasticsearch separetely:

```bash
composer require elasticsearch/elasticsearch
```

Wrap the default HTTP handler with this middleware:

```php
use Elasticsearch\ClientBuilder;

$handler = $awsRingHttpSigner(ClientBuilder::defaultHandler()); 

$client = ClientBuilder::create()
    ->setHandler($handler)
    ->build();
```

All requests using Elasticsearch will now be signed with AWS credentials.
