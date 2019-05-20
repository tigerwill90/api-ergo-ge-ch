<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 25.02.2019
 * Time: 13:44
 */

namespace Ergo\domains;

use Ergo\Business\Category;
use Ergo\Exceptions\IntegrityConstraintException;
use Ergo\Exceptions\NoEntityException;
use Ergo\Exceptions\UniqueException;
use Psr\Log\LoggerInterface;
use PDO;

class CategoriesDao
{
    /** @var \PDO */
    private $pdo;

    /** @var LoggerInterface */
    private $logger;

    private const INTEGRITY_CONSTRAINT_VIOLATION = 23000;

    public function __construct(PDO $pdo, LoggerInterface $logger)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    /**
     * @param int $id
     * @return Category
     * @throws NoEntityException
     * @throws \PDOException
     */
    public function getCategory(int $id) : Category
    {
        $sql =
            '
                SELECT categories_id AS id, categories_name AS name, categories_description AS description FROM categories
                WHERE categories_id = 
            ' . $id;

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                throw new NoEntityException('No entity found for this category id : ' . $id);
            }
            return new Category($data[0]);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @return Category[]
     * @throws NoEntityException
     * @throws \PDOException
     */
    public function getCategories() : array
    {
        $sql = 'SELECT categories_id AS id, categories_name AS name, categories_description AS description FROM categories ORDER BY categories_name';

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                throw new NoEntityException('No entity found for categories');
            }
            $categories = [];
            foreach ($data as $category) {
                $categories[] = new Category($category);
            }
            return $categories;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param int $officeId
     * @return Category[]
     * @throws NoEntityException
     * @throws \PDOException
     */
    public function getCategoriesByOffice(int $officeId) : array
    {
        $sql =
            '
                SELECT DISTINCT categories_id AS id, categories_name AS name, categories_description AS description FROM categories
                  JOIN therapistsCategories ON categories_id = therapistsCategories_categories_id
                  JOIN therapists ON therapists_id = therapistsCategories_therapists_id
                  WHERE therapists_offices_id = 
            ' . $officeId . ' ORDER BY categories_name';

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                throw new NoEntityException('No categories entities found for this office id : ' . $officeId);
            }
            $categories = [];
            foreach ($data as $category) {
                $categories[] = new Category($category);
            }
            return $categories;
        } catch (\PDOException $e) {
            throw $e;
        }

    }

    /**
     * @param Category $category
     * @throws UniqueException
     */
    public function createCategory(Category $category): void
    {
        $sql = 'INSERT INTO categories (categories_name, categories_description) VALUES (:name, :description)';

        try {
            $stmt = $this->pdo->prepare($sql);
            $name = $category->getName();
            $stmt->bindParam(':name', $name);
            $description = $category->getDescription();
            $stmt->bindParam(':description', $description);
            $stmt->execute();
            $category->setId((int) $this->pdo->lastInsertId());
        } catch (\PDOException $e) {
            if ((int) $e->getCode() === self::INTEGRITY_CONSTRAINT_VIOLATION) {
                throw new UniqueException('This category name already exist', $e->getCode());
            }
            throw $e;
        }
    }

    /**
     * @param Category $category
     * @throws UniqueException
     */
    public function updateCategory(Category $category) : void
    {
        $sql = 'UPDATE categories SET categories_name = :name, categories_description = :description WHERE categories_id = :id';

        try {
            $stmt = $this->pdo->prepare($sql);
            $name = $category->getName();
            $stmt->bindParam(':name', $name);
            $description = $category->getDescription();
            $stmt->bindParam(':description', $description);
            $id = $category->getId();
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (\PDOException $e) {
            if ((int) $e->getCode() === self::INTEGRITY_CONSTRAINT_VIOLATION) {
                throw new UniqueException('This category name already exist', $e->getCode());
            }
            throw $e;
        }
    }

    /**
     * @param int $id
     * @throws NoEntityException
     * @throws IntegrityConstraintException
     */
    public function deleteCategory(int $id) : void
    {
        $sql = 'DELETE FROM categories WHERE categories_id = :id';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                throw new NoEntityException('No entity found for this category id : ' . $id);
            }
        } catch (\PDOException $e) {
            if ((int) $e->getCode() === self::INTEGRITY_CONSTRAINT_VIOLATION) {
                throw new IntegrityConstraintException('Cannot delete a category linked to one or many therapists');
            }
            throw $e;
        }
    }

    /**
     * @param string $message
     * @param array $context
     */
    private function log(string $message, array $context = []) : void
    {
        if ($this->logger !== null) {
            $this->logger->debug($message, $context);
        }
    }
}
