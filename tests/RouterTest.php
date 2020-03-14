<?php

declare(strict_types=1);

namespace midorikocak\dictionary;

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        parent::setUp();
        $this->router = new Router();
    }

    public function testCompareUrl(): void
    {
        $this->assertTrue($this->router->compareUrl('/test', '/test'));
        $this->assertFalse($this->router->compareUrl('/test', '/hasan'));
        $this->assertTrue($this->router->compareUrl('/test', '/test/'));
        $this->assertTrue($this->router->compareUrl('test/', '/test/'));

        $this->assertTrue($this->router->compareUrl('test/osman', 'test/osman'));
    }

    public function testCompareUrlWildCards(): void
    {
        $this->assertTrue($this->router->compareUrl('test/kamil', 'test/{osman}'));
        $this->assertFalse($this->router->compareUrl('test/kamil/osman', 'test/{osman}'));

        $this->assertTrue($this->router->compareUrl('test/3', 'test/{mahmut}'));

        $this->assertTrue($this->router->compareUrl('test/3/hasan/5', 'test/{mahmut}/hasan/{kamil}'));
    }

    public function testGetWildCards(): void
    {
        $this->assertEquals(['osman' => 'kamil'], $this->router->getWildcards('test/kamil', 'test/{osman}'));
        $this->assertEquals(['mahmut' => '3'], $this->router->getWildcards('test/3', 'test/{mahmut}'));
        $this->assertEquals(
            ['mahmut' => '3', 'hasan' => 'kamil'],
            $this->router->getWildcards(
                'test/3/osman/kamil',
                'test/{mahmut}/osman/{hasan}'
            )
        );
    }

    public function testRun(): void
    {
        $this->router->post('test/{id}', function ($id) {
            echo $id;
        });
        $this->expectOutputString('3');
        $this->router->run('post', 'test/3');
    }

    public function testRunDouble(): void
    {
        $this->router->get('test/{id}/entry/{entryId}', function ($id, $entryId) {
            echo $id . $entryId;
        });
        $this->expectOutputString('35');
        $this->router->run('get', 'test/3/entry/5');
    }

    public function testRunConflict(): void
    {
        $this->router->get('test/{id}', function ($id) {
            echo $id;
        });

        $this->router->get('test/add', function () {
            echo 'mahmut';
        });

        $this->expectOutputString('3');
        $this->router->run('get', 'test/3');

        $this->expectOutputString('3mahmut');
        $this->router->run('get', 'test/add');
    }
}
