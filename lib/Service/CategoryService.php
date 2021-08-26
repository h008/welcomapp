<?php
namespace OCA\WelcomApp\Service;
use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\WelcomApp\Db\Category;
use OCA\WelcomApp\Db\CategoryMapper;

class CategoryService {
    private $mapper;
    public function __construct(CategoryMapper $mapper){
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
    public function create(string $category_name,int $category_order,string $color){
        $category=new Category();
        $category->setCategoryName($category_name);
        $category->setCategoryOrder($category_order);
        $category->setColor($color);
        return $this->mapper->insert($category);
    }
    public function update(int $id,string $category_name, int $category_order,string $color){
        try {
            $category = $this->mapper->find($id);
            $category->setCategoryName($category_name);
            $category->setCategoryOrder($category_order);
            $category->setColor($color);
            return $this->mapper->update($category);
        } catch(Exception $e){
            $this->handleException($e);
        }
    }
    public function delete(int $id){
        try{
            $category= $this->mapper->find($id);
            $this->mapper->delete($category);
            return $category;
        } catch(Exception $e){
            $this->handleException($e);
        }
    }


}