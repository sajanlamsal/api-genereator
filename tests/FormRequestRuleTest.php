<?php

namespace JoBins\APIGenerator\Tests;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use JoBins\APIGenerator\Tests\Stubs\RuleExampleFormRequest;
use JoBins\APIGenerator\Traits\HasDocsGenerator;

/**
 * Class FormRequestRuleTest
 * @package JoBins\APIGenerator\Tests
 */
class FormRequestRuleTest extends TestCase
{
    use HasDocsGenerator;

    /** @test */
    public function it_does_not_has_request_bodies_if_form_is_not_present()
    {
        $path = config()->get("api-generator.file-path");

        File::delete($path);

        $this->setSummary("This is a example route")
            ->setId("ExampleRoute")
            ->jsond("post", route("posts.store"), [])
            ->generate($this, true);

        $this->assertFileExists($path);

        $json = getJsonFromDocs();

        $this->assertNull(Arr::get($json, "paths./api/posts.post.requestBody"));
    }

    /**
     * @dataProvider requiredRuleDataProvider
     *
     * @test
     */
    public function all_the__required_params_will_list_in_request_bodies($class, $required)
    {
        $path = config()->get("api-generator.file-path");

        File::delete($path);

        $this->setSummary("This is a example route")
            ->setId("ExampleRoute")
            ->setRulesFromFormRequest(RuleExampleFormRequest::class)
            ->jsond("post", route("posts.store"), [])
            ->generate($this, true);

        $this->assertFileExists($path);

        $schema = getRequestBodyScheme(RuleExampleFormRequest::class);

        $this->assertEquals($required, Arr::get($schema, "schema.required"));
    }

    /** @test */
    public function the_form_request_descriptions_assocites_in_body()
    {
        $path = config()->get("api-generator.file-path");

        File::delete($path);

        $this->setSummary("This is a example route")
            ->setId("ExampleRoute")
            ->setRulesFromFormRequest(RuleExampleFormRequest::class)
            ->jsond("post", route("posts.store"), [])
            ->generate($this, true);

        $schema = getRequestBodyScheme(RuleExampleFormRequest::class);

        $properties = Arr::get($schema, "schema.properties");

        foreach ($properties as $key => $property) {
            $expected = Arr::get((new RuleExampleFormRequest())->descriptions(), $key);

            $this->assertEquals($expected, $property['description']);
        }
    }

    public function requiredRuleDataProvider()
    {
        return [
            [RuleExampleFormRequest::class, ["name"]],
        ];
    }
}
