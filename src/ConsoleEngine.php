<?php
declare(strict_types=1);

namespace IfCastle\Console;

use IfCastle\Application\NativeEngine;

class ConsoleEngine                 extends NativeEngine
{
    #[\Override]
    public function getEngineName(): string
    {
        return 'console/'.phpversion();
    }
}