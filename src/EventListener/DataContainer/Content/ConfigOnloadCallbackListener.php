<?php

namespace HeimrichHannot\MemberListingBundle\EventListener\DataContainer\Content;

use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use HeimrichHannot\MemberListingBundle\Controller\ContentElement\MemberListElementController;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCallback(table: 'tl_content', target: 'config.onload')]
class ConfigOnloadCallbackListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function __invoke(DataContainer|null $dc = null): void
    {
        if (null === $dc || !$dc->id || 'edit' !== $this->requestStack->getCurrentRequest()?->query->get('act')) {
            return;
        }

        $element = ContentModel::findById($dc->id);

        if (null === $element || MemberListElementController::TYPE !== $element->type) {
            return;
        }

        Controller::loadDataContainer('tl_member');

        if (array_key_exists('singleSRC', $GLOBALS['TL_DCA']['tl_member']['fields'])) {
            PaletteManipulator::create()
                ->addLegend('image_legend', 'ml_config_legend', PaletteManipulator::POSITION_AFTER)
                ->addField('size', 'image_legend', PaletteManipulator::POSITION_APPEND)
                ->applyToPalette(MemberListElementController::TYPE, 'tl_content');
        }
    }
}