<?php

use HeimrichHannot\MemberListingBundle\Controller\ContentElement\MemberListElementController;

$dca = &$GLOBALS['TL_DCA']['tl_content'];

$dca['palettes'][MemberListElementController::TYPE] = '{type_legend},type,headline;{ml_config_legend},mlSort,perPage;{template_legend:collapsed},customTpl;{protected_legend:collapsed},protected;{expert_legend:collapsed},guests,cssID;{invisible_legend:collapsed},invisible,start,stop';

$dca['fields']['mlSort'] = [
    'exclude' => true,
    'inputType' => 'picker',
    'eval' => [
        'multiple' => true,
        'isSortable' => true,
    ],
    'relation' => [
        'type' => 'hasMany',
        'load' => 'lazy',
        'table' => 'tl_member',
    ],
    'sql' => [
        'type' => 'blob',
        'notnull' => false,
    ],
];