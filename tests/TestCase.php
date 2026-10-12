<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Views use @vite; this lets them render without a built manifest,
        // so CI doesn't need a Node build just to run the PHP tests.
        $this->withoutVite();
    }
}
