# AWS Ring HTTP Signer

[![Build Status](https://travis-ci.org/Ekman/aws-ring-http-signer.svg?branch=master)](https://travis-ci.org/Ekman/aws-ring-http-signer)
[![Coverage Status](https://coveralls.io/repos/github/Ekman/aws-ring-http-signer/badge.svg)](https://coveralls.io/github/Ekman/aws-ring-http-signer)

**Note! [RingPHP](https://github.com/guzzle/RingPHP) has been discontinued this package will supported for now, but eventually it will follow the same fate.**

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

// Create an instance of this library
$signature = new SignatureV4($awsService, $awsRegion); // How do I create this? Please consult the AWS documentation for the service you are using.
$awsRingHttpSigner = AwsRingHttpSignerFactory::create($signature);

// Wrap your Ring HTTP handler
$defaultHandler = new CurlHandler(); // Or use whatever handler you already have available.
$awsSignedHandler = $awsRingHttpSigner($defaultHandler);

// And you're done!
//
// Below is just using the handler to send a test request
$response = $awsSignedHandler([
    "http_method" => "GET",
    "headers" => ["Host" => ["example.com"]]
]);
```

And you're done! If you want to use the package with your Elasticsearch instance hosted on AWS, then keep reading.

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

By default the library will use the default credentials provider provided by AWS. Lets say you want to provide static credentials from environment variables instead:

```php
use Nekman\AwsRingHttpSigner\AwsRingHttpSignerFactory;
use Aws\Credentials\CredentialProvider;
use Aws\Credentials\Credentials;

$credentials = new Credentials(getenv("AWS_KEY"), getenv("AWS_SECRET"));
$credentialProvider = CredentialProvider::fromCredentials($credentials);

$awsRingHttpSigner = AwsRingHttpSignerFactory::create($awsRegion, $credentialProvider);
```

There are many other ways to load credentials. You can [read more about loading credentials in the AWS documentation](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials_provider.html). Also, please consult the AWS documentation on how to create signatures.
