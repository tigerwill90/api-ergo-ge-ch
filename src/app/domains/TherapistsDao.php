<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 25.02.2019
 * Time: 22:47
 */

namespace Ergo\Domains;

use Ergo\Business\Therapist;
use Ergo\Exceptions\IntegrityConstraintException;
use Ergo\Exceptions\NoEntityException;
use Psr\Log\LoggerInterface;
use PDO;

class TherapistsDao
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
     * @param int $id
     * @return Therapist
     * @throws NoEntityException
     * @throws \PDOException
     */
    public function getTherapist(int $id) : Therapist
    {
        $sql =
            '
                SELECT 
                    therapists_id AS id, therapists_title AS title, therapists_firstname AS firstname, therapists_lastname AS lastname, therapists_home AS home, therapists_offices_id as officeId,
                    therapists_created AS created, therapists_updated AS updated,
                    phones_id AS phoneId ,phones_type AS phoneType, phones_number AS phoneNumber,
                    emails_id AS emailId, emails_address AS emailAddress,
                    categories_id AS categoryId, categories_name AS categoryName, categories_description AS categoryDescription
                        FROM therapists
                        LEFT JOIN phones ON therapists_id = phones_therapists_id
                        LEFT JOIN emails ON therapists_id = emails_therapists_id
                        LEFT JOIN therapistsCategories ON therapists_id = therapistsCategories_therapists_id
                        LEFT JOIN categories ON categories_id = therapistsCategories_categories_id
                        WHERE therapists_id = 
            ' . $id;

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                throw new NoEntityException('No entity found for this therapist id : ' . $id);
            }

            $phonesId = $emailsId = $phones = $emails = $categories = $categoriesId = [];
            foreach ($data as $contact) {
                    if ($contact['phoneId'] !== null && !in_array($contact['phoneId'], $phonesId, true)) {
                        $phones[] = [
                            'type' => $contact['phoneType'],
                            'number' => (string)$contact['phoneNumber']
                        ];
                        $phonesId[] = $contact['phoneId'];
                    }

                    if ($contact['emailId'] !== null && !in_array($contact['emailId'], $emailsId, true)) {
                        $emails[] = $contact['emailAddress'];
                        $emailsId[] = $contact['emailId'];
                    }

                    if ($contact['categoryId'] !== null && !in_array($contact['categoryId'], $categoriesId, true)) {
                        $categories[] = [
                            'id' => (int) $contact['categoryId'],
                            'name' => $contact['categoryName'],
                            'description' => $contact['categoryDescription']
                        ];
                        $categoriesId[] = $contact['categoryId'];
                    }
            }

            return new Therapist($data[0], $phones, $emails, $categories);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * TODO refactor with more efficient method, aka EXIST function
     * @param int $id
     * @return bool
     */
    public function isTherapistExist (int $id) : bool
    {
        $sql = 'SELECT therapists_id FROM therapists WHERE therapists_id = ' . $id;

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
     * Get all information about therapists. An office id parameter can be passed to fetch therapists for a given office
     * @param int|null $officeId
     * @return Therapist[]
     * @throws NoEntityException
     * @throws \PDOException
     */
    public function getTherapists(?int $officeId = null) : array
    {
        $sql =
            '
                SELECT 
                    therapists_id AS id, therapists_title AS title, therapists_firstname AS firstname, therapists_lastname AS lastname, therapists_home AS home,
                    therapists_offices_id AS officeId, therapists_created AS created, therapists_updated AS updated,
                    phones_id AS phoneId ,phones_type AS phoneType, phones_number AS phoneNumber,
                    emails_id AS emailId, emails_address AS emailAddress,
                    categories_id AS categoryId, categories_name AS categoryName, categories_description AS categoryDescription
                        FROM therapists
                        LEFT JOIN phones ON therapists_id = phones_therapists_id
                        LEFT JOIN emails ON therapists_id = emails_therapists_id
                        LEFT JOIN therapistsCategories ON therapists_id = therapistsCategories_therapists_id
                        LEFT JOIN categories ON categories_id = therapistsCategories_categories_id
                ';

        if ($officeId !== null) {
            $sql .= ' WHERE therapists_offices_id = ' . $officeId;
        }

        // TODO order by firstname, lastname, title, home
        $sql .= ' ORDER BY lastname ASC';

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                $message = 'No therapists entity found';
                if ($officeId !== null) {
                    $message .= 'for this office id : ' . $officeId;
                }
                throw new NoEntityException($message);
            }
            return $this->assembleTherapists($data);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param array $data
     * @return Therapist[]
     */
    private function assembleTherapists(array $data): array {
        $therapists = $therapistsId = [];
        foreach ($data as $therapist) {
            if (!in_array($therapist['id'], $therapistsId, true)) {
                $currentTherapist = new Therapist($therapist);

                $phonesId = $emailsId = $phones = $emails = $categories = $categoriesId = [];
                foreach ($data as $contact) {
                    if ($therapist['id'] === $contact['id']) {
                        if ($contact['phoneId'] !== null && !in_array($contact['phoneId'], $phonesId, true)) {
                            $phones[] = [
                                'type' => $contact['phoneType'],
                                'number' => (string)$contact['phoneNumber']
                            ];
                            $phonesId[] = $contact['phoneId'];
                        }

                        if ($contact['emailId'] !== null && !in_array($contact['emailId'], $emailsId, true)) {
                            $emails[] = $contact['emailAddress'];
                            $emailsId[] = $contact['emailId'];
                        }

                        if ($contact['categoryId'] !== null && !in_array($contact['categoryId'], $categoriesId, true)) {
                            $categories[] = [
                                'id' => (int) $contact['categoryId'],
                                'name' => $contact['categoryName'],
                                'description' => $contact['categoryDescription']
                            ];
                            $categoriesId[] = $contact['categoryId'];
                        }
                    }
                }
                $currentTherapist->setPhones($phones);
                $currentTherapist->setEmails($emails);
                $currentTherapist->setCategories($categories);
                $therapists[] = $currentTherapist;
                $therapistsId[] = $therapist['id'];
            }
        }

        return $therapists;
    }

    /**
     * @param string $attribute
     * @return array
     * @throws NoEntityException
     */
    public function searchTherapists(string $attribute) : array {
        $sql = '
                SELECT 
                    therapists_id AS id, therapists_title AS title, therapists_firstname AS firstname, therapists_lastname AS lastname, therapists_home AS home,
                    therapists_offices_id AS officeId, therapists_created AS created, therapists_updated AS updated,
                    phones_id AS phoneId ,phones_type AS phoneType, phones_number AS phoneNumber,
                    emails_id AS emailId, emails_address AS emailAddress,
                    categories_id AS categoryId, categories_name AS categoryName, categories_description AS categoryDescription
                        FROM therapists
                        LEFT JOIN phones ON therapists_id = phones_therapists_id
                        LEFT JOIN emails ON therapists_id = emails_therapists_id
                        LEFT JOIN therapistsCategories ON therapists_id = therapistsCategories_therapists_id
                        LEFT JOIN categories ON categories_id = therapistsCategories_categories_id
                        WHERE therapists_id IN (
                            SELECT DISTINCT 
                                therapists_id
                            FROM therapists
                            LEFT JOIN phones ON therapists_id = phones_therapists_id
                            LEFT JOIN emails ON therapists_id = emails_therapists_id
                            LEFT JOIN therapistsCategories ON therapists_id = therapistsCategories_therapists_id
                            LEFT JOIN categories ON categories_id = therapistsCategories_categories_id
                            WHERE
                                LOWER(therapists_firstname) LIKE :attribute OR
                                LOWER(therapists_lastname) LIKE :attribute OR
                                LOWER(CONCAT(therapists_firstname, \' \', therapists_lastname)) LIKE :attribute OR
                                LOWER(phones_number) LIKE :attribute OR
                                LOWER(emails_address) LIKE :attribute OR
                                LOWER(categories_name) LIKE :attribute OR 
                                LOWER(categories_description) LIKE :attribute
                        )
               ';

        $searchItem = strtolower('%' . $attribute . '%');

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':attribute', $searchItem);
            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                throw new NoEntityException('No therapist entities found for this attribute : ' . $attribute);
            }
            return $this->assembleTherapists($data);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param Therapist $therapist
     * @throws IntegrityConstraintException
     * @throws NoEntityException
     */
    public function createTherapist(Therapist $therapist) : void
    {
        $sql = '
                    INSERT INTO therapists (therapists_title, therapists_firstname, therapists_lastname, therapists_home, therapists_offices_id) 
                    VALUES (:title, :firstname, :lastname, :home, :officeId)
               ';

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $title = $therapist->getTitle();
            $stmt->bindParam(':title', $title);
            $firstname = $therapist->getFirstname();
            $stmt->bindParam(':firstname', $firstname);
            $lastname = $therapist->getLastname();
            $stmt->bindParam(':lastname', $lastname);
            $home = (int) $therapist->isHome();
            $stmt->bindParam(':home', $home);
            $officeId = $therapist->getOfficeId();
            $stmt->bindParam(':officeId', $officeId);
            $stmt->execute();

            $id = (int) $this->pdo->lastInsertId();
            $therapist->setId($id);
            $this->setTherapistDateTime($therapist);
            $this->createEmail($id, $therapist->getEmails());
            $this->createPhone($id, $therapist->getPhones());
            $this->linkTherapistToCategories($therapist);

            $this->pdo->commit();
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            if ((int) $e->getCode() === self::INTEGRITY_CONSTRAINT_VIOLATION) {
                throw new IntegrityConstraintException('Cannot insert new therapist. Office id ' . $therapist->getOfficeId() . ' not found');
            }
            throw $e;
        }
    }

    /**
     * @param Therapist $therapist
     * @throws NoEntityException
     */
    private function setTherapistDateTime(Therapist $therapist): void {
        $sql = 'SELECT therapists_created AS created, therapists_updated AS updated FROM therapists WHERE therapists_id = ' . $therapist->getId();

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                throw new NoEntityException('No entity found for this therapist id : ' . $therapist->getId());
            }
            $therapist->setCreated($data[0]['created']);
            $therapist->setUpdated($data[0]['updated']);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param Therapist $therapist
     * @throws IntegrityConstraintException
     * @throws NoEntityException
     */
    public function updateTherapist(Therapist $therapist) : void
    {
        $sql = '
                    UPDATE therapists SET
                        therapists_title = :title,
                        therapists_firstname = :firstname,
                        therapists_lastname = :lastname,
                        therapists_home = :home,
                        therapists_offices_id = :officeId
                        WHERE therapists_id = :id
               ';

        try {
            $this->pdo->beginTransaction();
            $id = $therapist->getId();
            $this->deletePhoneByTherapistId($id);
            $this->createPhone($id, $therapist->getPhones());
            $this->deleteEmailByTherapistId($id);
            $this->createEmail($id, $therapist->getEmails());
            $this->deleteTherapistToCategoriesLinkByTherapistId($id);
            $this->linkTherapistToCategories($therapist);

            $stmt = $this->pdo->prepare($sql);
            $title = $therapist->getTitle();
            $stmt->bindParam(':title', $title);
            $firstname = $therapist->getFirstname();
            $stmt->bindParam(':firstname', $firstname);
            $lastname = $therapist->getLastname();
            $stmt->bindParam(':lastname', $lastname);
            $home = (int) $therapist->isHome();
            $stmt->bindParam(':home', $home);
            $stmt->bindParam(':id', $id);
            $officeId = $therapist->getOfficeId();
            $stmt->bindParam(':officeId', $officeId);
            $stmt->execute();

            $this->setTherapistDateTime($therapist);

            $this->pdo->commit();

        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            if ((int) $e->getCode() === self::INTEGRITY_CONSTRAINT_VIOLATION) {
                throw new IntegrityConstraintException('Can not update therapist. Office id ' . $therapist->getOfficeId() . ' not found');
            }
            throw $e;
        }
    }

    /**
     * @param int $therapistId
     * @param array $emails
     */
    public function createEmail(int $therapistId, array $emails) : void
    {
        $sql = 'INSERT INTO emails (emails_address, emails_therapists_id) VALUES (:email, :therapistId)';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':therapistId', $therapistId);
            foreach ($emails as $email) {
                $stmt->bindParam(':email', $email);
                $stmt->execute();
            }

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param int $therapistId
     * @param array $phones
     */
    public function createPhone(int $therapistId, array $phones) : void
    {
        $sql = 'INSERT INTO phones (phones_type, phones_number, phones_therapists_id) VALUES (:type, :number, :therapistId)';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':therapistId', $therapistId);
            foreach ($phones as $phone) {
                $stmt->bindParam(':type', $phone['type']);
                $stmt->bindParam(':number', $phone['number']);
                /** @noinspection DisconnectedForeachInstructionInspection */
                $stmt->execute();
            }

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param Therapist $therapist
     * @throws IntegrityConstraintException
     */
    public function linkTherapistToCategories(Therapist $therapist) : void
    {
        $sql = 'INSERT INTO therapistsCategories (therapistsCategories_therapists_id, therapistsCategories_categories_id) VALUES (:therapistId, :categoryId)';

        try {
            $stmt = $this->pdo->prepare($sql);
            $therapistId = $therapist->getId();
            $stmt->bindParam(':therapistId', $therapistId);
            $categories = $therapist->getCategories();
            foreach ($categories as $category) {
                $stmt->bindParam('categoryId', $category['id']);
                /** @noinspection DisconnectedForeachInstructionInspection */
                $stmt->execute();
            }
        } catch (\PDOException $e) {
            if ((int) $e->getCode() === self::INTEGRITY_CONSTRAINT_VIOLATION) {
                throw new IntegrityConstraintException('Can only link therapist to existing categories');
            }
            throw $e;
        }
    }

    /**
     * @param int $id
     * @throws NoEntityException
     */
    public function deleteTherapist(int $id) : void
    {
        $sql = 'DELETE FROM therapists WHERE therapists_id = :id';

        try {
            $this->pdo->beginTransaction();
            $this->deleteEmailByTherapistId($id);
            $this->deletePhoneByTherapistId($id);
            $this->deleteTherapistToCategoriesLinkByTherapistId($id);

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                throw new NoEntityException('No entity found for this therapist id : ' . $id);
            }

            $this->pdo->commit();
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @param int $id
     */
    public function deleteTherapistToCategoriesLinkByTherapistId(int $id) : void
    {
        $sql = 'DELETE FROM therapistsCategories WHERE therapistsCategories_therapists_id = ' . $id;

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param int $id
     */
    public function deletePhoneByTherapistId(int $id) : void
    {
        $sql = 'DELETE FROM phones WHERE phones_therapists_id = ' . $id;

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param int $id
     */
    public function deleteEmailByTherapistId(int $id) : void
    {
        $sql = 'DELETE FROM emails WHERE emails_therapists_id = ' . $id;

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
