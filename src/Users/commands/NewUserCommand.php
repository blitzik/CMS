<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 06.03.2016
 */

namespace Users\Commands;

use Kdyby\Doctrine\EntityRepository;
use Nette\Utils\Validators;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Component\Console\Command\Command;
use Doctrine\Common\DataFixtures\Loader;
use Kdyby\Doctrine\EntityManager;
use Symfony\Component\Console\Question\Question;
use Users\User;

class NewUserCommand extends Command
{
    /** @var EntityManager */
    private $em;

    /** @var EntityRepository */
    private $userRepository;


    public function __construct(EntityManager $entityManager)
    {
        parent::__construct();

        $this->em = $entityManager;
        $this->userRepository = $entityManager->getRepository(User::class);
    }


    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('users:new-user')
            ->setDescription('Creates new user in database');
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
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $username = $this->askUsername($helper, $input, $output);
        $password = $this->askPassword($helper, $input, $output);
        $email = $this->askEmail($helper, $input, $output);

        $output->writeln(
            sprintf(
                'Summary:
                username: %s
                password: %s
                E-mail: %s',
                $username,
                $password,
                $email
            )
        );

        // CONFIRMATION
        $question = new ConfirmationQuestion('Do you want to create this new User? ', false);

        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('New user insertion has been CANCELED!');
            return;
        }

        $user = new User($username, $email, $password);

        try{
            // should we check for unique constraint violation?
            $this->em->persist($user);
            $this->em->flush();

            $output->writeln('Your new User has been SUCCESSFULLY created!');
            return 0;

        } catch (\Exception $e) {
            $output->writeLn("That's bad. An Error occurred: <error>{$e->getMessage()}</error>");
            return 1;
        }
    }


    private function askUsername(QuestionHelper $helper, InputInterface $input, OutputInterface $output)
    {
        $question = new Question('New user username: ');
        $question->setValidator(function ($answer) {
            if (!Validators::is(trim($answer), 'unicode:1..')) {
                throw new \RuntimeException('The username must be non-empty string');
            }

            try {
                $user = $this->userRepository->findOneBy(['username' => $answer]);

            } catch (\Exception $e) {
                throw new \RuntimeException('An error occurred while searching for username. Try it again.');
            }

            if ($user !== null) {
                throw new \RuntimeException(sprintf('User with username "%s" already exists', $answer));
            }

            return $answer;
        });

        $question->setMaxAttempts(null); // unlimited number of attempts

        return $helper->ask($input, $output, $question);
    }


    private function askEmail(QuestionHelper $helper, InputInterface $input, OutputInterface $output)
    {
        $question = new Question('New user email: ');
        $question->setValidator(function ($answer) {
            if (!Validators::is($answer, 'unicode:1..')) {
                throw new \RuntimeException('The E-mail address must have valid format');
            }

            try {
                $user = $this->userRepository->findOneBy(['email' => $answer]);

            } catch (\Exception $e) {
                throw new \RuntimeException('An error occurred while searching for E-mail address. Try it again.');
            }

            if ($user !== null) {
                throw new \RuntimeException(sprintf('User with E-mail "%s" already exists', $answer));
            }

            return $answer;
        });

        $question->setMaxAttempts(null); // unlimited number of attempts

        return $helper->ask($input, $output, $question);
    }


    private function askPassword(QuestionHelper $helper, InputInterface $input, OutputInterface $output)
    {
        $question = new Question('New user password: ');
        $question->setValidator(function ($answer) {
            if (!Validators::is(trim($answer), 'unicode:1..')) {
                throw new \RuntimeException('The password must be non-empty string');
            }
            return $answer;
        });

        $question->setMaxAttempts(null); // unlimited number of attempts

        return $helper->ask($input, $output, $question);
    }


}