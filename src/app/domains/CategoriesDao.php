<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 25.02.2019
 * Time: 13:44
 */

namespace Ergo\domains;

use Ergo\Business\Category;
use Ergo\Exceptions\NoEntityException;
use Psr\Log\LoggerInterface;
use PDO;

class CategoriesDao
{
    /** @var \PDO */
    private $pdo;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(PDO $pdo, LoggerInterface $logger)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    /**
     * @param int $id
     * @return Category
     * @throws NoEntityException
     * @throws \Exception
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
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return Category[]
     * @throws NoEntityException
     * @throws \Exception
     */
    public function getCategories() : array
    {
        $sql = 'SELECT categories_id AS id, categories_name AS name, categories_description AS description FROM categories ORDER BY categories_name ASC';

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
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param int $officeId
     * @return Category[]
     * @throws NoEntityException
     * @throws \Exception
     */
    public function getCategoriesByOffice(int $officeId) : array
    {
        $sql =
            '
                SELECT categories_id AS id, categories_name AS name, categories_description AS description FROM categories
                  JOIN therapistsCategories ON categories_id = therapistsCategories_categories_id
                  JOIN therapists ON therapists_id = therapistsCategories_therapists_id
                  JOIN offices ON offices_id = therapists_offices_id
                  WHERE offices_id = 
            ' . $officeId;

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
            $data = array_unique($stmt->fetchAll(PDO::FETCH_ASSOC), SORT_REGULAR);
            if (empty($data)) {
                throw new NoEntityException('No categories entities found for this office id : ' . $officeId);
            }
            $categories = [];
            foreach ($data as $category) {
                $categories[] = new Category($category);
            }
            return $categories;
        } catch (\Exception $e) {
            throw $e;
        }

    }

    public function createCategory(Category $category): void
    {

    }

    public function updateCategory(Category $category) : void
    {

    }

    public function deleteCategory(int $id) : void
    {

    }

    /**
     * @param string $message
     * @param array $context
     */
    private function log(string $message, array $context = []) : void
    {
        $this->logger->debug($message, $context);
    }
}
