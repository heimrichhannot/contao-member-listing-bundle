<?php

use HeimrichHannot\MemberListingBundle\Controller\ContentElement\MemberListElementController;

$dca = &$GLOBALS['TL_DCA']['tl_content'];

$dca['palettes'][MemberListElementController::TYPE] = '{type_legend},type,headline;{ml_config_legend},mlSort,perPage;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop';

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