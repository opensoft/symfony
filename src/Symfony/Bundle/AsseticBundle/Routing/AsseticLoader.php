<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Bundle\AsseticBundle\Routing;

use Assetic\Factory\LazyAssetManager;
use Assetic\Factory\Resource\FileResourceInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Loads routes for all assets.
 *
 * Assets should only be served through the routing system for ease-of-use
 * during development.
 *
 * For example, add the following to your application's routing_dev.yml:
 *
 *     _assetic:
 *         resource: .
 *         type:     assetic
 *
 * In a production environment you should use the `assetic:dump` command to
 * create static asset files.
 *
 * @author Kris Wallsmith <kris.wallsmith@symfony-project.com>
 */
class AsseticLoader extends Loader
{
    protected $am;

    public function __construct(LazyAssetManager $am)
    {
        $this->am = $am;
    }

    public function load($resource, $type = null)
    {
        $routes = new RouteCollection();

        // resources
        foreach ($this->am->getResources() as $resource) {
            if (!$resource instanceof \Traversable) {
                $resource = array($resource);
            }
            foreach ($resource as $r) {
                if ($r instanceof FileResourceInterface) {
                    $routes->addResource(new FileResource($r->getPath()));
                }
            }
        }

        // routes
        foreach ($this->am->getNames() as $name) {
            $asset = $this->am->get($name);

            $defaults = array(
                '_controller' => 'assetic.controller:render',
                'name'        => $name,
            );

            if ($extension = pathinfo($asset->getTargetUrl(), PATHINFO_EXTENSION)) {
                $defaults['_format'] = $extension;
            }

            $routes->add('assetic_'.$name, new Route($asset->getTargetUrl(), $defaults));
        }

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'assetic' == $type;
    }
}
