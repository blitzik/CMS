<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 06.03.2016
 */

namespace Users\Fixtures;

use Log\Services\EventLogGenerator;
use Users\Authorization\AuthorizationRulesGenerator;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Users\Authorization\Privilege;
use Url\Generators\UrlGenerator;
use Users\Authorization\Resource;
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
        $this->loadDefaultPrivileges($manager);
        $this->loadDefaultAuthorizatorRules($manager);
        $this->loadDefaultLoggingEvents($manager);
        $this->loadDefaultUrls($manager);

        $manager->flush();
    }


    private function loadDefaultUsers(ObjectManager $manager)
    {
        $guest = new User('guest', 'guest@cms.cz', 'guest');
        $guest->addRole($this->getReference('role_guest'));
        $manager->persist($guest);

        $admin = new User('admin', 'admin@cms.cz', 'admin');
        $admin->addRole($this->getReference('role_admin'));
        $manager->persist($admin);
    }


    private function loadDefaultUserRoles(ObjectManager $manager)
    {
        $guest = new Role('guest');
        $manager->persist($guest);

        $admin = new Role('admin');
        $manager->persist($admin);

        $this->addReference('role_guest', $guest);
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


    private function loadDefaultAuthorizatorRules(ObjectManager $manager)
    {
        $arg = new AuthorizationRulesGenerator($manager);

        $arg->addResource(new Resource('user'))
            ->addDefinition($this->getReference('privilege_edit'), $this->getReference('role_admin'))
            ->addDefinition($this->getReference('privilege_remove'), $this->getReference('role_admin'));

        $arg->addResource(new Resource('user_role'))
            ->addDefinition($this->getReference('privilege_create'), $this->getReference('role_admin'))
            ->addDefinition($this->getReference('privilege_edit'), $this->getReference('role_admin'))
            ->addDefinition($this->getReference('privilege_remove'), $this->getReference('role_admin'));
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

        $userRemoval = UrlGenerator::create('administration/user-removal', 'Users:Admin:Users', 'userRemove');
        $manager->persist($userRemoval);

        $roles = UrlGenerator::create('administration/roles', 'Users:Admin:Users', 'roles');
        $manager->persist($roles);

        $newRole = UrlGenerator::create('administration/new-role', 'Users:Admin:Users', 'newRole');
        $manager->persist($newRole);

        $roleDefinition = UrlGenerator::create('administration/role-definition', 'Users:Admin:Users', 'roleDefinition');
        $manager->persist($roleDefinition);
        
        $roleRemoval = UrlGenerator::create('administration/role-removal', 'Users:Admin:Users', 'roleRemove');
        $manager->persist($roleRemoval);
    }


    private function loadDefaultLoggingEvents(ObjectManager $manager)
    {
        $elg = new EventLogGenerator($manager);

        $elg->addLogType(new LogType('user'))
            ->addEvent('user_login')
            ->addEvent('user_logout')
            ->addEvent('user_editing')
            ->addEvent('user_removal');

        $elg->addLogType(new LogType('user_role'))
            ->addEvent('user_role_creation')
            ->addEvent('user_role_editing')
            ->addEvent('user_role_removal');
    }

}