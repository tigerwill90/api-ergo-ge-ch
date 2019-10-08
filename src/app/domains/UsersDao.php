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
                        users_firstname AS firstname, users_lastname AS lastname, users_active AS active, users_cookieValue AS cookieValue,
                        users_created AS created, users_updated AS updated, users_reset_jwt AS resetJwt,
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
     * @param string $token
     * @param string $type
     * @return User
     * @throws NoEntityException
     */
    public function getUserByToken(string $token, string $type = 'cookie') : User
    {
        $sql = '
                    SELECT DISTINCT 
                        users_id AS id, users_email AS email, users_hashed_password AS hashedPassword, users_roles AS roles,
                        users_firstname AS firstname, users_lastname AS lastname, users_active AS active, users_cookieValue AS cookieValue,
                        users_created AS created, users_updated AS updated, users_reset_jwt AS resetJwt,
                        offices_id AS officeId, offices_name AS officeName
                        FROM users
                        LEFT JOIN officesUsers ON users_id = officesUsers_users_id
                        LEFT JOIN offices ON offices_id = officesUsers_offices_id
                        WHERE ' . ($type === 'cookie' ? 'users_cookieValue = :token' : 'users_reset_jwt = :token') . '
               ';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                throw new NoEntityException('No entity found for this user ' . $type . ' value');
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
     * @param string $cookieValue
     * @return bool
     */
    public function isCookieValueExist(string $cookieValue) : bool
    {
        $sql = 'SELECT EXISTS(SELECT * FROM users WHERE users_cookieValue = :cookieValue)';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':cookieValue', $cookieValue);
            $stmt->execute();
            return (bool) $stmt->fetchAll(PDO::FETCH_COLUMN)[0];
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param string $resetJwt
     * @return bool
     */
    public function isResetJwtExist(string $resetJwt) : bool
    {
        $sql = 'SELECT EXISTS(SELECT * FROM users WHERE users_reset_jwt = :resetJwt)';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':resetJwt', $resetJwt);
            $stmt->execute();
            return (bool) $stmt->fetchAll(PDO::FETCH_COLUMN)[0];
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
                SELECT users_id AS id, users_email AS email, users_hashed_password AS hashedPassword, users_roles AS roles,
                    users_created AS created, users_updated AS updated, users_reset_jwt AS resetJwt,
                    users_firstname AS firstname, users_lastname AS lastname, users_active AS active, users_cookieValue AS cookieValue
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
     * @param User $user
     * @throws \PDOException
     * @throws NoEntityException
     */
    private function setUserDateTime(User $user) : void
    {
        $sql = 'SELECT users_created AS created, users_updated AS updated FROM users WHERE users_id = ' . $user->getId();

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                throw new NoEntityException('No entity found for this user id : ' . $user->getId());
            }
            $user->setCreated($data[0]['created']);
            $user->setUpdated($data[0]['updated']);
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
                        users_id AS id, users_email AS email, users_hashed_password AS hashedPassword, users_roles AS roles, users_firstname AS firstname, users_lastname AS lastname,
                        users_active AS active, users_cookieValue AS cookieValue, users_reset_jwt AS resetJwt,
                        users_created AS created, users_updated AS updated,
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
            return new User($data[0], $officesId, $officesName);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param User $user
     * @throws \PDOException
     * @throws UniqueException
     * @throws NoEntityException
     */
    public function createUser(User $user) : void
    {
        $sql = 'INSERT INTO users (users_email, users_hashed_password, users_roles, users_firstname, users_lastname, users_active, users_cookieValue, users_reset_jwt) VALUES (:email, :hashedPassword, :roles, :firstname, :lastname, :active, :cookieValue, :resetJwt)';

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
            $cookieValue = $user->getCookieValue();
            $stmt->bindParam(':cookieValue', $cookieValue);
            $jwtReset = $user->getResetJwt();
            $stmt->bindParam(':resetJwt',$jwtReset);
            $stmt->execute();
            $user->setId((int)$this->pdo->lastInsertId());
            $this->setUserDateTime($user);

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
     * @throws NoEntityException
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
                    users_active = :active,
                    users_cookieValue = :cookieValue,
                    users_reset_jwt = :resetJwt
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
           $cookieValue = $user->getCookieValue();
           $stmt->bindParam(':cookieValue', $cookieValue);
           $resetJwt = $user->getResetJwt();
           $stmt->bindParam(':resetJwt', $resetJwt);
           $stmt->execute();
           $this->deleteUserToOfficesLinkByUserId($user->getId());
           $this->setUserDateTime($user);

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
               throw new UniqueException('This cookie or token value already exist', $e->getCode());
           }
           throw $e;
       }
    }

    /**
     * @param int $id
     * @param string $cookieValue
     * @throws UniqueException
     */
    public function updateCookieValue(int $id, string $cookieValue) : void
    {
        $sql = 'UPDATE users SET users_cookieValue = :cookieValue WHERE users_id = :id';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':cookieValue', $cookieValue);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (\PDOException $e) {
            if ((int) $e->getCode() === self::INTEGRITY_CONSTRAINT_VIOLATION) {
                throw new UniqueException('This cookie value already exist', $e->getCode());
            }
            throw $e;
        }
    }

    /**
     * @param int $id
     * @param string $resetJwt
     * @throws UniqueException
     */
    public function updateResetJwt(int $id, string $resetJwt) : void
    {
        $sql = 'UPDATE users SET users_reset_jwt = :resetJwt WHERE users_id = :id';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':resetJwt', $resetJwt);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (\PDOException $e) {
            if ((int) $e->getCode() === self::INTEGRITY_CONSTRAINT_VIOLATION) {
                throw new UniqueException('This reset jwt already exist', $e->getCode());
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
