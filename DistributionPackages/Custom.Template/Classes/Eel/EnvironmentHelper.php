<?php
declare(strict_types=1);

namespace Custom\Template\Eel;

use Neos\Eel\ProtectedContextAwareInterface;

class EnvironmentHelper implements ProtectedContextAwareInterface
{
    public function get(string $name, $default = ''): string
    {
        return $_ENV[$name] ?? getenv($name) ?: $default;
    }

    public function getBool(string $name, $default = false): bool
    {
        $value = $this->get($name);
        if ($value === null) {
            return $default;
        }
        return filter_var($value, FILTER_VALIDATE_BOOL);
    }

    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}