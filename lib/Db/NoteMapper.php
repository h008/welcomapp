<?php

namespace OCA\WelcomApp\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

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
	public function find(int $id, string $userId): Note {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('welcomapp')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
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
	public function filter(int $category, int $offset,int $limit,bool $pubFlag,bool $pinFlag,string $userId): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('welcomapp')
			->orderBy('updated','DESC');
		if(!$pubFlag){
			$qb->where($qb->expr()->orX(
				$qb->expr()->eq('pub_flag', $qb->createNamedParameter($pubFlag,IQueryBuilder::PARAM_INT)),
				$qb->expr()->isNull('pub_flag'),
				$qb->expr()->eq('pub_flag','')

			));
			$qb->andWhere($qb->expr()->eq('user_id',$qb->createNamedParameter($userId)));
		}else{
			$qb->where($qb->expr()->orX(
				$qb->expr()->eq('pub_flag',$qb->createNamedParameter($pubFlag,IQueryBuilder::PARAM_INT)),
				$qb->expr()->eq('pub_flag',$qb->createNamedParameter($pubFlag,IQueryBuilder::PARAM::BOOL)),
			));

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

		return $this->findEntities($qb);
	}
	/**
	 * @param int $category
	 * @return int
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function filtercount(int $category,bool $pubFlag,bool $pinFlag,string $userId): int {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('id')
			->from('welcomapp');
		if(!$pubFlag){
			$qb->where($qb->expr()->orX(
				$qb->expr()->eq('pub_flag', $qb->createNamedParameter($pubFlag,IQueryBuilder::PARAM_INT)),
				$qb->expr()->isNull('pub_flag'),
				$qb->expr()->eq('pub_flag','')
			));
			$qb->andWhere($qb->expr()->eq('user_id',$qb->createNamedParameter($userId)));
		}else{
			$qb->where($qb->expr()->eq('pub_flag',$qb->createNamedParameter($pubFlag,IQueryBuilder::PARAM_INT)));
		}
		if($category != 0){
			$qb->andWhere($qb->expr()->eq('category', $qb->createNamedParameter($category, IQueryBuilder::PARAM_INT)));
		}
		if($pinFlag){
			$qb->andWhere($qb->expr()->eq('pin_flag',$qb->createNamedParameter($pinFlag,IQueryBuilder::PARAM_INT)));
		}
		//$count =$this-db->excuteQuery($qb);
		//return $count;
		return count($this->findEntities($qb));
	}
}
