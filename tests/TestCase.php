<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function request($method, $uri, $data = [], $headers = [])
    {
        return $this->json($method, $uri, $data, $headers);
    }
}
