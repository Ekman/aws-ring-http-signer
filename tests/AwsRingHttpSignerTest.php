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
            "uri" => "/"
        ];
        
        $handler = new MockHandler(["status" => 200]);
        
        $assertHandler = function ($handler) use ($expectedAwsSignatureRegexp) {
            return function (array $request) use ($handler, $expectedAwsSignatureRegexp) {
                // Assert that the request has been signed properly
                $this->assertRegExp($expectedAwsSignatureRegexp, $request["headers"]["Authorization"][0]);
                
                return $handler($request);
            };
        };
        
        $response = $awsRingHttpSigner($assertHandler($handler))($request);
        
        $this->assertEquals(200, $response["status"]);
    }
    
    /** @dataProvider provideConvertRingToPsr */
    public function testConvertRingToPsr($request, $expected)
    {
        $this->assertEquals($expected, AwsRingHttpSignerFactory::create("eu-central-1")->convertRingToPsr($request));
    }
    
    public function provideConvertRingToPsr()
    {
        return [
            [
                [
                    "http_method" => "GET",
                    "headers" => ["Host" => ["google.com"]],
                    "uri" => "/"
                ],
                new Request("GET", "http://google.com/")
            ]
        ];
    }
    
    /** @dataProvider provideConvertPsrToRing */
    public function testConvertPsrToRing($request, $expected)
    {
        $this->assertEquals($expected, AwsRingHttpSignerFactory::create("eu-central-1")->convertPsrToRing($request));
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
                    "scheme" => "https"
                ]
            ]
        ];
    }
}
