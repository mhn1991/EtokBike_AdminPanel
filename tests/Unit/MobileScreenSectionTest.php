<?php

namespace Tests\Unit;

use App\Models\MobileScreenSection;
use PHPUnit\Framework\TestCase;

class MobileScreenSectionTest extends TestCase
{
    public function test_it_can_be_converted_to_an_array_with_json_accessors(): void
    {
        $section = new MobileScreenSection([
            'section_id' => 'home-hero',
            'type' => 'hero',
            'data' => ['title' => 'Home'],
            'layout' => ['columns' => 2],
            'style' => ['tone' => 'primary'],
        ]);

        $array = $section->attributesToArray();

        $this->assertSame(['title' => 'Home'], $array['data']);
        $this->assertSame("{\n    \"title\": \"Home\"\n}", $section->data_json);
    }
}
