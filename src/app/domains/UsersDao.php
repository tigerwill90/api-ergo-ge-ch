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
                $officesId[] = $office['officeId'];
                $officesName[] = $office['officeName'];
            }
            return new User($data[0], $officesId, $officesName);
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
                        users_id AS id, users_email AS email, users_hashed_password AS hashedPassword, users_roles AS roles,
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
                $officesId[] = $office['officeId'];
                $officesName[] = $office['officeName'];
            }
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
        $sql = 'INSERT INTO users (users_email, users_hashed_password, users_roles) VALUES (:email, :hashedPassword, :roles)';

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare($sql);
            $email = $user->getEmail();
            $stmt->bindParam(':email', $email);
            $hashedPassword = $user->getHashedPassword();
            $stmt->bindParam(':hashedPassword', $hashedPassword);
            $roles = $user->getRoles();
            $stmt->bindParam(':roles', $roles);
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
     * @param int $id
     */
    public function deleteUserToOfficesLinkByUserId(int $id): void
    {
        $sql = 'DELETE FROM officesUsers WHERE officesUsers_users_id ' . $id;

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
                    users_roles = :roles
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
           $stmt->execute();

           $this->deleteUserToOfficesLinkByUserId($user->getId());
           $this->linkUserToOffices($user);

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
