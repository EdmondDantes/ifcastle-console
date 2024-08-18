<?php
declare(strict_types=1);

namespace IfCastle\Console;

use IfCastle\Application\ApplicationAbstract;
use IfCastle\Application\EngineRolesEnum;
use IfCastle\ServiceManager\DescriptorRepositoryInterface;

class ConsoleApplication            extends ApplicationAbstract
{
    #[\Override]
    protected function engineStartAfter(): void
    {
        (new SymfonyApplication(
            $this->systemEnvironment,
            $this->systemEnvironment->resolveDependency(DescriptorRepositoryInterface::class)
        ))->run();
    }
    
    #[\Override]
    protected function defineEngineRole(): EngineRolesEnum
    {
        return EngineRolesEnum::CONSOLE;
    }
}