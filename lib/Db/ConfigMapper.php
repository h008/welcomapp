<?php

namespace OCA\WelcomApp\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class ConfigMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'welcomapp_config', Config::class);
	}

	/**
	 * @param int $id
	 * @return Entity|Config
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id): Config {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('welcomapp_config')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}

	/**
	 * @param string $userId
	 * @return array
	 */
	public function findAll(): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('welcomapp_config');
		return $this->findEntities($qb);
	}
	/**
	 * @param string $kind
	 * @return array 
	 */
	public function findByKind(string $kind):array{
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('welcomapp_config')
			->where($qb->expr()->eq('kind',$qb->createNamedParameter($kind,IQueryBuilder::PARAM_STR)));
		return $this->findEntities($qb);
	}
}
