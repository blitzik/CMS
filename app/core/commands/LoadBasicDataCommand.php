<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 07.03.2016
 */

namespace App\Commands;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;
use Doctrine\Common\DataFixtures\Loader;
use Kdyby\Doctrine\EntityManager;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class LoadBasicDataCommand extends Command
{
    /** @var EntityManager */
    private $em;

    /** @var array */
    private $fixtures; // [Fixture::class => instance_of_fixture]


    public function __construct(EntityManager $entityManager)
    {
        parent::__construct();

        $this->em = $entityManager;
    }


    /**
     * @param string $fixtureClassName
     */
    public function addFixture($fixtureClassName)
    {
        if (!class_exists($fixtureClassName)) {
            return; // todo
        }

        $this->fixtures[$fixtureClassName] = new $fixtureClassName;
    }


    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('cms:initialize')
             ->setDescription('Initializes entire cms system and fill it with basic data');
    }


    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loader = new Loader();
        foreach ($this->fixtures as $fixture) {
            $loader->addFixture($fixture);
        }

        $purger = new ORMPurger($this->em);
        $executor = new ORMExecutor($this->em, $purger);

        $executor->setLogger(function ($message) use ($output) {
            $output->writeln(sprintf('  <comment>></comment> <info>%s</info>', $message));
        });

        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        $question = new ConfirmationQuestion('WARNING! Database will be purged before loading initialization data. Do you want to continue?', false);
        if (!$questionHelper->ask($input, $output, $question)) {
            $output->writeln('CMS initialization has been CANCELED!');
            return;
        }

        try {
            $executor->execute($loader->getFixtures());

            $output->writeln('Basic CMS data has been SUCCESSFULLY loaded!');

            return 0;
        } catch (\Exception $e) {
            $output->writeLn("That's bad. An Error occurred: <error>{$e->getMessage()}</error>");
            return 1;
        }
    }


}