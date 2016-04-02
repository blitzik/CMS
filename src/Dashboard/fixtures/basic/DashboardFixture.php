<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 07.03.2016
 */

namespace Dashboard\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Users\Authorization\AuthorizationRulesGenerator;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
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

        
        $arg = new AuthorizationRulesGenerator($manager);
        $arg->addRuleDefinition($logResource, $this->getReference('privilege_view'), $this->getReference('role_admin'));
    }
    

    function getDependencies()
    {
        return [
            UsersFixture::class
        ];
    }
}