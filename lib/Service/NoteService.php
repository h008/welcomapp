<?php
namespace OCA\WelcomApp\Service;
use Exception;
use DateTime;
use DateTimeZone;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\WelcomApp\Db\Note;
use OCA\WelcomApp\Db\NoteMapper;

class NoteService {
    private $mapper;
    public function __construct(NoteMapper $mapper){
        $this->mapper=$mapper;
    }
    public function findAll(string $userId,int $max){
        return $this->mapper->findAll($userId,$max);
    }
    private function handleException($e){
        if($e instanceof DoesNotExistException ||
        $e instanceof MultipleObjectReturnedException){
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }
    public function find(int $id,string $userId){
        try {
            return $this->mapper->find($id,$userId);
        } catch(Exception $e){
            $this->handleException($e);
        }
    }
    public function filter(int $category,int $offset,int $limit,bool $pubFlag,bool $pinFlag,array $userData,string $userId){
        try{
            return $this->mapper->filter($category,$offset,$limit,$pubFlag,$pinFlag,$userData,$userId);
        } catch(Exception $e){
            $this->handleException($e);
        }
    }
    public function filtercount(int $category,bool $pubFlag,bool $pinFlag,array $userData,string $userId){
        try{
            return $this->mapper->filtercount($category,$pubFlag,$pinFlag,$userData,$userId);
        } catch(Exception $e){
            $this->handleException($e);
        }
    }
    public function create(string $title,string $content,string $userId,int $category,bool $pinFlag,bool $pubFlag,string $tags,string $uuid,int $shareId,string $shareInfo){

        $now = new DateTime();
        $now->setTimeZone(new DateTimeZone('Asia/Tokyo'));
        $date = $now->format('Y-m-d H:i:s');
        $note=new Note();
        $note->setTitle($title);
        $note->setContent($content);
        $note->setUserId($userId);
        $note->setCategory($category);
        $note->setPinFlag($pinFlag);
        $note->setPubFlag($pubFlag);
        $note->setTags($tags);
        $note->setCreated($date);
        $note->setUpdated($date);
        $note->setUuid($uuid);
        $note->setShareId($shareId);
        $note->setShareInfo($shareInfo);
        return $this->mapper->insert($note);
    }
    public function update(int $id,string $title, string $content, string $userId,int $category,bool $pinFlag,bool $pubFlag,string $tags,string $uuid,int $shareId,string $shareInfo){
        try {
            $now = new DateTime();
            $now->setTimeZone(new DateTimeZone('Asia/Tokyo'));
            $date = $now->format('Y-m-d H:i:s');
            $note = $this->mapper->find($id,$userId);
            $note->setTitle($title);
            $note->setContent($content);
            $note->setUserId($userId);
            $note->setCategory($category);
            $note->setPinFlag($pinFlag);
            $note->setPubFlag($pubFlag);
            $note->setTags($tags);
            $note->setUpdated($date);
            $note->setUuid($uuid);
            $note->setShareId($shareId);
            $note->setShareInfo($shareInfo);
            return $this->mapper->update($note);
        } catch(Exception $e){
            $this->handleException($e);
        }
    }
    public function delete(int $id,string $userId){
        try{
            $note= $this->mapper->find($id,$userId);
            $this->mapper->delete($note);
            return $note;
        } catch(Exception $e){
            $this->handleException($e);
        }
    }


}