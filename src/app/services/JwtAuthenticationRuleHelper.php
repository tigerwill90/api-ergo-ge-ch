<?php

namespace Ergo\Services;

use Psr\Http\Message\ServerRequestInterface;
use Tuupola\Middleware\JwtAuthentication\RuleInterface;

class JwtAuthenticationRuleHelper implements RuleInterface
{
    private $options = [
        'path' => [],
        'ignore' => []
    ];

    public function __construct($options = [])
    {
        foreach ($options['path'] as $key => $methods) {
            if (is_int($key)) {
                $path[$methods] = [];
                $this->options['path'] = $path;
            }
            $path[$key] = (array)$methods;
            $this->options['path'] = $path;
        }

        foreach ($options['ignore'] as $key => $methods) {
            if (is_int($key)) {
                $ignore[$methods] = [];
                $this->options['ignore'] = $ignore;
            }
            $ignore[$key] = (array)$methods;
            $this->options['ignore'] = $ignore;
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function __invoke(ServerRequestInterface $request): bool
    {
        $uri = $request->getUri()->getPath();
        $uri = preg_replace('#/+#', '/', $uri);

        error_log('uri => ' . $uri);

        foreach ($this->options['ignore'] as $ignore => $methods) {
            if ((bool)preg_match("@^{$ignore}$@", $uri) && (in_array($request->getMethod(), $methods, true) || empty($methods))) {
                return false;
            }
        }

        foreach ($this->options['path'] as $path => $methods) {
            $path = rtrim($path, '/');
            if ((bool)preg_match("@^{$path}(/.*)?$@", $uri) && (in_array($request->getMethod(), $methods, true) || empty($methods))) {
                return true;
            }
        }

        return false;
    }
}
