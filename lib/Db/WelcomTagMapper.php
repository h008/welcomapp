<?php

namespace OCA\WelcomApp\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class WelcomTagMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'welcomapp_tags', WelcomTag::class);
	}

	/**
	 * @param int $id
	 * @return Entity|WelcomTag
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id): WelcomTag {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('welcomapp_tags')
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
			->from('welcomapp_tags')
			->orderBy('tag_order', 'ASC');
		return $this->findEntities($qb);
	}
}
