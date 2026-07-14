<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Dashboard\SduiValidatorService;

class SduiSchemaTest extends TestCase
{
    public function test_valid_primitive_schema_passes()
    {
        $validator = new SduiValidatorService();
        $json = [
            'schema_version' => '1.0',
            'screen' => 'Dashboard',
            'nodes' => [
                [
                    'type' => 'Container',
                    'children' => [
                        ['type' => 'Row'],
                        ['type' => 'Column'],
                        ['type' => 'Text']
                    ]
                ]
            ]
        ];

        $this->assertTrue($validator->validate($json));
    }

    public function test_domain_component_fails_schema_validation()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("SDUI Invalid Primitive Type: WeatherCard");

        $validator = new SduiValidatorService();
        $json = [
            'schema_version' => '1.0',
            'screen' => 'Dashboard',
            'nodes' => [
                [
                    'type' => 'WeatherCard' // Proving domain widgets are banned
                ]
            ]
        ];

        $validator->validate($json);
    }
    
    public function test_remotenode_fails_schema_validation()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("SDUI Invalid Primitive Type: RemoteNode");

        $validator = new SduiValidatorService();
        $json = [
            'schema_version' => '1.0',
            'screen' => 'Dashboard',
            'nodes' => [
                [
                    'type' => 'RemoteNode' // Proving lazy loading / N+1 is banned
                ]
            ]
        ];

        $validator->validate($json);
    }
}
