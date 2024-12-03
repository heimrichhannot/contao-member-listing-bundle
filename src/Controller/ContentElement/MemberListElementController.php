<?php

namespace HeimrichHannot\MemberListingBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\CoreBundle\Routing\ResponseContext\JsonLd\JsonLdManager;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\Pagination;
use Contao\StringUtil;
use Contao\Template;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use HeimrichHannot\MemberListingBundle\Member\Member;
use Spatie\SchemaOrg\Graph;
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
    ) {
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
        $template->members = $members;

        $template->pagination = null;
        if ($limit > 0) {
            $pagination = new Pagination($total, $limit, 7, 'mlpage');
            $template->pagination = $pagination;
        }

        $template->total = $total;

        $response = $template->getResponse();

        $this->addJsonLdContext($members, $model);

        return $response;
    }

    protected function buildMemberObject(array $row, ContentModel $model): Member
    {
        $figure = null;

        if (!empty($row['addImage']) && !empty($row['singleSRC'])) {
            $figure = $this->studio->createFigureBuilder()
                ->from($row['singleSRC'])
                ->setSize($model->size)
                ->buildIfResourceExists();
        } elseif (!isset($row['addImage']) && !empty($row['singleSRC'])) {
            $figure = $this->studio->createFigureBuilder()
                ->from($row['singleSRC'])
                ->setSize($model->size)
                ->buildIfResourceExists();
        }

        return new Member($row, $figure);
    }

    protected function addJsonLdContext(array $members, ContentModel $model): void
    {
        $jsonLd = [
            '@type' => 'ItemList',
            '@context' => 'https://schema.org',
            'identifier' => '#/element/member_list/'.$model->id,
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