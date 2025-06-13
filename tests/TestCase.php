<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Log;

abstract class TestCase extends BaseTestCase
{
    protected string $apiVersion;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        //$this->apiVersion = request()->server('API_VERSION') ?? 'v1';
        $this->apiVersion = env('API_VERSION') ?? 'v1';

        Log::info("Running tests for API version: {$this->apiVersion}");
    }

    /**
     * Provides a versioned API url string
     *
     * @param string $uri
     * @return string
     */
    protected function APIUrl(string $uri): string
    {
        return '/api/' . $this->apiVersion . '/' . ltrim($uri, '/');
    }
}
