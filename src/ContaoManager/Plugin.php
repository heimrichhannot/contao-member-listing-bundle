<?php

namespace HeimrichHannot\MemberListingBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use HeimrichHannot\MemberListingBundle\HeimrichHannotMemberListingBundle;

class Plugin implements BundlePluginInterface
{
    /**
     * @inheritDoc
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(HeimrichHannotMemberListingBundle::class)->setLoadAfter([
                ContaoCoreBundle::class,
                'member_plus',
            ]),
        ];
    }
}