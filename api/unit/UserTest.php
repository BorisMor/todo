<?php

namespace unit;

use PHPUnit\Framework\TestCase;
use app\component\UsersComponent;

class UserTest extends TestCase
{
    function testLogin()
    {
        $token = UsersComponent::login('test', 'test');
        $this->assertFalse(empty($token), 'empty token');
    }
}