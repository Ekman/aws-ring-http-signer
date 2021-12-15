# AWS Ring HTTP Signer

[![Build Status](https://circleci.com/gh/Ekman/aws-ring-http-signer.svg?style=svg)](https://app.circleci.com/pipelines/github/Ekman/aws-ring-http-signer)
[![Coverage Status](https://coveralls.io/repos/github/Ekman/aws-ring-http-signer/badge.svg)](https://coveralls.io/github/Ekman/aws-ring-http-signer)

**[RingPHP](https://github.com/guzzle/RingPHP) has been discontinued. This package does not make sense any more.**

In order for AWS to know who/what is making the request it needs to be signed. Using this package, you can sign [RingPHP](https://ringphp.readthedocs.io/en/latest/) requests with AWS credentials.

**Do you want to use this with your Elasticsearch instance hosted on AWS? See the [Usage with Elasticsearch](#usage-with-elasticsearch) section below.**

## Usage

Install with [Composer](https://getcomposer.org):

```bash
composer require nekman/aws-ring-http-signer
```

In order to instantiate a new instance of the library, use the factory. Then wrap your Ring HTTP handler with the middleware and use it as normal:

```php
use GuzzleHttp\Ring\Client\CurlHandler;
use Aws\Signature\SignatureV4;
use Nekman\AwsRingHttpSigner\AwsRingHttpSignerFactory;

$signature = new SignatureV4($awsService, $awsRegion); // How do I create this? Please consult the AWS documentation for the service you are using.
$awsRingHttpSigner = AwsRingHttpSignerFactory::create($signature);

$defaultHandler = new CurlHandler(); // Or use whatever handler you already have available.
$handler = $awsRingHttpSigner($defaultHandler);

// And you're done! Use the $handler as you normally would
```

## Usage with Elasticsearch

Install with [Composer](https://getcomposer.org):

```bash
composer require nekman/aws-ring-http-signer elasticsearch/elasticsearch
```

In order to instantiate a new instance of the library, use the factory. Then wrap your the Elasticsearch client with the middleware and use it as normal:

```php
use Elasticsearch\ClientBuilder;
use Nekman\AwsRingHttpSigner\AwsRingHttpSignerFactory;

$awsRingHttpSigner = AwsRingHttpSignerFactory::create($awsRegion);
$handler = $awsRingHttpSigner(ClientBuilder::defaultHandler()); 

$client = ClientBuilder::create()
    ->setHandler($handler)
    ->build();

// And you're done! Use the $client as you normally would
```


## AWS Credentials Provider and signatures

By default the library will use the default credentials provider provided by AWS. There are many other ways to load credentials which you can [read about in the AWS documentation](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials_provider.html). Consult the AWS documentation on how to create a `SignatureInterface`.

### Example

Lets say you want to provide static credentials from environment variables:

```php
use Nekman\AwsRingHttpSigner\AwsRingHttpSignerFactory;
use Aws\Credentials\CredentialProvider;
use Aws\Credentials\Credentials;

$credentials = new Credentials(getenv("AWS_KEY"), getenv("AWS_SECRET"));
$credentialProvider = CredentialProvider::fromCredentials($credentials);

$awsRingHttpSigner = AwsRingHttpSignerFactory::create($awsRegion, $credentialProvider);
```

Consult the AWS documentation for more information.

## Versioning

This project complies with [Semantic Versioning](https://semver.org/).

## Changelog

For a complete list of changes, and how to migrate between major versions, see [releases page](https://github.com/Ekman/aws-ring-http-signer/releases).
