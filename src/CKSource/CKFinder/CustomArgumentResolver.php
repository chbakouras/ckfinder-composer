<?php

namespace CKSource\CKFinder;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;

class CustomArgumentResolver implements ArgumentResolverInterface
{
    /**
     * The app instance.
     *
     * @var CKFinder $app
     */
    protected $app;

    /**
     * Constructor.
     *
     * @param CKFinder $app
     */
    public function __construct(CKFinder $app)
    {
        $this->app = $app;
    }

    /**
     * This method is used to inject objects to controllers.
     * It depends on arguments taken by the executed controller callable.
     * Supported injected types:
     * Request             - current request object
     * CKFinder            - application object
     * EventDispatcher     - event dispatcher
     * Config              - Config object
     * Acl                 - Acl object
     * BackendManager      - BackendManager object
     * ResourceTypeFactory - ResourceTypeFactory object
     * WorkingFolder       - WorkingFolder object
     *
     * @param Request  $request request object
     * @param callable $command
     * @return array arguments used during the command callable execution
     * @throws \ReflectionException
     */
    public function getArguments(Request $request, $command)
    {
        $r = new \ReflectionMethod($command[0], $command[1]);

        $parameters = $r->getParameters();

        $arguments = [];

        foreach ($parameters as $param) {
            /* @var $param \ReflectionParameter */
            if ($reflectionClass = $param->getClass()) {
                if ($reflectionClass->isInstance($this->app)) {
                    $arguments[] = $this->app;
                } elseif ($reflectionClass->isInstance($request)) {
                    $arguments[] = $request;
                } elseif ($reflectionClass->isInstance($this->app['dispatcher'])) {
                    $arguments[] = $this->app['dispatcher'];
                } elseif ($reflectionClass->isInstance($this->app['config'])) {
                    $arguments[] = $this->app['config'];
                }

                // Don't check isInstance to avoid unnecessary instantiation
                $classShortName = $reflectionClass->getShortName();

                switch ($classShortName) {
                    case 'BackendFactory':
                        $arguments[] = $this->app['backend_factory'];
                        break;
                    case 'ResourceTypeFactory':
                        $arguments[] = $this->app['resource_type_factory'];
                        break;
                    case 'Acl':
                        $arguments[] = $this->app['acl'];
                        break;
                    case 'WorkingFolder':
                        $arguments[] = $this->app['working_folder'];
                        break;
                    case 'ThumbnailRepository':
                        $arguments[] = $this->app['thumbnail_repository'];
                        break;
                    case 'ResizedImageRepository':
                        $arguments[] = $this->app['resized_image_repository'];
                        break;
                    case 'CacheManager':
                        $arguments[] = $this->app['cache'];
                        break;
                }
            } else {
                $arguments[] = null;
            }
        }

        return $arguments;
    }
}
