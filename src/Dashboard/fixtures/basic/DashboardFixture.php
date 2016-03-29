<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 07.03.2016
 */

namespace Dashboard\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Users\Authorization\AccessDefinition;
use Users\Authorization\Permission;
use Users\Authorization\Resource;
use Url\Generators\UrlGenerator;
use Users\Fixtures\UsersFixture;

class DashboardFixture extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadDefaultUrls($manager);
        $this->loadDefaultAuthorizatorRules($manager);

        $manager->flush();
    }


    private function loadDefaultUrls(ObjectManager $manager)
    {
        // ADMINISTRATION
        $dashboardDefault = UrlGenerator::create('administration', 'Dashboard:Dashboard', 'default');
        $manager->persist($dashboardDefault);
    }


    private function loadDefaultAuthorizatorRules(ObjectManager $manager)
    {
        $logResource = new Resource('dashboard_systemLog');
        $manager->persist($logResource);
        
        $logPermissionView = new Permission(
            $this->getReference('role_user'),
            $logResource,
            $this->getReference('privilege_view')
        );
        $manager->persist($logPermissionView);


        // access definitions
        $acLog = new AccessDefinition($logResource, $this->getReference('privilege_view'));
        $manager->persist($acLog);
    }
    

    function getDependencies()
    {
        return [
            UsersFixture::class
        ];
    }
}