<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_account_profile(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CASHIER,
        ]);

        $this->actingAs($user)
            ->get(route('account.edit'))
            ->assertOk()
            ->assertSee('Akun Saya')
            ->assertSee($user->email);
    }

    public function test_user_can_update_name_without_current_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Nama Lama',
            'email' => 'akun-lama@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_VIEWER,
        ]);

        $this->actingAs($user)->post(route('account.update'), [
            'name' => 'Nama Baru',
            'email' => 'akun-lama@example.com',
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('account.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru',
            'email' => 'akun-lama@example.com',
        ]);
    }

    public function test_user_must_confirm_current_password_to_change_email_or_password(): void
    {
        $user = User::factory()->create([
            'email' => 'akun@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CASHIER,
        ]);

        $this->actingAs($user)->from(route('account.edit'))->post(route('account.update'), [
            'name' => $user->name,
            'email' => 'akun-baru@example.com',
            'current_password' => 'salah',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertRedirect(route('account.edit'))
            ->assertSessionHasErrors('current_password');

        $user->refresh();

        $this->assertSame('akun@example.com', $user->email);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_user_can_change_email_and_password_with_current_password(): void
    {
        $user = User::factory()->create([
            'email' => 'akun@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CASHIER,
        ]);

        $this->actingAs($user)->post(route('account.update'), [
            'name' => 'Akun Baru',
            'email' => 'akun-baru@example.com',
            'current_password' => 'password',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertRedirect(route('account.edit'));

        $user->refresh();

        $this->assertSame('Akun Baru', $user->name);
        $this->assertSame('akun-baru@example.com', $user->email);
        $this->assertTrue(Hash::check('password-baru', $user->password));
    }
}
