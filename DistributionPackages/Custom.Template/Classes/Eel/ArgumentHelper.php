<?php
declare(strict_types=1);

namespace Custom\Template\Eel;

use GuzzleHttp\Psr7\ServerRequest;
use Neos\Eel\ProtectedContextAwareInterface;

/**
 * Neos.Fusion.defaultContext:
 * Custom.Template.Argument: Custom\Template\Eel\ArgumentHelper
 * call in fusion: {Custom.Template.Argument.get(parameter)}
 */
final class ArgumentHelper implements ProtectedContextAwareInterface
{
    private readonly ServerRequest $request;

    public function __construct()
    {
        $this->request = ServerRequest::fromGlobals();
    }
    public function get(string $name): int
    {
        $path = $this->request?->getUri()?->getPath();
        $parameter = preg_filter('/^.*'. $name . '(\d+)$/', '$1', $path);
        return (int) $parameter ?? 1;
    }

    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}