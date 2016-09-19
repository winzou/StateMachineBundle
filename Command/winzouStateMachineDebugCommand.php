<?php

namespace winzou\Bundle\StateMachineBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\ChoiceQuestion;

class winzouStateMachineDebugCommand extends ContainerAwareCommand
{
    /**
     * @var array
     */
    protected $config;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->addArgument('key', InputArgument::REQUIRED, 'A state machine key');

        $this->setName('debug:winzou:state-machine');
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->config = $this->getContainer()->getParameter('sm.configs');

        if (empty($this->config)) {
            throw new \RuntimeException('The is no state machines configured.');
        }
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
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $input->getArgument('key');

        if (!array_key_exists($key, $this->config)) {
            throw new \RuntimeException("The provided state machine key is not configured.");
        }

        $config = $this->config[$key];

        $this->printStates($config['states'], $output);
        $this->printTransitions($config['transitions'], $output);
    }

    /**
     * @param $states
     * @param OutputInterface $output
     */
    protected function printStates($states, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(array('Configured States:'));

        foreach ($states as $state) {
            $table->addRow([$state]);
        }

        $table->render();
    }

    /**
     * @param $transitions
     * @param OutputInterface $output
     */
    protected function printTransitions($transitions, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(array('Transition', 'FROM', 'TO'));

        foreach ($transitions as $name => $transition) {
            $table->addRow(array($name, implode("\n", $transition['from']), $transition['to']));
        }

        $table->render();
    }
}
