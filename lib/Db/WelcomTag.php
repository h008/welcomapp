<?php
namespace OCA\WelcomApp\Db;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class WelcomTag extends Entity implements JsonSerializable {
    protected $tagName;
    protected $tagOrder;
    protected $color;

    public function __construct() {
        $this->addType('id','integer');
    }
    public function jsonSerialize() {
        return [

            'id'=> $this->id,
            'tag_name'=>$this->tagName,
            'tag_order'=>$this->tagOrder,
            'color'=>$this->color,
        ];
    }
    
}