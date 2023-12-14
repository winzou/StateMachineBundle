<?php

namespace winzou\Bundle\StateMachineBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\ChoiceQuestion;

class winzouStateMachineDebugCommand extends Command
{
    protected static $defaultName = 'debug:winzou:state-machine';

    /** @var array */
    protected $config;

    public function __construct(array $config)
    {
        parent::__construct();

        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this
            ->setDescription('Shows information about state machine states, transitions and callbacks.')
            ->addArgument('key', InputArgument::REQUIRED, 'A state machine key')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        if (empty($this->config)) {
            throw new \RuntimeException('There is no state machine configured.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null !== $input->getArgument('key')) {
            return;
        }

        $choices = array_map(function ($name, $config) {
            return $name . "\t(" . $config['class'] . ' - ' . $config['graph'] . ')';
        }, array_keys($this->config), $this->config);

        $question = new ChoiceQuestion(
            '<question>Which state machine would you like to know about?</question>',
            $choices,
            0
        );
        $question->setErrorMessage('State Machine %s does not exists.');

        $choice = $this->getHelper('question')->ask($input, $output, $question);
        $choice = substr($choice, 0, strpos($choice, "\t"));

        $output->writeln('<info>You have just selected: '.$choice.'</info>');

        $input->setArgument('key', $choice);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = $input->getArgument('key');

        if (!array_key_exists($key, $this->config)) {
            throw new \RuntimeException("The provided state machine key is not configured.");
        }

        $config = $this->config[$key];

        $this->printStates($config['states'], $output);
        $this->printTransitions($config['transitions'], $output);

        if (isset($config['callbacks'])) {
            $this->printCallbacks($config['callbacks'], $output);
        }

        return 0;
    }

    /**
     * @param array           $states
     * @param OutputInterface $output
     */
    protected function printStates(array $states, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(array('Configured States:'));

        foreach ($states as $state) {
            $table->addRow(array($state));
        }

        $table->render();
    }

    /**
     * @param array           $transitions
     * @param OutputInterface $output
     */
    protected function printTransitions(array $transitions, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(array('Transition', 'From(s)', 'To'));

        end($transitions);
        $lastTransition = key($transitions);
        reset($transitions);

        foreach ($transitions as $name => $transition) {
            $table->addRow(array($name, implode("\n", $transition['from']), $transition['to']));

            if ($name !== $lastTransition) {
                $table->addRow(new TableSeparator());
            }
        }

        $table->render();
    }

    /**
     * @param array           $allCallbacks
     * @param OutputInterface $output
     */
    protected function printCallbacks(array $allCallbacks, OutputInterface $output)
    {
        foreach ($allCallbacks as $type => $callbacks) {
            $table = new Table($output);
            $table->setHeaders(array(ucfirst($type) . ' Callback', 'On', 'Do', 'Args'));

            end($callbacks);
            $lastCallback = key($callbacks);
            reset($callbacks);

            foreach ($callbacks as $name => $callback) {
                if (empty($callback['on'])) {
                    $callbackOn = array('*');
                } elseif(is_array($callback['on'])) {
                    $callbackOn = $callback['on'];
                } else {
                    $callbackOn = array($callback['on']);
                }
                $table->addRow(array($name, implode("\n", $callbackOn), implode("\n", $callback['do']), implode("\n", $callback['args'])));

                if ($name !== $lastCallback) {
                    $table->addRow(new TableSeparator());
                }
            }

            $table->render();
        }
    }
}
