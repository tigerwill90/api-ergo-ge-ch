<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 02.12.2018
 * Time: 01:43
 */

namespace Ergo\Domains;

use Ergo\Business\Office;
use Ergo\Exceptions\NoEntityException;
use PDO;
use Psr\Log\LoggerInterface;

class OfficesDao
{
    /* @var PDO */
    private $pdo;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(PDO $pdo, LoggerInterface $logger = null)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    /**
     * @param string $attribute
     * @return Office
     * @throws NoEntityException
     * @throws \Exception
     */
    public function getOffice(string $attribute) : Office
    {
        $sql =
            '
                SELECT DISTINCT 
                    offices_id AS id, offices_address AS address, offices_npa AS npa, offices_city AS city, offices_cp AS cp, offices_name AS name,
                    offices_phone AS phone, offices_fax AS fax, offices_email AS email, offices_district AS district
                    FROM offices
                    WHERE offices_id = :attribute OR offices_name = :attribute
            ';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':attribute', $attribute);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                throw new NoEntityException('No entity found for this office attribute : ' . $attribute);
            }
            return new Office($data[0]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param string|null $orderAttribute
     * @param string|null $sortAttribute
     * @return array
     * @throws NoEntityException
     * @throws \Exception
     */
    public function getOffices(?string $orderAttribute = 'name', ?string $sortAttribute = 'ASC') : array
    {
        $orderable = ['name', 'npa', 'district'];
        $sortable = ['ASC', 'DESC'];

        $order = $orderable[array_search(strtolower($orderAttribute), $orderable, true) | 0];
        $sort = $sortable[array_search(strtoupper($sortAttribute), $sortable, true) | 0];
        $sql =
            '
                SELECT 
                    offices_id AS id, offices_address AS address, offices_npa AS npa, offices_city AS city, offices_cp AS cp, offices_name AS name,
                    offices_phone AS phone, offices_fax AS fax, offices_email AS email, offices_district AS district
                    FROM offices
                    ORDER BY 
            ' . $order . ' ' . $sort;

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                throw new NoEntityException('No entity found for offices');
            }
            $offices = [];
            foreach ($data as $office) {
                $offices[] = new Office($office);
            }
            return $offices;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateOffice(Office $office) : void
    {

    }

    public function createOffice(Office $office) : void
    {

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