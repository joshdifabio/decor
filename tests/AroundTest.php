<?php
use Decor\Invoker;

class AroundTest extends PHPUnit_Framework_TestCase
{
    public function testNullDecorator()
    {
        $sayHello = Decor\around(
            function () {
                return 'Hello, world!';
            },
            function (Invoker $invoker) {
                $invoker();
            }
        );

        $result = $sayHello();
        $this->assertNull($result);
    }

    public function testProxyDecorator()
    {
        $sayHello = Decor\around(
            function () {
                return 'Hello, world!';
            },
            function (Invoker $invoker) {
                return $invoker();
            }
        );

        $result = $sayHello();
        $this->assertSame('Hello, world!', $result);
    }

    public function testUnchangedArgs()
    {
        $shout = Decor\around(
            function ($phrase) {
                return $phrase;
            },
            function (Invoker $invoker) {
                return $invoker() . '!';
            }
        );

        $result = $shout('Hello, world');
        $this->assertSame('Hello, world!', $result);
    }

    public function testDefaultValues()
    {
        $getFullName = Decor\around(
            function ($firstName, $lastName, $title = 'Mr') {
                return "$title $firstName $lastName";
            },
            function ($title, $lastName) {
                return "$title $lastName";
            }
        );

        $fullName = $getFullName('Joshua', 'Di Fabio');
        $this->assertSame('Mr Di Fabio', $fullName);
    }

    public function testSharedArg()
    {
        $shout = Decor\around(
            function ($phrase) {
                return $phrase;
            },
            function (Invoker $invoker, $phrase) {
                return $invoker([
                    'phrase' => "$phrase!",
                ]);
            }
        );

        $result = $shout('Hello, world');
        $this->assertSame('Hello, world!', $result);

        $shout = Decor\around(
            function ($phrase) {
                return $phrase;
            },
            function ($phrase, Invoker $invoker) {
                return $invoker([
                    'phrase' => "$phrase!",
                ]);
            }
        );

        $result = $shout('Hello, world');
        $this->assertSame('Hello, world!', $result);
    }

    public function testCustomArg()
    {
        $say = Decor\around(
            function ($phrase) {
                return $phrase;
            },
            function (Invoker $invoker, $phrase, $upperCase = true) {
                return $invoker([
                    'phrase' => $upperCase ? strtoupper($phrase) : $phrase,
                ]);
            }
        );

        $upper = $say('Hello, world');
        $this->assertSame('HELLO, WORLD', $upper);

        $upper = $say('Hello, world', false);
        $this->assertSame('Hello, world', $upper);
    }

    public function createUser($username)
    {
        $user = new stdClass;
        $user->username = $username;

        return $user;
    }

    public function createProduct()
    {
        $product = new stdClass;
        $product->deleted = false;

        return $product;
    }

    public function deleteProduct(stdClass $product)
    {
        $product->deleted = true;
    }
}
