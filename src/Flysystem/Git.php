<?php

namespace shadiakiki1986\Flysystem;

use League\Flysystem\Util;
use League\Flysystem\Config;

// Adapter for flysystem using git-rest-api-client
// copying from league/flysystem/Adapter/Ftp and Local
class Git extends \League\Flysystem\Adapter\NullAdapter
{

  private $repo;

  public function __construct(\GitRestApi\Repository $repo, bool $push = true, bool $pull = true) {
    $this->repo = $repo;
    $this->push = $push;
    $this->pull = $pull;
  }

  public function has($path) {
    $bn=basename($path);
    $this->preprocessPath($path);

    if($this->pull) $this->repo->pull();
    $result = $this->repo->lsTree($path);
    $result = array_column($result, 'name');
    return in_array($bn,$result);
  }

  private function preprocessPath(&$path) {
    $path = urlencode($path);
  }

  public function write($path, $contents, Config $config) {
    $this->preprocessPath($path);

    if($this->pull) $this->repo->pull();

    $result = [
      'contents'=>$contents,
      'mimetype'=>Util::guessMimeType($path, $contents)
    ];
    if($this->has($path)) {
      $current = $this->repo->getTree($path);
      if($current == $contents) {
        // will not do anything since same data
        return $result;
      }
    }
    $this->repo->putTree($path,$contents);
    $this->repo->postCommit('set '.$path);
    if($this->push) $this->repo->push();

    return $result;
  }

  public function update($path,$contents,Config $config) {
    return $this->write($path,$contents,$config);
  }

  public function read($path) {
    $this->preprocessPath($path);
    if($this->pull) $this->repo->pull();

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

  public function delete($path) {
    $this->preprocessPath($path);
    if($this->pull) $this->repo->pull();

    try {
      $this->repo->deleteTree($path);
      $this->repo->postCommit('del '.$path);
      if($this->push) $this->repo->push();

      return true;
    } catch(\Exception $e) {
      return false;
    }
  }

  public function getMetadata($path) {
    // just getting repo hash and commit date
    if($this->pull) $this->repo->pull();

    $log = $this->repo->log('-1');
    if(count($log)==0) {
      return false;
    }

    return array_values($log)[0];
  }

}
