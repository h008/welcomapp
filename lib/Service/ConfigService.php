<?php
namespace OCA\WelcomApp\Service;
use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\WelcomApp\Db\Config;
use OCA\WelcomApp\Db\ConfigMapper;

class ConfigService {
    private $mapper;
    public function __construct(ConfigMapper $mapper){
        $this->mapper=$mapper;
    }
    public function findAll(){
        return $this->mapper->findAll();
    }
    private function handleException($e){
        if($e instanceof DoesNotExistException ||
        $e instanceof MultipleObjectReturnedException){
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
    public function findByKind(string $kind){
        try{
            return $this->mapper->findByKind($kind);
        } catch(Exception $e){
            $this->handleException($e);
        }
    }
    public function create(string $kind,string $key,string $value,string $userId){
        $config=new Config();
        $config->setKind($kind);
        $config->setKey($key);
        $config->setValue($value);
        $config->setUserId($userId);
        return $this->mapper->insert($config);
    }
    public function update(int $id,string $kind, string $key,string $value,string $userId){
        try {
            $config = $this->mapper->find($id);
            $config->setKind($kind);
            $config->setKey($key);
            $config->setValue($value);
            $config->setUserId($userId);
            return $this->mapper->update($config);
        } catch(Exception $e){
            $this->handleException($e);
        }
    }
    public function delete(int $id){
        try{
            $config = $this->mapper->find($id);
            $this->mapper->delete($config);
            return $config;
        } catch(Exception $e){
            $this->handleException($e);
        }
    }


}