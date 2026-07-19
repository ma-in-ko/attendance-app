<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class LoginTest extends TestCase
{
    use RefreshDatabase;

   /** @test */
    public function メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        User::factory()->create([
        'name' => 'テスト太郎',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login',[
            'email' => '',
            'password' =>'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);

    }

    /** @test */
    public function パスワードが未入力の場合バリデーションメッセージが表示される()
    {
        User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);
    }

    /** @test */
    public function 登録内容と一致しない場合バリデーションメッセージが表示される()
    {
        User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'passward',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);
    }
}