<?php
declare(strict_types=1);

namespace IfCastle\Console;

use IfCastle\DI\ContainerInterface;
use IfCastle\ServiceManager\DescriptorRepositoryInterface;
use IfCastle\ServiceManager\DescriptorWalker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class CommandLoader      implements CommandLoaderInterface
{
    protected ?array $commands      = null;
    
    public function __construct(
        protected ContainerInterface $container,
        protected DescriptorRepositoryInterface $descriptorRepository
    ) {}
    
    
    #[\Override]
    public function get(string $name): Command
    {
        $commandClass               = $this->foundCommand($name);
        
        if($commandClass === null) {
            throw new CommandNotFoundException($name);
        }
        
        return $this->instantiateCommand($commandClass);
    }
    
    #[\Override]
    public function has(string $name): bool
    {
        return $this->foundCommand($name) !== null;
    }
    
    #[\Override]
    public function getNames(): array
    {
        if($this->commands === null) {
            $this->buildCommands();
        }
        
        return array_keys($this->commands);
    }
    
    protected function foundCommand(string $name): ?ServiceCommand
    {
        if($this->commands === null) {
            $this->buildCommands();
        }
        
        return $this->commands[$name] ?? null;
    }
    
    protected function instantiateCommand(ServiceCommand $command): Command
    {
        $command->injectDependenciesFromLocator($this->locator)->initializeAfterInject();
        
        return $command;
    }
    
    protected function buildCommands(): void
    {
        $this->commands             = [];
        
        foreach (DescriptorWalker::walk($this->descriptorRepository) as $serviceName => $methodDescriptor) {
            
            $console                = $methodDescriptor->findAttribute(Console::class);
            
            if($console === null) {
                continue;
            }
            
            $commandName            = CommandBuildHelper::getCommandName($console, $methodDescriptor, $serviceName);
            
            $this->commands[$commandName] = new ServiceCommand(
                $commandName,
                $serviceName,
                $methodDescriptor->getMethod(),
                CommandBuildHelper::buildArgumentsAndOptions($methodDescriptor),
                $console->aliases,
                $console->help,
                $console->description,
                $console->hidden
            );
        }
    }
}