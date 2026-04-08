<?php

namespace HeimrichHannot\MemberListingBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\CoreBundle\Routing\ResponseContext\JsonLd\JsonLdManager;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Pagination;
use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use HeimrichHannot\MemberListingBundle\Member\Member;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(MemberListElementController::TYPE, category: 'member', template: 'content_element/member_list')]
class MemberListElementController extends AbstractContentElementController
{
    public const TYPE = 'huh_memberlist';

    public function __construct(
        private readonly Connection $connection,
        private readonly Studio $studio,
        private readonly ResponseContextAccessor $responseContextAccessor,
        private readonly ScopeMatcher $scopeMatcher,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        Controller::loadDataContainer('tl_member');

        $memberIds = array_filter(StringUtil::deserialize($model->mlSort, true));

        if (empty($memberIds)) {
            $template->set('total', 0);
            $template->set('members', []);
            return $template->getResponse();
        }

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('tl_member')
            ->where('id IN (:ids)')
            ->setParameter('ids', $memberIds, ArrayParameterType::INTEGER);

        $result = $queryBuilder->executeQuery();

        $total = $result->rowCount();
        $template->set('total', $total);

        if ($this->scopeMatcher->isBackendRequest($request)) {
            $template->set('members', []);
            $template->set('pagination', null);
            return $template->getResponse();
        }

        $limit = $model->perPage ?? 0;

        $page = (int) $request->query->get('mlpage');
        if ($page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * $limit;

        $queryBuilder->select('*');
        $queryBuilder->orderBy('FIELD(id, :ids)');
        $queryBuilder->setFirstResult($offset);
        if ($limit > 0) {
            $queryBuilder->setMaxResults($limit);
        }
        $result = $queryBuilder->executeQuery();

        $members = [];
        while ($row = $result->fetchAssociative()) {
            $members[] = $this->buildMemberObject($row, $model);
        }
        $template->set('members', $members);

        $template->set('pagination', null);
        if ($limit > 0) {
            $pagination = new Pagination((int)$total, $limit, 7, 'mlpage');
            $template->set('pagination', $pagination);
        }

        $response = $template->getResponse();

        $this->addJsonLdContext($members, $model);

        return $response;
    }

    /**
     * @param array<mixed> $row
     * @param ContentModel $model
     * @return Member
     */
    protected function buildMemberObject(array $row, ContentModel $model): Member
    {
        // If addImage is defined in the DCA, we check if it is set to true on the row. Otherwise, it defaults to true.
        $addImage = !isset($GLOBALS['TL_DCA']['tl_member']['fields']['addImage']) || ($row['addImage'] ?? false);
        $src = $row['singleSRC'] ?? null;

        if ($addImage && $src)
        {
            $figure = $this->studio->createFigureBuilder()
                ->from($src)
                ->setSize($model->size)
                ->buildIfResourceExists();
        }

        return new Member($row, $figure ?? null);
    }

    /**
     * @param array<Member> $members
     * @param ContentModel $model
     * @return void
     */
    protected function addJsonLdContext(array $members, ContentModel $model): void
    {
        $jsonLd = [
            '@type' => 'ItemList',
            '@context' => 'https://schema.org',
            'identifier' => '#/element/member_list/' . $model->id,
        ];

        if ($model->name) {
            $jsonLd['name'] = $model->name;
        }

        $jsonLd['itemListElement'] = array_map(fn(Member $member, int $index) => $member->getSchemaOrgData(), $members, array_keys($members));

        $responseContext = $this->responseContextAccessor->getResponseContext();

        if (!$responseContext || !$responseContext->has(JsonLdManager::class)) {
            return;
        }

        /** @var JsonLdManager $jsonLdManager */
        $jsonLdManager = $responseContext->get(JsonLdManager::class);
        $type = $jsonLdManager->createSchemaOrgTypeFromArray($jsonLd);

        $jsonLdManager
            ->getGraphForSchema(JsonLdManager::SCHEMA_ORG)
            ->set($type, $jsonLd['identifier'])
        ;
    }
}