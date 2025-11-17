<?php

namespace App;

use App\Observer\GameObserverInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(GameObserverInterface::class)
            ->addTag('game.observer');
    }


    public function process(ContainerBuilder $container)
    {
        // Return a Definition object that knows everything about how to instantiate a GameApplication,
        // like its class, constructor arguments, and any calls it might have on it
        $definition = $container->findDefinition(GameApplication::class);

        // Return an array of all the services that have the game.observer tag
        $taggedObservers = $container->findTaggedServiceIds('game.observer');

        // $id = service id. $tags = array of tags
        foreach ($taggedObservers as $id => $tags) {
            // Equivalent to call in service.yaml => we want the subscribe method to be called
            // on GameApp autowiring and pass the service with game.observer tag
            $definition->addMethodCall('subscribe', [new Reference($id)]);
        }
    }
}
