<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 06.03.2016
 */

namespace Users\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Url\Generators\UrlGenerator;
use Users\Authorization\Privilege;
use Users\Authorization\Role;
use Log\EventLog;
use Log\LogType;
use Users\User;

class UsersFixture extends AbstractFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadDefaultUserRoles($manager);
        $this->loadDefaultUsers($manager);
        $this->loadDefaultLoggingEvents($manager);
        $this->loadDefaultUrls($manager);
        $this->loadDefaultPrivileges($manager);

        $manager->flush();
    }


    private function loadDefaultUsers(ObjectManager $manager)
    {
        $admin = new User('admin', 'admin@cms.cz', 'admin');
        $admin->addRole($this->getReference('role_admin'));
        $manager->persist($admin);
    }


    private function loadDefaultUserRoles(ObjectManager $manager)
    {
        $guest = new Role('guest');
        $manager->persist($guest);

        $user = new Role('user', $guest);
        $manager->persist($user);

        $admin = new Role('admin', $user);
        $manager->persist($admin);

        $this->addReference('role_guest', $guest);
        $this->addReference('role_user', $user);
        $this->addReference('role_admin', $admin);
    }


    private function loadDefaultPrivileges(ObjectManager $manager)
    {
        $create = new Privilege('create');
        $manager->persist($create);
        $this->setReference('privilege_create', $create);

        $edit = new Privilege('edit');
        $manager->persist($edit);
        $this->setReference('privilege_edit', $edit);

        $remove = new Privilege('remove');
        $manager->persist($remove);
        $this->setReference('privilege_remove', $remove);

        $view = new Privilege('view');
        $manager->persist($view);
        $this->setReference('privilege_view', $view);

        $upload = new Privilege('upload');
        $manager->persist($upload);
        $this->setReference('privilege_upload', $upload);
    }


    private function loadDefaultUrls(ObjectManager $manager)
    {
        $login = UrlGenerator::create('administration/login', 'Users:Front:Auth', 'login');
        $manager->persist($login);

        $logout = UrlGenerator::create('administration/logout', 'Users:Front:Auth', 'logout');
        $manager->persist($logout);

        $users = UrlGenerator::create('administration/users', 'Users:Admin:Users', 'default');
        $manager->persist($users);

        $detail = UrlGenerator::create('administration/user-detail', 'Users:Admin:Users', 'detail');
        $manager->persist($detail);

        $roles = UrlGenerator::create('administration/roles', 'Users:Admin:Users', 'roles');
        $manager->persist($roles);

        $roleDefinition = UrlGenerator::create('administration/role-definition', 'Users:Admin:Users', 'roleDefinition');
        $manager->persist($roleDefinition);
    }


    private function loadDefaultLoggingEvents(ObjectManager $manager)
    {
        // Log types
        $userLogType = new LogType('user');
        $manager->persist($userLogType);

        // Log events
        $userLoginEvent = new EventLog('user_login', $userLogType);
        $manager->persist($userLoginEvent);

        $userLogoutEvent = new EventLog('user_logout', $userLogType);
        $manager->persist($userLogoutEvent);
    }

}