<?php
namespace Nekman\AwsRingHttpSigner\Test;

use Aws\Credentials\CredentialProvider;
use Aws\Credentials\Credentials;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Ring\Client\MockHandler;
use Nekman\AwsRingHttpSigner\AwsRingHttpSigner;
use Nekman\AwsRingHttpSigner\AwsRingHttpSignerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class AwsRingHttpSignerTest extends TestCase
{
    public function testInvoke()
    {
        $expectedAwsSignatureRegexp = "/AWS4-HMAC-SHA256 Credential=test1\/\d{8}\/eu-central-1\/es\/aws4_request, SignedHeaders=host;x-amz-date, Signature=.{64}/";
        
        $credentials = new Credentials("test1", "test2");
        $credentialsProvider = CredentialProvider::fromCredentials($credentials);
        
        $signature = new SignatureV4("es", "eu-central-1");
        
        $awsRingHttpSigner = new AwsRingHttpSigner($signature, $credentialsProvider);
        
        $request = [
            "http_method" => "GET",
            "headers" => ["Host" => "example.com"],
            "scheme" => "https",
            "uri" => "/",
            "future" => true
        ];
        
        $handler = new MockHandler(["status" => 200]);
        
        $assertHandler = function ($handler) use ($expectedAwsSignatureRegexp) {
            return function (array $request) use ($handler, $expectedAwsSignatureRegexp) {
                // Assert that the request has been signed properly
                $this->assertRegExp($expectedAwsSignatureRegexp, $request["headers"]["Authorization"][0]);
                // Assert that merging keys works
                $this->assertTrue($request["future"]);
                
                return $handler($request);
            };
        };
        
        $response = $awsRingHttpSigner($assertHandler($handler))($request);
        
        $this->assertEquals(200, $response["status"]);
    }
    
    /** @dataProvider provideConvertRingToPsr */
    public function testConvertRingToPsr($ringRequest, $expected)
    {
        /** @var \Psr\Http\Message\RequestInterface $psrRequest */
        $psrRequest = AwsRingHttpSignerFactory::create("eu-central-1")->convertRingToPsr($ringRequest);
        
        $this->assertEquals($expected->getMethod(), $psrRequest->getMethod());
        $this->assertEquals($expected->getHeaders(), $psrRequest->getHeaders());
        $this->assertEquals($expected->getUri(), $psrRequest->getUri());
        $this->assertEquals($expected->getBody()->getContents(), $psrRequest->getBody()->getContents());
        $this->assertEquals($expected->getProtocolVersion(), $psrRequest->getProtocolVersion());
    }
    
    public function provideConvertRingToPsr()
    {
        return [
            [
                [
                    "http_method" => "GET",
                    "headers" => ["Host" => ["google.com"]],
                    "uri" => "/",
                    "version" => 1.1
                ],
                new Request("GET", "http://google.com/", ["Host" => ["google.com"]])
            ],
            "Test with body" => [
                [
                    "http_method" => "PUT",
                    "headers" => ["Host" => ["google.com"]],
                    "uri" => "/",
                    "body" => '{"hello":"world"}'
                ],
                new Request("PUT", "http://google.com/", ["Host" => ["google.com"]], '{"hello":"world"}')
            ],
            "Test with query string" => [
                [
                    "http_method" => "GET",
                    "headers" => ["Host" => ["google.com"]],
                    "uri" => "/",
                    "query_string" => "foo=bar"
                ],
                new Request("GET", "http://google.com/?foo=bar", ["Host" => ["google.com"]])
            ]
        ];
    }
    
    /** @dataProvider provideConvertPsrToRing */
    public function testConvertPsrToRing($psrRequest, $expected)
    {
        $ringRequest = AwsRingHttpSignerFactory::create("eu-central-1")->convertPsrToRing($psrRequest);
        
        $this->assertEquals($expected["http_method"], $ringRequest["http_method"]);
        $this->assertEquals($expected["headers"], $ringRequest["headers"]);
        $this->assertEquals($expected["uri"], $ringRequest["uri"]);
        $this->assertNotInstanceOf(StreamInterface::class, $ringRequest["body"]);
        $this->assertEquals($expected["body"], $ringRequest["body"]);
        $this->assertEquals($expected["scheme"], $ringRequest["scheme"]);
        $this->assertEquals($expected["query_string"] ?? null, $ringRequest["query_string"]);
        $this->assertEquals($expected["version"] ?? "1.1", $ringRequest["version"] ?? "1.1");
    }
    
    public function provideConvertPsrToRing()
    {
        return [
            [
                new Request("GET", "https://google.com"),
                [
                    "http_method" => "GET",
                    "headers" => ["Host" => ["google.com"]],
                    "uri" => "/",
                    "body" => null,
                    "scheme" => "https",
                    "version" => "1.1"
                ]
            ],
            "Test with body" => [
                new Request("PUT", "https://google.com", [], '{"hello":"world"}'),
                [
                    "http_method" => "PUT",
                    "headers" => ["Host" => ["google.com"]],
                    "uri" => "/",
                    "body" => '{"hello":"world"}',
                    "scheme" => "https"
                ]
            ],
            "Test with query string" => [
                new Request("GET", "https://google.com?foo=bar"),
                [
                    "http_method" => "GET",
                    "headers" => ["Host" => ["google.com"]],
                    "uri" => "/",
                    "body" => null,
                    "scheme" => "https",
                    "query_string" => "foo=bar"
                ]
            ],
            "Test without scheme" => [
                new Request("GET", "service.local"),
                [
                    "http_method" => "GET",
                    "headers" => ["Host" => ["service.local"]],
                    "uri" => "/",
                    "body" => null,
                    "scheme" => "http",
                    "query_string" => null
                ]
            ]
        ];
    }
}
