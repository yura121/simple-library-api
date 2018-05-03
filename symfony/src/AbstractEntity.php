<?php

namespace App;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
abstract class AbstractEntity
{
    /** @var EntityManager|null */
    protected static $em;

    const PRODUCT_TYPE__BOOK = 0;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    protected $createdAt;

    public static function setEntityManager(EntityManager $em)
    {
        static::$em = $em;
    }

    /**
     * Triggered only on insert
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime("now");
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Used for generated/virtual columns
     * @param string $className
     * @param array $findParams
     * @return object|null
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function findOneBy(string $className, array $findParams)
    {
        $where = [];
        foreach ($findParams as $name => $value) {
            $where[] = "$name = :$name";
        }

        if (!count($where)) {
            return null;
        }

        $tableName = static::$em->getClassMetadata($className)->getTableName();
        $whereStr = implode(' AND ', $where);
        $sql = "SELECT id FROM {$tableName} WHERE $whereStr LIMIT 1";
        $connection = static::$em->getConnection();
        $statement = $connection->prepare($sql);
        foreach ($findParams as $name => $value) {
            $statement->bindValue($name, $value);
        }
        $statement->execute();
        $entityId = $statement->fetchColumn();

        if (empty($entityId)) {
            return null;
        }

        return static::$em->getRepository($className)
            ->findOneBy(['id' => $entityId]);
    }
}
