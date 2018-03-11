<?php

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;

class SeedOauthTables extends Migration
{
    /** @var Collection $clients */
    private $clients;

    /** @var DatabaseManager $db */
    protected $db;

    protected $db_flavor;

    /**
     * SeedOauthTables constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->db = app()->make(DatabaseManager::class);
        $this->db_flavor = env('DB_CONNECTION', 'mysql');

        if ( ! env('APP_CLIENT_SECRET')) {
            throw new \Exception("WARNING: Set APP_CLIENT_SECRET in .env");
        }

        $this->clients = collect([
            [
                'id'                     => 1,
                'name'                   => 'Postman',
                'secret'                 => 'p252ZUtoeyJX5kcB3HALX78or4esbDlEnqDZWW70',
                'redirect'               => 'http://localhost',
                'personal_access_client' => 0,
                'password_client'        => 1,
                'revoked'                => 0,
            ],
            [
                'id'                     => 2,
                'name'                   => 'Dev Web Frontend',
                'secret'                 => env('APP_CLIENT_SECRET'),
                'redirect'               => 'http://localhost',
                'personal_access_client' => 0,
                'password_client'        => 1,
                'revoked'                => 0,
            ],
        ]);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // oauth clients
        $this->clients->each(function ($client) {
            $this->db->table('oauth_clients')->insert($client);
        });

        switch($this->db_flavor) {
            case 'pgsql':
                $this->db->update('ALTER SEQUENCE oauth_clients_id_seq RESTART WITH ' . ($this->clients->count() + 1) . ';');
                break;

            default:
                $this->db->update('ALTER TABLE oauth_clients AUTO_INCREMENT = ' . ($this->clients->count() + 1) . ';');
                break;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->table('oauth_clients')
            ->whereIn('id', $this->clients->pluck('id'))
            ->delete();
    }
}
