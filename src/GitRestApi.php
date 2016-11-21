<?php

namespace shadiakiki1986\Flysystem;

use League\Flysystem\Util;
use League\Flysystem\Config;

// Adapter for flysystem using git-rest-api-client
// copying from league/flysystem/Adapter/Ftp and Local
class GitRestApi extends \League\Flysystem\Adapter\NullAdapter
{

  private $repo;

  public function __construct(\GitRestApi\Repository $repo, bool $pushPull = false) {
    $this->repo = $repo;
    $this->pushPull = $pushPull;
  }

  public function has($path) {
    if($this->pushPull) $this->repo->pull();
//    return $this->repo->lsTree($path);

    try {
      $this->repo->getTree($path);
      return true;
    } catch(\Exception $e) {
      return false;
    }
  }

  public function write($path, $contents, Config $config) {
    if($this->pushPull) $this->repo->pull();
    $this->repo->putTree($path,$contents);
    $this->repo->postCommit('set '.$path);
    if($this->pushPull) $this->repo->push();

    return [
      'contents'=>$contents,
      'mimetype'=>Util::guessMimeType($path, $contents)
    ];
  }

  public function update ($path,$contents,Config $config) {
    return $this->write($path,$contents,$config);
  }

  public function read($path) {
    if($this->pushPull) $this->repo->pull();

    // get raw data from repo
    try {
      $raw = $this->repo->getTree($path);
    } catch(\Exception $e) {
      return false;
    }

    return [
      'contents'=>$raw,
      'path'=>$path
    ];
  }

}
