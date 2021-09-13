<?php
namespace OCA\WelcomApp\Service;
use Exception;
use DateTime;
use DateTimeZone;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\WelcomApp\Db\Files;
use OCA\WelcomApp\Db\FilesMapper;

class FilesService {
    private $mapper;
    public function __construct(FilesMapper $mapper){
        $this->mapper=$mapper;
    }
    public function findAll(){
        return $this->mapper->findAll();
    }
    private function handleException($e){
        if($e instanceof DoesNotExistException ||
        $e instanceof MultipleObjectsReturnedException){
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }
    public function find(int $id){
        try {
            return $this->mapper->find($id);
        } catch(Exception $e){
            $this->handleException($e);
        }
    }
    public function findByAid(string $fileurl){
        try {
            return $this->mapper->findByAid($fileurl);
        } catch(Exception $e){
            $this->handleException($e);
        }
    }
    public function create(int $id,int $announceId,string $filename,string $fileurl,string $filetype,bool $isEyecatch,string $href,bool $hasPreview,string $updated,int $size,string $userId){
        $now = new DateTime();
        $now->setTimeZone(new DateTimeZone('Asia/Tokyo'));
        $date = $now->format('Y-m-d H:i:s');
        try {
            $files = $this->mapper->find($id);
            $flag = true;
        } catch(Exception $e ) {
            $files=new Files();
            $flag=false;

        } 

        $files->setId($id);
        $files->setAnnounceId($announceId);
        $files->setFilename($filename);
        $files->setFileurl($fileurl);
        $files->setFiletype($filetype);
        $files->setIsEyecatch($isEyecatch?1:0);
        $files->setHref($href);
        $files->setHasPreview($hasPreview?1:0);
        $files->setUpdated($date);
        $files->setUserId($userId);
        $files->setSize($size);
        try {
            if($flag){
                return $this->mapper->update($files);
            }else{
                return $this->mapper->insert($files);
            }
        } catch(Exception $e){
            $this->handleException($e);
        }
    }
    public function update(int $id,int $announceId,string $filename, string $fileurl,string $filetype,bool $isEyecatch,string $href,bool $hasPreview,string $updated,int $size,string $userId){
        $now = new DateTime();
        $now->setTimeZone(new DateTimeZone('Asia/Tokyo'));
        $date = $now->format('Y-m-d H:i:s');
        try {
            $files = $this->mapper->find($id);
            $flag = true;
        } catch(Exception $e ) {
            $files=new Files();
            $flag=false;
        } 
        
            $files->setAnnounceId($announceId);
            $files->setFilename($filename);
            $files->setFileurl($fileurl);
            $files->setFiletype($filetype);
            $files->setIsEyecatch($isEyecatch?1:0);
            $files->setHref($href);
            $files->setHasPreview($hasPreview?1:0);
            $files->setUpdated($date);
            $files->setSize($size);
            $files->setUserId($userId);
        try {
            if($flag){
                return $this->mapper->update($files);
            }else{
               //$files->setCreated($date); 
                return $this->mapper->insert($files);
            }
        } catch(Exception $e){
            $this->handleException($e);
        }
    }
    public function delete(int $id){
        try{
            $files= $this->mapper->find($id);
            $this->mapper->delete($files);
            return $files;
        } catch(Exception $e){
            $this->handleException($e);
        }
    }


}