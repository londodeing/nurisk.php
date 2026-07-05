<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Validator;

class LoginRequestTest extends TestCase
{
    /**
     * Test validasi LoginRequest lolos jika input lengkap.
     */
    public function test_login_request_passes_with_valid_data(): void
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'no_hp' => '081234567890',
            'kata_sandi' => 'Password123'
        ], $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test validasi LoginRequest gagal jika no_hp kosong.
     */
    public function test_login_request_fails_when_no_hp_is_empty(): void
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'no_hp' => '',
            'kata_sandi' => 'Password123'
        ], $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('no_hp', $validator->errors()->messages());
    }

    /**
     * Test validasi LoginRequest gagal jika kata_sandi kosong.
     */
    public function test_login_request_fails_when_kata_sandi_is_empty(): void
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'no_hp' => '081234567890',
            'kata_sandi' => ''
        ], $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('kata_sandi', $validator->errors()->messages());
    }
}
