<?php

namespace Ergo\Domains;

use Ergo\Business\User;
use Ergo\Exceptions\NoEntityException;
use Ergo\Exceptions\UniqueException;
use Psr\Log\LoggerInterface;
use PDO;

class UsersDao
{
    /** @var PDO  */
    private $pdo;

    /** @var LoggerInterface  */
    private $logger;

    private const INTEGRITY_CONSTRAINT_VIOLATION = 23000;

    public function __construct(PDO $pdo, LoggerInterface $logger = null)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    /**
     * @param string $attribute
     * @return User
     * @throws \PDOException
     * @throws NoEntityException
     */
    public function getUser(string $attribute): User
    {
        $sql = '
                    SELECT DISTINCT 
                        users_id AS id, users_email AS email, users_hashed_password AS hashedPassword, users_roles AS roles,
                        users_firstname as firstname, users_lastname as lastname, users_active as active,
                        offices_id AS officeId, offices_name AS officeName
                        FROM users
                        LEFT JOIN officesUsers ON users_id = officesUsers_users_id
                        LEFT JOIN offices ON offices_id = officesUsers_offices_id
                        WHERE users_id = :attribute OR users_email = :attribute
               ';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':attribute', $attribute);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                throw new NoEntityException('No entity found for this user attribute : ' . $attribute);
            }
            $officesId = $officesName = [];
            foreach ($data as $office) {
                if (!empty($office['officeId'])) {
                    $officesId[] = $office['officeId'];
                }
                if (!empty($office['officeName'])) {
                    $officesName[] = $office['officeName'];
                }
            }
            return new User($data[0], $officesId, $officesName);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param string|null $orderAttribute
     * @param string|null $sortAttribute
     * @return User[]
     * @throws NoEntityException
     */
    public function getUsers(?string $orderAttribute = 'lastname', ?string $sortAttribute = 'ASC'): array
    {
        $orderable = ['lastname', 'firstname', 'email'];
        $sortable = ['ASC', 'DESC'];
        $order = $orderable[array_search(strtolower(str_replace('_', '', $orderAttribute)), $orderable, true) | 0];
        $sort = $sortable[array_search(strtoupper($sortAttribute), $sortable, true) | 0];
        $sql = '
                SELECT users_id as id, users_email as email, users_hashed_password as hashedPassword, users_roles as roles,
                    users_firstname as firstname, users_lastname as lastname, users_active as active
                    FROM users
                    ORDER BY ' . $order . ' ' . $sort;

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                throw new NoEntityException('No entity found for users');
            }

            $users = [];
            foreach ($data as $user) {
                $users[] = new User($user);
            }

            return $users;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param string $email
     * @return User
     * @throws \PDOException
     * @throws NoEntityException
     */
    public function authenticateUser(string $email): User
    {
        $sql = '
                    SELECT DISTINCT 
                        users_id AS id, users_email AS email, users_hashed_password AS hashedPassword, users_roles AS roles, users_firstname as firstname, users_lastname as lastname, users_active as active,
                        offices_id AS officeId, offices_name AS officeName
                        FROM users
                        LEFT JOIN officesUsers ON users_id = officesUsers_users_id
                        LEFT JOIN offices ON offices_id = officesUsers_offices_id
                        WHERE users_email = :email
               ';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                throw new NoEntityException('No entity found for this user email : ' . $email);
            }
            $officesId = $officesName = [];
            foreach ($data as $office) {
                if (!empty($office['officeId'])) {
                    $officesId[] = $office['officeId'];
                }
                if (!empty($office['officeName'])) {
                    $officesName[] = $office['officeName'];
                }
            }
            $this->log(print_r($data, true));
            $this->log(print_r($officesId, true));
            return new User($data[0], $officesId, $officesName);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param User $user
     * @throws \PDOException
     * @throws UniqueException
     */
    public function createUser(User $user) : void
    {
        $sql = 'INSERT INTO users (users_email, users_hashed_password, users_roles, users_firstname, users_lastname, users_active) VALUES (:email, :hashedPassword, :roles, :firstname, :lastname, :active)';

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare($sql);
            $email = $user->getEmail();
            $stmt->bindParam(':email', $email);
            $hashedPassword = $user->getHashedPassword();
            $stmt->bindParam(':hashedPassword', $hashedPassword);
            $roles = $user->getRoles();
            $stmt->bindParam(':roles', $roles);
            $firstname = $user->getFirstname();
            $stmt->bindParam(':firstname', $firstname);
            $lastname = $user->getLastname();
            $stmt->bindParam(':lastname', $lastname);
            $active = (int) $user->getActive();
            $stmt->bindParam(':active', $active);
            $stmt->execute();
            $user->setId((int)$this->pdo->lastInsertId());

            if (!empty($user->getOfficesId())) {
                $this->linkUserToOffices($user);
            }

            $this->pdo->commit();
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            if ((int) $e->getCode() === self::INTEGRITY_CONSTRAINT_VIOLATION) {
                if (strpos($e->getMessage(), $user->getEmail()) !== false) {
                    throw new UniqueException('This user email already exist', $e->getCode());
                }
            }
            throw $e;
        }
    }

    /**
     * @param User $user
     * @throws \PDOException
     */
    public function linkUserToOffices(User $user) : void
    {
        $sql = 'INSERT INTO officesUsers (officesUsers_users_id, officesUsers_offices_id) VALUES (:userId, :officeId)';

        try {
            $stmt = $this->pdo->prepare($sql);
            $userId = $user->getId();
            $stmt->bindParam(':userId', $userId);
            $officesId = $user->getOfficesId();
            foreach ($officesId as $officeId) {
                $stmt->bindParam('officeId', $officeId);
                $stmt->execute();
            }
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param int $idlastname
     */
    public function deleteUserToOfficesLinkByUserId(int $id): void
    {
        $sql = 'DELETE FROM officesUsers WHERE officesUsers_users_id = ' . $id;

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param User $user
     * @throws UniqueException
     */
    public function updateUser(User $user) : void
    {
       $sql = '
                UPDATE users SET
                    users_email = :email,
                    users_hashed_password = :hashedPassword,
                    users_roles = :roles,
                    users_firstname = :firstname,
                    users_lastname = :lastname,
                    users_active = :active
                    WHERE users_id = :id
              ';

       try {
           $this->pdo->beginTransaction();
           $stmt = $this->pdo->prepare($sql);
           $email = $user->getEmail();
           $stmt->bindParam(':email', $email);
           $hashedPassword = $user->getHashedPassword();
           $stmt->bindParam(':hashedPassword', $hashedPassword);
           $roles = $user->getRoles();
           $stmt->bindParam(':roles', $roles);
           $firstname = $user->getFirstname();
           $stmt->bindParam(':firstname', $firstname);
           $lastname = $user->getLastname();
           $stmt->bindParam(':lastname', $lastname);
           $id = $user->getId();
           $stmt->bindParam(':id', $id);
           $active = (int) $user->getActive();
           $stmt->bindParam(':active', $active);
           $stmt->execute();
           $this->deleteUserToOfficesLinkByUserId($user->getId());

           if (!empty($user->getOfficesId())) {
               $this->linkUserToOffices($user);
           }

           $this->pdo->commit();
       } catch (\PDOException $e) {
           $this->pdo->rollBack();
           if ((int) $e->getCode() === self::INTEGRITY_CONSTRAINT_VIOLATION) {
               if (strpos($e->getMessage(), $user->getEmail()) !== false) {
                   throw new UniqueException('This user email already exist', $e->getCode());
               }
           }
           throw $e;
       }
    }

    /**
     * @param int $id
     * @throws NoEntityException
     */
    public function deleteUser(int $id) : void
    {
        $sql = 'DELETE FROM users WHERE users_id = :id';

        try {
            $this->pdo->beginTransaction();
            $this->deleteUserToOfficesLinkByUserId($id);
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                throw new NoEntityException('No entity found for this user id : ' . $id);
            }
            $this->pdo->commit();
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
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
