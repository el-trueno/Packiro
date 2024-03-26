<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Test\Controller;

use Kuniva\PackiroConfigurator\Controller\ExportController;
use Kuniva\PackiroConfigurator\Service\OrderService;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class ExportControllerTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function setUp(): void
    {
        $this->installPlugin();//just in case
    }

    public function testInitiation()
    {
        $controller = new ExportController($this->createStub(OrderService::class));
        $this->assertInstanceOf(ExportController::class, $controller);
    }

    public function testExportReturnDataStructure()
    {
        $c = $this->getContainer()->get(ExportController::class);

        $export = $c->exportAction(Context::createDefaultContext());
        $resp = json_decode($export->getContent(), flags: JSON_OBJECT_AS_ARRAY);
        $this->assertArrayHasKey('payload', $resp);
        $this->assertArrayHasKey('pagination', $resp);
    }

    private function installPlugin(): void
    {
        $application = new Application($this->getKernel());
        $installCommand = $application->find('plugin:install');

        $args = [
            '--activate' => true,
            '--reinstall' => false,
            'plugins' => ['PackiroConfigurator'],
        ];

        $installCommand->run(new ArrayInput($args, $installCommand->getDefinition()), new NullOutput());
    }
}
