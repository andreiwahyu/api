<?php

use Illuminate\Database\Seeder;

class AllTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Template::class, 10)->create();
        factory(App\Checklist::class, 100)->create();
        factory(App\Item::class, 1000)->create();
    }
}
