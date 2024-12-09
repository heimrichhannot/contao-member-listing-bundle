<?php

namespace HeimrichHannot\MemberListingBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use HeimrichHannot\MemberListingBundle\HeimrichHannotMemberListingBundle;
use Symfony\Component\Config\Loader\LoaderInterface;

class Plugin implements BundlePluginInterface, ConfigPluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(HeimrichHannotMemberListingBundle::class)->setLoadAfter([
                ContaoCoreBundle::class,
                'member_plus',
            ]),
        ];
    }

    /**
     * @param LoaderInterface $loader
     * @param array<mixed> $managerConfig
     * @return void
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig): void
    {
        $loader->load('@HeimrichHannotMemberListingBundle/config/services.yaml');
    }
}