<?php

namespace OCA\WelcomApp\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class FilesMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'welcomapp_files', Files::class);
	}

	/**
	 * @param int $id
	 * @return Entity|Files
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id): Files {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('welcomapp_files')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}
	/**
	 * @param int $fileurl
	 * @return array
	 */
	public function findByAid(string $fileurl): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('welcomapp_files')
			->where($qb->expr()->eq('fileurl', $qb->createNamedParameter($fileurl, IQueryBuilder::PARAM_STR)));
		return $this->findEntities($qb);
	}

	/**
	 * @param string $userId
	 * @return array
	 */
	public function findAll(): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('welcomapp_files');
		return $this->findEntities($qb);
	}
}
