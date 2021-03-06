<?php

namespace Box\Component\Console\Test;

use Box\Component\Console\Application;
use KHerGe\File\Utility;
use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Test case for commands registered with the console.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class CommandTestCase extends TestCase
{
    /**
     * The application.
     *
     * @var Application
     */
    protected $application;

    /**
     * The container.
     *
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * The configuration directory.
     *
     * @var string
     */
    protected $configDir;

    /**
     * Returns the contents of the output stream.
     *
     * @param StreamOutput $output The output manager.
     *
     * @return string The contents of the stream.
     */
    public function readOutput(StreamOutput $output)
    {
        $contents = '';
        $stream = $output->getStream();

        rewind($stream);

        do {
            $contents .= fgets($stream);
        } while (!feof($stream));

        return $contents;
    }

    /**
     * Runs a command and returns the exit status.
     *
     * If an output manager is not provided, a new one will be created. The
     * new output stream will use a memory stream, and the instance will be
     * set as the `$output` argument by reference.
     *
     * @param InputInterface  $input   The input manager.
     * @param OutputInterface &$output The output manager.
     *
     * @return integer The exit status.
     */
    public function runCommand(
        InputInterface $input = null,
        OutputInterface &$output = null
    ) {
        if (($this->container instanceof ContainerBuilder)
            && !$this->container->isFrozen()) {
            $this->container->compile();
        }

        if (null === $output) {
            $output = new StreamOutput(
                fopen('php://memory', 'r+')
            );
        }

        return $this->application->run($input, $output);
    }

    /**
     * Creates a new container and application.
     */
    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->application = new Application($this->container);

        $this->container->setParameter(
            Application::getId('auto_exist'),
            false
        );

        $this->configDir = tempnam(sys_get_temp_dir(), 'box-');

        unlink($this->configDir);
        mkdir($this->configDir);
    }

    /**
     * Cleans up the test container and application instance.
     */
    protected function tearDown()
    {
        $this->application = null;
        $this->container = null;

        if (file_exists($this->configDir)) {
            Utility::remove($this->configDir);
        }
    }
}
