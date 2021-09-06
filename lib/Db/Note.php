<?php
namespace OCA\WelcomApp\Db;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Note extends Entity implements JsonSerializable {
    protected $title;
    protected $category;
    protected $content;
    protected $userId;
    protected $created;
    protected $updated;
    protected $pinFlag;
    protected $pubFlag;
    protected $tags;
    protected $uuid;
    protected $shareId;
    protected $shareInfo;

    public function __construct() {
        $this->addType('id','integer');
    }
    public function jsonSerialize() {
        return [

            'id'=> $this->id,
            'title'=>$this->title,
            'content'=>$this->content,
            'category'=>$this->category,
            'userId'=>$this->userId,
            'created'=>$this->created,
            'updated'=>$this->updated,
            'pinFlag'=>$this->pinFlag,
            'pubFlag'=>$this->pubFlag,
            'tags'=>$this->tags,
            'uuid'=>$this->uuid,
            'shareId'=>$this->shareId,
            'shareInfo'=>$this->shareInfo,
        ];
    }
    
}