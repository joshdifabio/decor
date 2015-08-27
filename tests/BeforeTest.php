<?php
class BeforeTest extends PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $user = $this->createUser('joshdifabio');
        $product = $this->createProduct();

        $authorise = function () use ($user) {
            $this->authorise($user);
        };

        $deleteProductWithAuth = Decor\before([$this, 'deleteProduct'], $authorise);
        $deleteProductWithAuth($product);
        $this->assertTrue($product->deleted);

        $user->username = 'evil_dude';
        $product->deleted = false;

        try {
            $deleteProductWithAuth($product);
            $this->fail();
        } catch (\RuntimeException $e) {
        }

        $this->assertFalse($product->deleted);
    }

    public function testWithExtraParam()
    {
        $user = $this->createUser('joshdifabio');
        $product = $this->createProduct();

        $deleteProductWithAuth = Decor\before([$this, 'deleteProduct'], [$this, 'authorise']);
        $deleteProductWithAuth($product, $user);
        $this->assertTrue($product->deleted);

        $user->username = 'evil_dude';
        $product->deleted = false;

        try {
            $deleteProductWithAuth($product, $user);
            $this->fail();
        } catch (\RuntimeException $e) {
        }

        $this->assertFalse($product->deleted);
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

    public function authorise(stdClass $user)
    {
        if ('joshdifabio' !== $user->username) {
            throw new \RuntimeException;
        }
    }
}
