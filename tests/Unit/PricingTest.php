<?php

namespace Tests\Unit;

use App\Models\Gallery;
use App\Models\Photo;
use App\Support\Pricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolves_markup_from_photo_when_available(): void
    {
        $photo = Photo::factory()->create(['markup_percent' => 45]);
        $gallery = $photo->gallery;

        config(['settings.sales.markup_percent' => 12]);

        $this->assertSame(45.0, Pricing::resolveMarkupPercent($photo, $gallery));
    }

    public function test_resolves_markup_from_gallery_when_photo_is_null(): void
    {
        $gallery = Gallery::factory()->create(['markup_percent' => 30]);

        config(['settings.sales.markup_percent' => 12]);

        $this->assertSame(30.0, Pricing::resolveMarkupPercent(null, $gallery));
    }

    public function test_falls_back_to_settings_default_or_constant(): void
    {
        config(['settings.sales.markup_percent' => 18]);
        $this->assertSame(18.0, Pricing::resolveMarkupPercent());

        config(['settings.sales.markup_percent' => null]);
        $this->assertSame(25.0, Pricing::resolveMarkupPercent());
    }

    public function test_apply_markup_rounds_to_two_decimals(): void
    {
        $this->assertSame(12.5, Pricing::applyMarkup(10.0, 25));
        $this->assertSame(10.07, Pricing::applyMarkup(8.39, 20));
    }
}
