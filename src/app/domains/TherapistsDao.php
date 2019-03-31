<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 25.02.2019
 * Time: 22:47
 */

namespace Ergo\Domains;

use Ergo\Business\Therapist;
use Ergo\Exceptions\NoEntityException;
use Psr\Log\LoggerInterface;
use PDO;

class TherapistsDao
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
     * @param int $id
     * @return Therapist
     * @throws NoEntityException
     * @throws \Exception
     */
    public function getTherapist(int $id) : Therapist
    {
        $sql =
            '
                SELECT 
                    therapists_id AS id, therapists_title AS title, therapists_firstname AS firstname, therapists_lastname AS lastname, therapists_home AS home,
                    officesTherapists_offices_id AS officeId,
                    phones_id AS phoneId ,phones_type AS phoneType, phones_number AS phoneNumber,
                    emails_id AS emailId, emails_address AS emailAddress,
                    categories_id AS categoryId, categories_name AS categoryName, categories_description AS categoryDescription
                        FROM therapists
                        LEFT JOIN officesTherapists ON therapists_id = officesTherapists_therapists_id
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

            $phonesId = $emailsId = $phones = $emails = $categories = $categoriesId = $officesId = [];
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
                            'name' => $contact['categoryName'],
                            'description' => $contact['categoryDescription']
                        ];
                        $categoriesId[] = $contact['categoryId'];
                    }

                    if ($contact['officeId'] !== null && !in_array($contact['officeId'], $officesId, true)) {
                        $officesId[] = $contact['officeId'];
                    }
            }

            return new Therapist($data[0], $phones, $emails, $categories, $officesId);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get all information about therapists. An office id parameter can be passed to fetch therapists for a given office
     * @param int|null $officeId
     * @return Therapist[]
     * @throws NoEntityException
     * @throws \Exception
     */
    public function getTherapists(?int $officeId = null) : array
    {
        $sql =
            '
                SELECT 
                    therapists_id AS id, therapists_title AS title, therapists_firstname AS firstname, therapists_lastname AS lastname, therapists_home AS home,
                    officesTherapists_offices_id AS officeId,
                    phones_id AS phoneId ,phones_type AS phoneType, phones_number AS phoneNumber,
                    emails_id AS emailId, emails_address AS emailAddress,
                    categories_id AS categoryId, categories_name AS categoryName, categories_description AS categoryDescription
                        FROM therapists
                        LEFT JOIN officesTherapists ON therapists_id = officesTherapists_therapists_id
                        LEFT JOIN phones ON therapists_id = phones_therapists_id
                        LEFT JOIN emails ON therapists_id = emails_therapists_id
                        LEFT JOIN therapistsCategories ON therapists_id = therapistsCategories_therapists_id
                        LEFT JOIN categories ON categories_id = therapistsCategories_categories_id
                ';

        if ($officeId !== null) {
            $sql .= ' WHERE officesTherapists_offices_id = ' . $officeId;
        }

        // TODO order by firstname, lastname, title, home
        $sql .= ' ORDER BY lastname ASC';

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                throw new NoEntityException('No therapists entity found for this office id : ' . $officeId);
            }

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
        } catch (\Exception $e) {
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
