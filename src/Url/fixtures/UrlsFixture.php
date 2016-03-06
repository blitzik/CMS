<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 06.03.2016
 */

namespace Url\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Url\Url;

class UrlsFixture extends AbstractFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        if (($handle = fopen(__DIR__ . '/data/urls.csv', "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $url = new Url();
                $url->setUrlPath($data[2]);
                $url->setDestination($data[3], $data[4]);
                if ($data[5] !== '') {
                    $url->setInternalId($data[5]);
                }
                $manager->persist($url);
            }

            fclose($handle);
            $manager->flush();
        }
    }

}