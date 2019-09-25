<?php
namespace Nekman\AwsRingHttpSigner\Test;

use Nekman\AwsRingHttpSigner\PsrRequestUtility;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Stream\Stream;

class PsrRequestUtilityTest extends TestCase
{
    /** @dataProvider provideGetUrl */
    public function testGetUrl($ringRequest, $expected)
    {
        $this->assertEquals($expected, PsrRequestUtility::getUrl($ringRequest));
    }
    
    public function provideGetUrl()
    {
        return [
            [
                [
                    "scheme" => "https",
                    "headers" => ["host" => ["example.com"]],
                    "uri" => "/path"
                ],
                "https://example.com/path"
            ],
            "Test without scheme (defaults to http)" => [
                [
                    "headers" => ["host" => ["example.com"]],
                    "uri" => "/path"
                ],
                "http://example.com/path"
            ],
            "Test without host" => [
                [
                    "scheme" => "https",
                    "uri" => "/path"
                ],
                null
            ]
        ];
    }
    
    /** @dataProvider provideGetHost */
    public function testGetHost($ringRequest, $expected)
    {
        $this->assertEquals($expected, PsrRequestUtility::getFirstHost($ringRequest));
    }
    
    public function provideGetHost()
    {
        return [
            "Small letters" => [
                [
                    "headers" => ["host" => ["example.com"]]
                ],
                "example.com"
            ],
            "Capital H" => [
                [
                    "headers" => ["Host" => ["example.com"]]
                ],
                "example.com"
            ],
            "Host is not array" => [
                [
                    "headers" => ["host" => "example.com"]
                ],
                "example.com"
            ],
            "Could not find host" => [
                [
                    "headers" => ["content-type" => ["application/json"]]
                ],
                null
            ]
        ];
    }

    /** @dataProvider provideGetBody */
    public function testGetBody($request, $expected)
    {
        $body = PsrRequestUtility::getBody($request);

        if (is_resource($body)) {
            $body = stream_get_contents($body);
        }

        $this->assertEquals($expected, $body);
    }

    public function provideGetBody()
    {
        return [
            "Test null" => [
                [],
                null
            ],
            "Test \GuzzleHttp\Stream\StreamInterface" => [
                [
                    "body" => Stream::factory("Hello, World")
                ],
                "Hello, World"
            ],
            "Test resource" => [
                [
                    "body" => fopen('data://text/plain,Hello World','r')
                ],
                "Hello World"
            ],
            "Test __toString" => [
                [
                    "body" => new class {
                        public function __toString()
                        {
                            return "Hello, World";
                        }
                    }
                ],
                "Hello, World"
            ],
            "Test \Iterator" => [
                [
                    "body" => new \ArrayIterator([ "Hello", ", ", "World" ])
                ],
                "Hello, World"
            ]
        ];
    }
}
