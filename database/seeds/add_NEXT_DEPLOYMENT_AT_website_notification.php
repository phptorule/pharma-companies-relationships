<?php

use App\Models\WebsiteNotification;
use Illuminate\Database\Seeder;

class add_NEXT_DEPLOYMENT_AT_website_notification extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WebsiteNotification::create([
            'key' => 'NEXT_DEPLOYMENT_AT',
            'title' => 'Attention. Next Deployment',
            'body' => 'Next deployment will be executed at:',
        ]);
    }
}
