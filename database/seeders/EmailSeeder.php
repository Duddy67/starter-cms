<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Settings\Email;

class EmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Email::create([
          'code' => 'user_registration',
          'subject' => 'Welcome {{ $data->name }}',
          'body_html' => '<p>Hello {{ $data->name }}</p>
    <p>Welcome to Starter CMS !<br />A user account has been created for you.</p>
    <p>login: {{ $data->email }}<br />Please use the password you chose during your registration.</p>
    <p>Best regard,<br />The Starter CMS team.</p>',
          'plain_text' => 0,
        ]);
    }
}
