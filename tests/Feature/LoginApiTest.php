<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginApiTest extends TestCase
{

    use RefreshDatabase;
    
    public function setup()
    {
        parent::setup();

        // テストユーザー作成
        $this->user = factory(User::class)->create();
    }
    /**
     * A basic test example.
     *
     * @test
     */
    public function should_登録済みのユーザーを認証して返却する()
    {
        $response = $this->json('POST',route('login'), [
            'email' => $this->user->email,
            'password' => 'secret'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(['name'=> $this->user->name]);

        $this->assertAuthenticatedAs($this->user);
    }
}
