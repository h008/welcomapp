<?php
namespace OCA\WelcomApp\Db;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Config extends Entity implements JsonSerializable {
    protected $kind;
    protected $key;
    protected $value;
    protected $userId;

    public function __construct() {
        $this->addType('id','integer');
    }
    public function jsonSerialize() {
        return [

            'id'=> $this->id,
            'kind'=>$this->kind,
            'key'=>$this->key,
            'value'=>$this->value,
            'userId'=>$this->userId,
        ];
    }
    
}