<?php

namespace OCA\WelcomApp\Service;

use Exception;
use DateTime;
use DateTimeZone;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\WelcomApp\Db\Note;
use OCA\WelcomApp\Db\NoteMapper;

class NoteService
{
    private $mapper;
    public function __construct(NoteMapper $mapper)
    {
        $this->mapper = $mapper;
    }
    public function findAll(string $userId, int $max)
    {
        return $this->mapper->findAll($userId, $max);
    }
    private function handleException($e)
    {
        if (
            $e instanceof DoesNotExistException ||
            $e instanceof MultipleObjectsReturnedException
        ) {
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }
    public function find(int $id, string $userId,bool $updateflg)
    {
        try {
            return $this->mapper->find($id, $userId, $updateflg);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    public function filter(int $category, int $offset, int $limit, int $pubFlag, int $pinFlag, array $userData, array $tagArray, bool $unread,string $userId)
    {
        if (!$pinFlag) {
            $pinFlag = 0;
        }
        if (!$pubFlag) {
            $pubFlag = 0;
        }
        try {
            $pinFlag = intval($pinFlag);
            $pubFlag = intval($pubFlag);
        } catch (Exception $e) {
            $pinFlag = 0;
            $pubFlag = 1;
        }
        try {
            return $this->mapper->filter($category, $offset, $limit, $pubFlag, $pinFlag, $userData, $tagArray, $unread,$userId);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    public function filtercount(int $category, int $pubFlag, int $pinFlag, array $userData, array $tagArray,bool $unread ,string $userId)
    {
        if (!$pinFlag) {
            $pinFlag = 0;
        }
        if (!$pubFlag) {
            $pubFlag = 0;
        }
        try {
            $pinFlag = intval($pinFlag);
            $pubFlag = intval($pubFlag);
        } catch (Exception $e) {
            $pinFlag = 0;
            $pubFlag = 1;
        }
        try {
            return $this->mapper->filtercount($category, $pubFlag, $pinFlag, $userData, $tagArray,$unread, $userId);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    public function create(
        string $title,
        string $content,
        string $userId,
        int $category,
        bool $pinFlag,
        bool $pubFlag,
        string $tags,
        string $uuid,
        string $shareInfo,
        string $readusers,
        bool $updateflg
    ) {
        if (!$pinFlag) {
            $pinFlag = 0;
        }
        if (!$pubFlag) {
            $pubFlag = 0;
        }
        try {
            $pinFlag = intval($pinFlag);
            $pubFlag = intval($pubFlag);
        } catch (Exception $e) {
            $pinFlag = 0;
            $pubFlag = 0;
        }


        $now = new DateTime();
        $now->setTimeZone(new DateTimeZone('Asia/Tokyo'));
        $date = $now->format('Y-m-d H:i:s');
        //$date=date("Y-m-d H:i:s");
        $note = new Note();
        $note->setTitle($title);
        $note->setContent($content);
        $note->setUserId($userId);
        $note->setCategory($category);
        $note->setPinFlag($pinFlag);
        $note->setPubFlag($pubFlag);
        $note->setTags($tags);
        $note->setCreated($date);
        $note->setUpdated($date);
        $note->setUuid($uuid);
        $note->setShareInfo($shareInfo);
        $note->setReadusers($readusers);
        return $this->mapper->insert($note);
    }
    public function update(int $id, string $title, string $content, string $userId, int $category, int $pinFlag, int $pubFlag, string $tags, string $uuid, string $shareInfo, string $readusers, bool $updateflg)
    {
        if (!$pinFlag) {
            $pinFlag = 0;
        }
        if (!$pubFlag) {
            $pubFlag = 0;
        }
        try {
            $pinFlag = intval($pinFlag);
            $pubFlag = intval($pubFlag);
        } catch (Exception $e) {
            $pinFlag = 0;
            $pubFlag = 0;
        }
        try {
            $note = $this->mapper->find($id, $userId,$updateflg);
            if ($updateflg) {
                $now = new DateTime();
                $now->setTimeZone(new DateTimeZone('Asia/Tokyo'));
                $date = $now->format('Y-m-d H:i:s');
                $note->setUpdated($date);
            }
            #$date=date("Y-m-d H:i:s");
            $note->setTitle($title);
            $note->setContent($content);
            //$note->setUserId($userId);
            $note->setCategory($category);
            $note->setPinFlag($pinFlag);
            $note->setPubFlag($pubFlag);
            $note->setTags($tags);
            $note->setUuid($uuid);
            $note->setShareInfo($shareInfo);
            $note->setReadusers($readusers);
            return $this->mapper->update($note);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    public function delete(int $id, string $userId)
    {
        try {
            $note = $this->mapper->find($id, $userId,true);
            $this->mapper->delete($note);
            return $note;
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
}
