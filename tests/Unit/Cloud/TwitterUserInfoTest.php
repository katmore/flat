<?php
namespace flat\test\Unit\Cloud;
use PHPUnit\Framework\TestCase;
class TwitterUserInfoTest extends TestCase
{
    public function testKnownFieldsAreMappedAndExcludedFromExtraFields()
    {
        $user = new \flat\cloud\twit\user\info(['screen_name'=>'local-user','followers_count'=>12]);
        $this->assertSame('local-user',$user->screen_name);
        $this->assertSame(12,$user->followers_count);
        $this->assertSame([],$user->get_extra_fields());
        $this->assertSame([],$user->get_extra_fields(true));
    }
    public function testFieldsAddedAfterConstructionReturnNamesOrValues()
    {
        $user = new \flat\cloud\twit\user\info([]); $metadata = new \stdClass;
        $user->extra = $metadata; $user->empty_extra = false;
        $this->assertSame(['extra','empty_extra'],$user->get_extra_fields());
        $this->assertSame(['extra'=>$metadata,'empty_extra'=>false],$user->get_extra_fields(true));
    }
}
