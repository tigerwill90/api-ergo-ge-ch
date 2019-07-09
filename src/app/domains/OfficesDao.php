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
use Ergo\Exceptions\IntegrityConstraintException;
use Ergo\Exceptions\NoEntityException;
use Ergo\Exceptions\UniqueException;
use PDO;
use Psr\Log\LoggerInterface;

class OfficesDao
{
    /* @var PDO */
    private $pdo;

    /** @var LoggerInterface */
    private $logger;

    private const INTEGRITY_CONSTRAINT_VIOLATION = 23000;

    public function __construct(PDO $pdo, LoggerInterface $logger = null)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    /**
     * @param string $attribute
     * @param string $param
     * @return Office
     * @throws NoEntityException
     */
    public function getOffice(string $attribute, string $param = 'id') : Office
    {
        $sql =
            '
                SELECT DISTINCT 
                    offices_id AS id, offices_email AS email, offices_name AS name, offices_web_url AS web,
                    contacts_street AS street, contacts_city AS city, contacts_npa AS npa, contacts_cp AS cp, contacts_phone AS phone, contacts_fax AS fax
                    FROM offices
                    LEFT JOIN contacts ON offices_id = contacts_offices_id
                    WHERE ' . ($param === 'name' ? 'offices_name = :attribute' : 'offices_id = :attribute') . '
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
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * TODO refactor with more efficient method, aka EXIST function
     * @param int $id
     * @return bool
     */
    public function isOfficeExist(int $id) : bool {
        $sql = 'SELECT offices_id FROM offices WHERE offices_id = ' . $id;

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                return false;
            }
            return true;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param array $officesId
     * @return array
     * @throws NoEntityException
     */
    public function getOfficeNameByOfficesId(array $officesId) : array
    {
        $sql = 'SELECT DISTINCT offices_name AS name FROM offices WHERE offices_id = :id';

        try {
            $stmt = $this->pdo->prepare($sql);
            $notFound = [];
            $officesName = [];
            foreach ($officesId as $officeId) {
                $stmt->bindParam(':id', $officeId);
                $stmt->execute();
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (empty($data)) {
                    $notFound[] = $officeId;
                }
                $officesName[] = $data[0]['name'];
            }

            if (!empty($notFound)) {
                throw new NoEntityException('No entity found for their offices id : [' . implode(', ', $notFound) . ']');
            }
            return $officesName;

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param string|null $orderAttribute
     * @param string|null $sortAttribute
     * @return array
     * @throws NoEntityException
     * @throws \PDOException
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
                    offices_id AS id, offices_name AS name, offices_email AS email, offices_web_url AS web,
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
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param int $id
     * @param string|null $orderAttribute
     * @param string|null $sortAttribute
     * @return array
     * @throws NoEntityException
     */
    public function getOfficesByUserId(int $id, ?string $orderAttribute = 'name', ?string $sortAttribute = 'ASC'): array
    {
        $orderable = ['name', 'email', 'id'];
        $sortable = ['ASC', 'DESC'];

        $order = $orderable[array_search(strtolower($orderAttribute), $orderable, true) | 0];
        $sort = $sortable[array_search(strtoupper($sortAttribute), $sortable, true) | 0];
        $sql =
            '
                SELECT 
                    offices_id AS id, offices_name AS name, offices_email AS email, offices_web_url AS web,
                    contacts_street AS street, contacts_city AS city, contacts_npa AS npa, contacts_cp AS cp, contacts_phone AS phone, contacts_fax AS fax
                    FROM offices
                    LEFT JOIN officesUsers ON offices_id = officesUsers_offices_id
                    LEFT JOIN contacts ON offices_id = contacts_offices_id
                    WHERE officesUsers_users_id = ' . $id .'
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
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param Office $office
     * @throws UniqueException
     */
    public function updateOffice(Office $office) : void
    {
        $sql = '
                    UPDATE offices SET
                        offices_name = :name,
                        offices_email = :email,
                        offices_web_url = :web
                        WHERE offices_id = :id
               ';

        try {
            $this->pdo->beginTransaction();

            // delete and create contact on transaction
            $this->deleteContactByOfficeId($office->getId());
            $this->createContact($office->getId(), $office->getContacts());

            $stmt = $this->pdo->prepare($sql);
            $name = $office->getName();
            $stmt->bindParam(':name', $name);
            $email = $office->getEmail();
            $stmt->bindParam(':email', $email);
            $webUrl = $office->getWebUrl();
            $stmt->bindParam(':web', $webUrl);
            $id = $office->getId();
            $stmt->bindParam(':id',$id);
            $stmt->execute();

            $this->pdo->commit();
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            if ((int) $e->getCode() === self::INTEGRITY_CONSTRAINT_VIOLATION) {
                if (!empty($office->getEmail()) && strpos($e->getMessage(), (string) $office->getEmail()) !== false) {
                    throw new UniqueException('This office email already exist', $e->getCode());
                }

                if (strpos($e->getMessage(), $office->getName()) !== false) {
                    throw new UniqueException('This office name already exist', $e->getCode());
                }

                if (!empty($office->getWebUrl()) && strpos($e->getMessage(), (string) $office->getWebUrl()) !== false) {
                    throw new UniqueException('This office web url already exist', $e->getCode());
                }
            }
            throw $e;
        }
    }

    /**
     * @param Office $office
     * @throws UniqueException
     */
    public function createOffice(Office $office) : void
    {
        $sql = 'INSERT INTO offices (offices_name, offices_email, offices_web_url) values (:name, :email, :web)';

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $name = $office->getName();
            $stmt->bindParam(':name', $name);
            $email = $office->getEmail();
            $stmt->bindParam(':email', $email);
            $webUrl = $office->getWebUrl();
            $stmt->bindParam(':web', $webUrl);
            $stmt->execute();

            $office->setId((int) $this->pdo->lastInsertId());
            $this->createContact($office->getId(), $office->getContacts());

            $this->pdo->commit();

        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            if ((int) $e->getCode() === self::INTEGRITY_CONSTRAINT_VIOLATION) {
                if (!empty($office->getEmail()) && strpos($e->getMessage(), $office->getEmail()) !== false) {
                    throw new UniqueException('This office email already exist', $e->getCode());
                }

                if (strpos($e->getMessage(), $office->getName()) !== false) {
                    throw new UniqueException('This office name already exist', $e->getCode());
                }

                if (!empty($office->getWebUrl()) && strpos($e->getMessage(), $office->getWebUrl()) !== false) {
                    throw new UniqueException('This office web url already exist', $e->getCode());
                }
            }
            throw $e;
        }
    }

    /**
     * @param int $officeId
     * @param Contact[] $contacts
     */
    public function createContact(int $officeId, array $contacts) : void
    {
        $sql = '
                    INSERT INTO contacts (contacts_street, contacts_city, contacts_npa, contacts_cp, contacts_phone, contacts_fax, contacts_offices_id) 
                        VALUES (:street, :city, :npa, :cp, :phone, :fax, :officeId) 
               ';

        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($contacts as $contact) {
                $street = $contact->getStreet();
                $stmt->bindParam(':street', $street);
                $city = $contact->getCity();
                $stmt->bindParam(':city', $city);
                $npa = $contact->getNpa();
                $stmt->bindParam(':npa', $npa);
                $cp = $contact->getCp();
                $stmt->bindParam(':cp', $cp);
                $phone = $contact->getPhone();
                $stmt->bindParam(':phone', $phone);
                $fax = $contact->getFax();
                $stmt->bindParam(':fax', $fax);
                $stmt->bindParam(':officeId', $officeId);
                $stmt->execute();
            }
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param int $id
     * @throws NoEntityException
     * @throws IntegrityConstraintException
     */
    public function deleteOffice(int $id) : void
    {
        $sql = 'DELETE FROM offices WHERE offices_id = :id';

        try {
            $this->pdo->beginTransaction();
            $this->deleteContactByOfficeId($id);
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                throw new NoEntityException('No entity found for this office id : ' . $id);
            }
            $this->pdo->commit();
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            if ((int) $e->getCode() === self::INTEGRITY_CONSTRAINT_VIOLATION) {
                throw new IntegrityConstraintException('Cannot delete an office with therapists');
            }
            throw $e;
        }
    }

    /**
     * @param int $id
     */
    public function deleteContactByOfficeId(int $id) : void
    {
        $sql = 'DELETE FROM contacts WHERE contacts_offices_id = ' . $id;

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
        } catch (\PDOException $e) {
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