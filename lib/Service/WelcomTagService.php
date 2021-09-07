<?php
namespace OCA\WelcomApp\Service;
use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\WelcomApp\Db\WelcomTag;
use OCA\WelcomApp\Db\WelcomTagMapper;

class WelcomTagService {
    private $mapper;
    public function __construct(WelcomTagMapper $mapper){
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
    public function create(string $tag_name,int $tag_order,string $color){
        $tag=new WelcomTag();
        $tag->setTagName($tag_name);
        $tag->setTagOrder($tag_order);
        $tag->setColor($color);
        return $this->mapper->insert($tag);
    }
    public function update(int $id,string $tag_name, int $tag_order,string $color){
        try {
            $tag = $this->mapper->find($id);
            $tag->setTagName($tag_name);
            $tag->setTagOrder($tag_order);
            $tag->setColor($color);
            return $this->mapper->update($tag);
        } catch(Exception $e){
            $this->handleException($e);
        }
    }
    public function delete(int $id){
        try{
            $tag= $this->mapper->find($id);
            $this->mapper->delete($tag);
            return $tag;
        } catch(Exception $e){
            $this->handleException($e);
        }
    }


}