<?php

namespace shadiakiki1986\Flysystem;

use League\Flysystem\Filesystem;

// copied from flysystem-github/tests/integration/compareToLocal.php
class GitTest extends \GitRestApi\TestCase {

    private static $filesystem;

    final public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$filesystem = new Filesystem(new Git(self::$repo,false,false));
        self::$repo->putConfig('user.name','phpunit test flysystem-git');
        self::$repo->putConfig('user.email','shadiakiki1986@gmail.com');

        if(!self::$filesystem->has('bla')) {
          self::$filesystem->write('bla',self::$random);
        }
    }

  /**
   * @expectedException \League\Flysystem\FileNotFoundException
   */
    final public function testFileNotFound()
    {
        $result = self::$filesystem->read('some random file');
    }

    final public function testFileOk()
    {
        $result =self::$filesystem->read('bla');
        $this->assertNotNull($result);
    }

    final public function testMetadata()
    {
        $fn='bla';
        $log = self::$filesystem->getMetadata($fn);
        $this->assertNotNull($log);
        $this->assertNotNull($log->sha1);
        $this->assertNotNull($log->commitDate);
    }

  /**
   * @expectedException \League\Flysystem\FileExistsException
   */
    final public function testWriteFileAlreadyExists()
    {
        self::$filesystem->write('bla',self::$random);
    }

    final public function testWriteFileNew()
    {
        $fn = 'random files/'.self::$random;
        self::$filesystem->write($fn,self::$random);
        $result = self::$filesystem->read($fn);
        $this->assertEquals($result,self::$random);
    }

    final public function testUpdateOk()
    {
        $fn='bla';
        self::$filesystem->update($fn,self::$random);
        $result = self::$filesystem->read($fn);
        $this->assertEquals($result,self::$random);
    }

    final public function testPut()
    {
        $fn = 'random files/'.self::$random.'-new';
        self::$filesystem->put($fn,self::$random);
        self::$filesystem->put($fn,self::$random);
        $this->assertTrue(true); // will reach here without failing
    }

    /**
     * @depends testUpdateOk
     * @dataProvider testUpdateSameContentSoDoNothingProvider
     */
    final public function testUpdateSameContentSoDoNothing($fn)
    {
        $result = self::$filesystem->read($fn);
        self::$filesystem->update($fn,$result);
    }

    public function testUpdateSameContentSoDoNothingProvider() {
      return [
        ['bla'],
        ['bla foo']
      ];
    }

    /**
     * @depends testUpdateSameContentSoDoNothing
     */
    final public function testDelete()
    {
        $fn='bla';
        $this->assertTrue(self::$filesystem->has($fn));
        self::$filesystem->delete($fn);
        $this->assertFalse(self::$filesystem->has($fn));
    }

}

/*EOF*/
