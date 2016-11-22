<?php

namespace shadiakiki1986\Flysystem;

use League\Flysystem\Filesystem;

// copied from flysystem-github/tests/integration/compareToLocal.php
class GitTest extends \GitRestApi\TestCase {

    private static $filesystem;

    final public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$filesystem = new Filesystem(new Git(self::$repo,false));
        self::$repo->putConfig('user.name','phpunit test flysystem-git');
        self::$repo->putConfig('user.email','shadiakiki1986@gmail.com');
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
        $this->assertNotEquals($result,self::$random);
    }

  /**
   * @expectedException \League\Flysystem\FileExistsException
   */
    final public function testWriteFileAlreadyExists()
    {
        self::$filesystem->write('bla',self::$random);
        $result = self::$filesystem->read('bla');
        $this->assertEquals($result,self::$random);
    }

    final public function testWriteOk()
    {
        self::$filesystem->update('bla',self::$random);
        $result = self::$filesystem->read('bla');
        $this->assertEquals($result,self::$random);
    }

}

/*EOF*/
