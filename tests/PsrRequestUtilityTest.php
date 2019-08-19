<?php
namespace Nekman\AwsRingHttpSigner\Test;

use Nekman\AwsRingHttpSigner\PsrRequestUtility;
use PHPUnit\Framework\TestCase;

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
            "Test without scheme" => [
                [
                    "headers" => ["host" => ["example.com"]],
                    "uri" => "/path"
                ],
                null
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
}
