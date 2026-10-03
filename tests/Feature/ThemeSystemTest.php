<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'Test User',
            'email' => 'test_' . uniqid() . '@example.com',
            'mobile' => '98765' . rand(10000, 99999),
            'password' => bcrypt('secret123'),
            'role_title' => 'HR Specialist',
            'theme' => 'charcoal',
        ], $attributes));
    }

    public function test_theme_switcher_renders_on_dashboard(): void
    {
        $user = $this->makeUser();
        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('themeSwitcherBtn');
        $response->assertSee('themeDropdown');
        $response->assertSee('data-theme-target="charcoal"', false);
        $response->assertSee('data-theme-target="navy"', false);
        $response->assertSee('data-theme-target="indigo"', false);
        $response->assertSee('data-theme-target="emerald"', false);
        $response->assertSee('data-theme-target="walnut"', false);
        $response->assertSee('data-theme-target="burgundy"', false);
    }

    public function test_user_can_update_theme_preference(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->postJson('/profile/theme', [
            'theme' => 'navy',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'theme' => 'navy']);
        $this->assertEquals('navy', $user->fresh()->theme);

        // Reset back to charcoal
        $this->actingAs($user)->postJson('/profile/theme', ['theme' => 'charcoal']);
        $this->assertEquals('charcoal', $user->fresh()->theme);
    }

    public function test_theme_validation_rejects_invalid_theme(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->postJson('/profile/theme', [
            'theme' => 'neon-pink-invalid',
        ]);

        $response->assertStatus(422);
    }

    public function test_profile_page_displays_properly(): void
    {
        $user = $this->makeUser();
        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Personal Information');
        $response->assertDontSee('System Appearance & Color Combos');
    }
}
