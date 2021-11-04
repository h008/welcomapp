<?php

namespace OCA\WelcomApp\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use DoctrineExtensions\Query\Mysql\FindInSet;
use OCP\Diagnostics\IQuery;

class NoteMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'welcomapp', Note::class);
	}

	/**
	 * @param int $id
	 * @param string $userId
	 * @return Entity|Note
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id, string $userId,bool $updateflg): Note {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('welcomapp')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		if($updateflg){
			$qb->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
		}

		return $this->findEntity($qb);
	}

	/**
	 * @param string $userId
	 * @param int $max
	 * @return array
	 */
	public function findAll(string $userId,int $max): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('welcomapp')
			// ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('updated', 'DESC')
			->setMaxResults($max);
		return $this->findEntities($qb);
	}
	/**
	 * @param int $category
	 * @param int $offset
	 * @param int $limit
	 * @return array
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function filter(int $category, int $offset,int $limit,int $pubFlag,int $pinFlag,array $userData,array $tagArray,bool $unread,string $userId ): array {
	//$config->addCustomStringFunction('FINDINSET','DoctrineExtentions\Query\MySql\FindInSet');	
		/* @var $qb1 IQueryBuilder */
		 //$qb1= $this->db->getQueryBuilder();
 //$qb1->select('*')
		// 	->from('group_user');
		// $this->findEntities($qb1);
		/* @var $qb IQueryBuilder */
		
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('welcomapp')
			->orderBy('updated','DESC');
		if($pubFlag==0){
			$qb->where($qb->expr()->orX(
				$qb->expr()->eq('pub_flag', $qb->createNamedParameter(0,IQueryBuilder::PARAM_INT)),
				$qb->expr()->isNull('pub_flag'),
				$qb->expr()->eq('pub_flag',$qb->createNamedParameter(''))

			));
			$qb->andWhere($qb->expr()->eq('user_id',$qb->createNamedParameter($userId)));
		}else{
			$qb->where($qb->expr()->eq('pub_flag',$qb->createNamedParameter(1,IQueryBuilder::PARAM_INT)));

		}
		if($category != 0){
			$qb->andWhere($qb->expr()->eq('category', $qb->createNamedParameter($category, IQueryBuilder::PARAM_INT)));
		}
		if($pinFlag){
			$qb->andWhere($qb->expr()->eq('pin_flag',$qb->createNamedParameter($pinFlag,IQueryBuilder::PARAM_INT)));
		}

		if($limit){
			$qb->setMaxResults($limit);
		}
		if($offset){
			$qb->setFirstResult($offset);
		}
		if($userData && $userData['groups'] && count($userData['groups'])>0){
			$groups=$userData['groups'];
			$orX = $qb->expr()->orX();
			$orX->add($qb->expr()->eq('user_id',$qb->createNamedParameter($userData['id'])));
			foreach($groups as $groupId ) {

				$orX->add($qb->expr()->like('share_info',$qb->createNamedParameter('%"gid":"'.$groupId.'"%')));
			}
			$qb->andWhere($orX);
		}
		if($tagArray[0]!="all"){
			$orX=$qb->expr()->orX();
			foreach($tagArray as $tagId) {
				$orX->add(' FIND_IN_SET ('. (int) $tagId. ',tags ) ');
			}
			$qb->andWhere($orX );
			
		}
		if($unread){
		$isread="NOT FIND_IN_SET('".$userId."',readusers)";
			$qb->andWhere($isread);
		}
		//$qb->andWhere('FindInSet("1,2,3",1)');

		return $this->findEntities($qb);
	}
	/**
	 * @param int $category
	 * @return int
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function filtercount(int $category,int $pubFlag,int $pinFlag,array $userData ,array $tagArray,bool $unread,string $userId): int {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('id')
			->from('welcomapp');
		if($pubFlag==0){
			$qb->where($qb->expr()->orX(
				$qb->expr()->eq('pub_flag', $qb->createNamedParameter(0,IQueryBuilder::PARAM_INT)),
				$qb->expr()->isNull('pub_flag'),
				$qb->expr()->eq('pub_flag',$qb->createNamedParameter(''))
			));
			$qb->andWhere($qb->expr()->eq('user_id',$qb->createNamedParameter($userId)));
		}else{
			$qb->where($qb->expr()->eq('pub_flag',$qb->createNamedParameter(1,IQueryBuilder::PARAM_INT)));
		}
		if($category != 0){
			$qb->andWhere($qb->expr()->eq('category', $qb->createNamedParameter($category, IQueryBuilder::PARAM_INT)));
		}
		if($pinFlag){
			$qb->andWhere($qb->expr()->eq('pin_flag',$qb->createNamedParameter($pinFlag,IQueryBuilder::PARAM_INT)));
		}
		if($userData && $userData['groups'] && count($userData['groups'])>0){
			$groups=$userData['groups'];
			$orX = $qb->expr()->orX();
			$orX->add($qb->expr()->eq('user_id',$qb->createNamedParameter($userData['id'])));
			foreach($groups as $groupId ) {
				$orX->add($qb->expr()->like('share_info',$qb->createNamedParameter('%"gid":"'.$groupId.'"%')));
			}
			$qb->andWhere($orX);
		}
		if($tagArray[0]!="all"){
			$orX2=$qb->expr()->orX();
			foreach($tagArray as $tagId) {
				$orX2->add(' FIND_IN_SET ('.(int) $tagId. ',tags ) ');
			}
			$qb->andWhere($orX2 );
			
		}
		if($unread){
		$isread="NOT FIND_IN_SET('".$userId."',readusers)";
			$qb->andWhere($isread);
		}
		//$count =$this-db->excuteQuery($qb);
		//return $count;
		return count($this->findEntities($qb));
	}
}
