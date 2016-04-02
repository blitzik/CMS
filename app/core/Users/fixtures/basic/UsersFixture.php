<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 06.03.2016
 */

namespace Users\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Users\Authorization\AccessDefinition;
use Users\Authorization\Permission;
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
        $userResource = new Resource('user');
        $manager->persist($userResource);

        $permUserEdit = new Permission($this->getReference('role_admin'), $userResource, $this->getReference('privilege_edit'));
        $manager->persist($permUserEdit);
        

        $roleResource = new Resource('user_role');
        $manager->persist($roleResource);

        $permRoleCreate = new Permission($this->getReference('role_admin'), $roleResource, $this->getReference('privilege_create'));
        $manager->persist($permRoleCreate);

        $permRoleEdit = new Permission($this->getReference('role_admin'), $roleResource, $this->getReference('privilege_edit'));
        $manager->persist($permRoleEdit);

        $permRoleRemove = new Permission($this->getReference('role_admin'), $roleResource, $this->getReference('privilege_remove'));
        $manager->persist($permRoleRemove);


        // access definitions

        $acUserEdit = new AccessDefinition($userResource, $this->getReference('privilege_edit'));
        $manager->persist($acUserEdit);


        $acRoleCreate = new AccessDefinition($roleResource, $this->getReference('privilege_create'));
        $manager->persist($acRoleCreate);

        $acRoleEdit = new AccessDefinition($roleResource, $this->getReference('privilege_edit'));
        $manager->persist($acRoleEdit);

        $acRoleRemove = new AccessDefinition($roleResource, $this->getReference('privilege_remove'));
        $manager->persist($acRoleRemove);
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

        $newRole = UrlGenerator::create('administration/new-role', 'Users:Admin:Users', 'newRole');
        $manager->persist($newRole);

        $roleDefinition = UrlGenerator::create('administration/role-definition', 'Users:Admin:Users', 'roleDefinition');
        $manager->persist($roleDefinition);
    }


    private function loadDefaultLoggingEvents(ObjectManager $manager)
    {
        // Log types
        $userLogType = new LogType('user');
        $manager->persist($userLogType);

        $userRoleLogType = new LogType('user_role');
        $manager->persist($userRoleLogType);

        // Log events
        $userLoginEvent = new EventLog('user_login', $userLogType);
        $manager->persist($userLoginEvent);

        $userLogoutEvent = new EventLog('user_logout', $userLogType);
        $manager->persist($userLogoutEvent);

        $userEditingEvent = new EventLog('user_editing', $userLogType);
        $manager->persist($userEditingEvent);

        $userRoleCreationEvent = new EventLog('user_role_creation', $userRoleLogType);
        $manager->persist($userRoleCreationEvent);

        $userRoleRemovalEvent = new EventLog('user_role_removal', $userRoleLogType);
        $manager->persist($userRoleRemovalEvent); // todo implement into subscriber

        $userRoleEditingEvent = new EventLog('user_role_editing', $userRoleLogType);
        $manager->persist($userRoleEditingEvent);
    }

}