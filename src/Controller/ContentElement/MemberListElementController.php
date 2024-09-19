<?php

namespace HeimrichHannot\MemberListingBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\Pagination;
use Contao\StringUtil;
use Contao\Template;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(MemberListElementController::TYPE, category: 'member', template: 'content_element/member_list')]
class MemberListElementController extends AbstractContentElementController
{
    public const TYPE = 'huh_memberlist';

    public function __construct(
        private readonly Connection $connection,
    )
    {
    }

    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        $memberIds = array_filter(StringUtil::deserialize($model->mlSort));

        if (empty($memberIds)) {
            $template->empty = $GLOBALS['TL_LANG']['MSC']['emptyMemberlist'];
            return $template->getResponse();
        }

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('tl_member')
            ->where('id IN (:ids)')
            ->setParameter('ids', $memberIds, ArrayParameterType::INTEGER);

        $result = $queryBuilder->executeQuery();

        $total = $result->rowCount();
        $limit = $model->perPage ?? 0;

        $page = (int)$request->query->get('mlpage', 0);
        if ($page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * $limit;

        $queryBuilder->select('*');
        $queryBuilder->setFirstResult($offset);
        if ($limit > 0) {
             $queryBuilder->setMaxResults($limit);
        }

        $result = $queryBuilder->executeQuery();
        $members = [];
        while ($member = $result->fetchAssociative()) {
            $members[] = $member;
        }
        $template->members = $members;

        $template->pagination = null;
        $template->pagination_raw = null;
        if ($limit > 0) {
            $pagination = new Pagination($total, $limit, 7, 'mlpage');
            $template->pagination = $pagination->generate("\n  ");
        }

        $template->total = $total;

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