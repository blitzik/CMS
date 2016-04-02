<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 01.04.2016
 */

namespace Users\Services;

use App\ValidationObjects\ValidationObject;
use blitzik\FlashMessages\FlashMessage;
use Kdyby\Doctrine\EntityManager;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Users\Authentication\UserAuthenticator;
use Users\Authorization\Role;
use Nette\Object;
use Users\User;

class UserPersister extends Object
{
    public $onSuccessUserCreation;
    public $onSuccessUserEditing;

    /** @var Cache */
    private $cache;

    /** @var EntityManager */
    private $em;


    public function __construct(
        EntityManager $entityManager,
        IStorage $storage
    ) {
        $this->em = $entityManager;
        $this->cache = new Cache($storage, UserAuthenticator::CACHE_NAMESPACE);
    }


    /**
     * @param array $values
     * @param User $user
     * @return ValidationObject
     * @throws \Exception
     */
    public function save(array $values, User $user/* = null*/)
    {
        foreach ($values as $key => $value) $values[$key] = $value !== '' ? $value : null;

        try {
            if (isset($user) and $user->getId() !== null) {
                $validationObject = $this->update($values, $user);
            } else {
                $validationObject = $this->create($values, $user);
            }

        } catch (\Exception $e) {
            $this->em->rollback();
            $this->em->close();

            // todo log

            throw $e;
        }

        if ($validationObject->isValid()) {
            $validationObject->setResult($user);
        }

        return $validationObject;
    }


    /**
     * @param array $values
     * @param User|null $user
     * @return ValidationObject
     */
    private function create(array $values, User $user = null) // todo
    {
        if ($user === null) {
            $user = new User($values['username'], $values['email'], $values['password']);
        }

        $this->em->beginTransaction();

        $user->setUsername($values['username']);
        $user->setEmail($values['email']);
        $user->setPassword($values['password']);
        $user->setFirstName($values['first_name']);
        $user->setLastName($values['last_name']);

        $validationObject = new ValidationObject();
        $role = $this->getRole($values['role'], $validationObject);
        if (!$validationObject->isValid()) {
            $this->em->rollback();
            return $validationObject;
        }
        $user->addRole($role);

        $newUser = $this->em->safePersist($user);
        if ($newUser === false) { // username or email already exists
            if ($this->usernameExists($values['username'])) {
                $validationObject->addError('users.user.form.messages.usernameExists', FlashMessage::WARNING);
            }

            if ($this->emailExists($values['email'])) {
                $validationObject->addError('users.user.form.messages.emailExists', FlashMessage::WARNING);
            }
        }

        if ($validationObject->isValid()) {
            $this->onSuccessUserCreation($user);
            $this->em->commit();
        } else {
            $this->em->rollback();
        }

        return $validationObject;
    }


    /**
     * @param array $values
     * @param User|null $user
     * @return ValidationObject
     */
    public function update(array $values, User $user)
    {
        $this->em->beginTransaction();

        $user->setFirstName($values['first_name']);
        $user->setLastName($values['last_name']);

        $validationObject = new ValidationObject();
        // todo could be optimized
        $user->clearRoles();
        $role = $this->getRole($values['role'], $validationObject);
        if (!$validationObject->isValid()) {
            $this->em->rollback();
            return $validationObject;
        }

        $user->addRole($role);

        $this->em->persist($user);
        $this->em->flush();

        if ($validationObject->isValid()) {
            $this->em->commit();
            $this->onSuccessUserEditing($user);
            $this->cache->remove($user->getCacheKey());
        } else {
            $this->em->rollback();
        }

        return $validationObject;
    }


    /**
     * @param $roleId
     * @param ValidationObject $validationObject
     * @return Role
     */
    private function getRole($roleId, ValidationObject $validationObject)
    {
        /** @var Role $role */
        $role = $this->em->find(Role::class, $roleId);
        if ($role === null) {
            //throw new RoleMissingException;
            $validationObject->addError('users.user.form.messages.missingRole', FlashMessage::WARNING);
        }

        return $role;
    }


    private function usernameExists($username)
    {
        return (bool)$this->em->createQuery(
            'SELECT COUNT(u.username) FROM ' . User::class . ' u
             WHERE u.username = :username'
        )->setParameter('username', $username)
         ->getSingleScalarResult();
    }


    private function emailExists($email)
    {
        return (bool)$this->em->createQuery(
            'SELECT COUNT(u.email) FROM ' . User::class . ' u
             WHERE u.email = :email'
        )->setParameter('email', $email)
         ->getSingleScalarResult();
    }

}