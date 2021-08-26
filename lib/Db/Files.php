<?php
namespace OCA\WelcomApp\Db;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Files extends Entity implements JsonSerializable {
    public $id;
    protected $announceId;
    protected $filename;
    protected $fileurl;
    protected $filetype; 
    protected $isEyecatch;
    protected $href;
    protected $hasPreview;
    protected $userId;
    protected $updated;
    protected $size;
    protected $shareId;

    public function __construct() {
        $this->addType('id','integer');
    }
    public function jsonSerialize() {
        return [

            'id'=> $this->id,
            'announce_id'=>$this->announceId,
            'filename'=>$this->filename,
            'fileurl'=>$this->fileurl,
            'filetype'=>$this->filetype,
            'is_eyecatch'=>$this->isEyecatch,
            'href'=>$this->href,
            'has_preview'=>$this->hasPreview,
            'updated'=>$this->updated,
            'size'=>$this->size,
            'share_id'=>$this->shareId,
            'user_id'=>$this->userId,
        ];
    }
    
}