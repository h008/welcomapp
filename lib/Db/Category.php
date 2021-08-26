<?php
namespace OCA\WelcomApp\Db;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Category extends Entity implements JsonSerializable {
    protected $categoryName;
    protected $categoryOrder;
    protected $color;

    public function __construct() {
        $this->addType('id','integer');
    }
    public function jsonSerialize() {
        return [

            'id'=> $this->id,
            'category_name'=>$this->categoryName,
            'category_order'=>$this->categoryOrder,
            'color'=>$this->color,
        ];
    }
    
}