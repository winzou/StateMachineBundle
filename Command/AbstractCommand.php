<?php

namespace winzou\Bundle\StateMachineBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

abstract class AbstractCommand extends ContainerAwareCommand
{
    /**
     * @var array
     */
    protected $config;

    protected function configure()
    {
        $this->addArgument('key', InputArgument::REQUIRED, 'A state machine key');
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (null === $input->getArgument('key')) {
            $helper = $this->getHelper('question');

            $question = new ChoiceQuestion(
                '<question>Which state machine would you like to know about?</question>',
                array_keys($this->config),
                0
            );
            $question->setErrorMessage('State Machine %s does not exists.');

            $choice = $helper->ask($input, $output, $question);

            $output->writeln('<info>You have just selected: '. $choice.'</info>');

            $input->setArgument('key', $choice);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->config = $this->getContainer()->getParameter('sm.configs');
    }
}