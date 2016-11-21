<?php

namespace shadiakiki1986\Flysystem;

// Adapter for flysystem using git-rest-api-client
class GitRestApi extends \League\Flysystem\Adapter\AbstractAdapter
{

  private $repo;

  public function __construct(\GitRestApi\Repository $repo, bool $pushPull = false) {
    $this->repo = $repo;
    $this->pushPull = $pushPull;
  }

  protected function has(string $path) {
    if($this->pushPull) $this->repo->pull();
    return $this->repo->lsTree($path);
    $this->repo->commit('set '.$item->getKey());
    if($this->pushPull) $this->repo->push();
  }

  protected function fetchObjectFromCache($key) {
    $empty = [false, null, []];
    if($this->pushPull) $this->repo->pull();

    // get raw data from repo
    try {
      $raw = $this->repo->get($key);
    } catch(\Exception $e) {
      return $empty;
    }

    // save raw data to fsCachePool
    $file  = $this->getFilePath($key);
    $this->fsCachePool->filesystem->write($file,$raw);

    // now get with fsCachePool
    $data = $this->fsCachePool->getItem($key)->get();

    if(!$data) {
      // then the key expired
      $this->clearOneObjectFromCache($key);
      return $empty;
    }

    return [true, $data, []];
  }

  protected function clearAllObjectsFromCache() {
    $this->repo->deleteKey('*');
    $this->repo->commit('clear repo');
    if($this->pushPull) $this->repo->push();
  }

  protected function clearOneObjectFromCache($key) {
    $this->repo->deleteKey($key);
  }

}
