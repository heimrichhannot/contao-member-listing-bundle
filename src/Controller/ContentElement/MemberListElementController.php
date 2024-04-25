<?php

namespace HeimrichHannot\MemberListingBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\MemberModel;
use Contao\Template;
use HeimrichHannot\MemberPlus\MemberPlusMemberModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(MemberListElementController::TYPE, category: 'member')]
class MemberListElementController extends AbstractContentElementController
{
    public const TYPE = 'huh_memberlist';

    protected function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {
        return $template->getResponse();
//
//        $this->mlSort = StringUtil::deserialize($this->mlSort);
//
//        $this->Controller = new MemberPlus($this->objModel);
//
//        $this->objMembers =  MemberPlusMemberModel::findActiveByIds($this->mlSort);
//
//        if ($this->objMembers === null) {
//            $this->Template->empty = $GLOBALS['TL_LANG']['MSC']['emptyMemberlist'];
//
//            return;
//        }
//
//        $arrMembers = [];
//
//        while ($this->objMembers->next()) {
//            $arrMembers[$this->objMembers->id] = $this->Controller->parseMember($this->objMembers->current());
//        }
//
//        $this->Template->members = $arrMembers;
    }
}