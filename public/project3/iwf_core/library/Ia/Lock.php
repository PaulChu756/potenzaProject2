<?php
namespace Ia;

/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class Lock
{
    public $lockFolder = null;
    
    public $lockFile = null;
    
    public $tag = null;
    
    public $handle = null;
    
    public $got_lock = false;
    
    public function __construct($lockFolder) {
        $this->lockFolder = $lockFolder;    
    }
    
    public function __destruct() {
        $this->releaseLock();        
    }
    
    public function requireTag() {
        if($this->tag==null){
            throw new \Exception('Tag is required for all lock functions');
        }
    }
    
    public function setTag($tag){
        $this->closeHandle();
        $this->tag = $tag;
        $this->lockFile = $this->lockFolder . md5($tag);
        if(!file_exists($this->lockFile)){
            touch($this->lockFile);
            if(!file_exists($this->lockFile)){
                throw new \Exception('Unable to create lock file at '.$this->lockFile);
            }
        }
        $this->openHandle();
        return $this;
    }
    
    public function openHandle(){
        $this->requireTag();
        if($this->handle==null){
            $this->handle = fopen($this->lockFile,'r+');
        }
        return $this->handle;
    }
    
    public function getContents(){
        $this->requireTag();
        if(!$this->got_lock){
            throw new \Exception('You have not acquired a lock for tag `'.$tag.'`');
        }
        return file_get_contents($this->lockFile);
    }
    
    public function putContents($contents){
        $this->requireTag();
        if(!$this->got_lock){
            throw new \Exception('You have not acquired a lock for tag `'.$tag.'`');
        }
        return file_put_contents($this->lockFile,$contents);
    } 

    public function getTimestamp(){
        if($this->handle)
            return filemtime($this->lockFile);
        else
            return false;
    }
    
    public function closeHandle(){
        if($this->handle !== null){
            $this->requireTag();
            fclose($this->handle); 
            $this->handle = null;
        }
    }
    
    public function getLock($max_tries=0,$usleep=500000){ //usleep - delay in microseconds
        $this->requireTag();
        $i = 0;
        if($max_tries > 0){
            while($this->got_lock==false && $i <= $max_tries){
                if(!$this->tryGetLock()){
                    $i++;
                    usleep($usleep);
                }
            }
            if($this->got_lock==false){
                throw new \Exception('Failed to acquire lock (Tag: '. $this->tag. '; File: '. $this->lockFolder . md5($this->tag) . ') after '.$max_tries.' attempts.');
                exit;
            }
        } else {
            while($this->got_lock==false){
                $i++;
                $this->tryGetLock();
                usleep($usleep);
            }
        }
    }
    
    private function tryGetLock(){
        $this->requireTag();
        if(flock($this->handle, LOCK_EX|LOCK_NB)){
            $this->got_lock = true;
            ftruncate($this->handle, 0);
            fwrite($this->handle, 'Locked: '.date('Y-m-d H:i:s').'; ');
        } else {
            $this->got_lock = false;
        }
        return $this->got_lock;
    }
    
    public function releaseLock(){
        if($this->got_lock){
            $this->requireTag();
            fwrite($this->handle, 'Unlocked: '.date('Y-m-d H:i:s'));
            flock($this->handle, LOCK_UN);
            $this->got_lock = false;
            $this->handle = null;
        }
        $this->closeHandle();
    }
    
}