<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)->assertJson([
            'success' => true,
            'message' => 'Super Score API is running.',
            'data' => [
                'application' => 'Super Score',
                'version' => 'v1',
            ],
        ]);
    }
}
