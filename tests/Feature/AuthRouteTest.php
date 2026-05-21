<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthRouteTest extends TestCase
{
    public function test_login_post_route_is_rate_limited(): void
    {
        $route = Route::getRoutes()->getByName('login.store');

        $this->assertContains('throttle:10,1', $route->gatherMiddleware());
    }

    public function test_register_post_route_is_rate_limited(): void
    {
        $route = Route::getRoutes()->getByName('register.store');

        $this->assertContains('throttle:5,1', $route->gatherMiddleware());
    }

    public function test_password_reset_routes_are_rate_limited(): void
    {
        $emailRoute = Route::getRoutes()->getByName('password.email');
        $updateRoute = Route::getRoutes()->getByName('password.update');

        $this->assertContains('throttle:5,1', $emailRoute->gatherMiddleware());
        $this->assertContains('throttle:5,1', $updateRoute->gatherMiddleware());
    }

    public function test_login_page_has_no_demo_credentials_or_dummy_auth_actions(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="remember"', false)
            ->assertSee('data-password-toggle="password"', false)
            ->assertSee(route('password.request'), false)
            ->assertDontSee('owner@stokpintar.test')
            ->assertDontSee('value="password"', false)
            ->assertDontSee('Masuk dengan Google');
    }

    public function test_register_page_has_no_dummy_footer_links(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee(route('legal.terms'), false)
            ->assertSee(route('legal.privacy'), false)
            ->assertDontSee('href="#"', false)
            ->assertDontSee('Help Center');
    }

    public function test_password_reset_and_legal_pages_render(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Reset Password');

        $this->get(route('password.reset', ['token' => 'sample-token', 'email' => 'owner@example.com']))
            ->assertOk()
            ->assertSee('Buat Password Baru')
            ->assertSee('owner@example.com');

        $this->get(route('legal.terms'))
            ->assertOk()
            ->assertSee('Syarat & Ketentuan');

        $this->get(route('legal.privacy'))
            ->assertOk()
            ->assertSee('Kebijakan Privasi');
    }

    public function test_public_demo_page_does_not_require_login(): void
    {
        $this->get(route('demo'))
            ->assertOk()
            ->assertSee('Mode Demo Publik')
            ->assertSee('Demo Operasional Toko')
            ->assertSee('Produk & Stok', false)
            ->assertSee('POS Kasir')
            ->assertSee('Laporan Transaksi')
            ->assertSee('data-stock-in', false)
            ->assertSee('data-add-cart', false)
            ->assertSee('data-checkout', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('demo'), false);
    }
}
