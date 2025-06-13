<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class xxx extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:xxx';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tinkering cli';

    /**
     * Execute the console command.
     */
    public function handle()
    {

    }

    public function regUser($name, $email)
    {
        $r = Http::post(config('app.url') . '/api/v1/register', [
            'name' => $name,
            'email' => $email,
            'password' => 'verysecure',
            'password_confirmation' => 'verysecure',
        ]);

        return $r;
    }
}
