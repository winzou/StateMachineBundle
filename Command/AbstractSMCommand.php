<?php

namespace winzou\Bundle\StateMachineBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

abstract class AbstractSMCommand extends ContainerAwareCommand
{
    /**
     * @var array
     */
    protected $config;

    protected function configure()
    {
        $this->addArgument('key', InputArgument::REQUIRED, 'A state machine key');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (null === $input->getArgument('key')) {
            /** @var QuestionHelper $questionHelper */
            $questionHelper = $this->getHelper('question');
            $whichStateMachineQuestion = new Question('Which state machine would you like to know about? ');
            $whichStateMachineQuestion->setAutocompleterValues(array_keys($this->config));
            $whichStateMachineQuestion->setValidator(function ($answer) {
                if (array_key_exists($answer, $this->config)) {
                    return $answer;
                };
                throw new \RuntimeException('Please choose one of the configured state machines.');
            });
            $answer = $questionHelper->ask($input, $output, $whichStateMachineQuestion);
            $input->setArgument('key', $answer);
        }
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->config = $this->getContainer()->getParameter('sm.configs');
    }
}
