<?php

namespace HeimrichHannot\MemberListingBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotMemberListingBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}