<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 02.12.2018
 * Time: 01:43
 */

namespace Ergo\Domains;

use Ergo\Business\Contact;
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
                    offices_id AS id, offices_email AS email, offices_name AS name,
                    contacts_street AS street, contacts_city AS city, contacts_npa AS npa, contacts_cp AS cp, contacts_phone AS phone, contacts_fax AS fax
                    FROM offices
                    LEFT JOIN contacts ON offices_id = contacts_offices_id
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
            $contacts = [];
            foreach ($data as $contact) {
                $contacts[] = new Contact($contact);
            }
            return new Office($data[0], $contacts);
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
        $orderable = ['name', 'email', 'id'];
        $sortable = ['ASC', 'DESC'];

        $order = $orderable[array_search(strtolower($orderAttribute), $orderable, true) | 0];
        $sort = $sortable[array_search(strtoupper($sortAttribute), $sortable, true) | 0];
        $sql =
            '
                SELECT 
                    offices_id AS id, offices_name AS name, offices_email AS email,
                    contacts_street AS street, contacts_city AS city, contacts_npa AS npa, contacts_cp AS cp, contacts_phone AS phone, contacts_fax AS fax
                    FROM offices
                    LEFT JOIN contacts ON offices_id = contacts_offices_id
                    ORDER BY 
            ' . $order . ' ' . $sort;

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                throw new NoEntityException('No entity found for offices');
            }
            $offices = $officesId = [];
            foreach ($data as $office) {
                if (!in_array($office['id'], $officesId, true)) {
                    $contacts = [];
                    foreach ($data as $contact) {
                        if ($office['id'] === $contact['id']) {
                            $contacts[] = new Contact($contact);
                        }
                    }
                    $offices[] = new Office($office, $contacts);
                    $officesId[] = $office['id'];
                }
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