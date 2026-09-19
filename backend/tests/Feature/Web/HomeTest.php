<?php

namespace Tests\Feature\Web;

use Tests\TestCase;

class HomeTest extends TestCase
{
    public function test_home_page_is_accessible(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Super Score');
    }
}
