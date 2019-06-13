<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

// $factory->define(App\User::class, function (Faker\Generator $faker) {
//     return [
//         'name' => $faker->name,
//         'email' => $faker->email,
//     ];
// });


$factory->define(App\Checklist::class, function (Faker\Generator $faker) {
    return [
        'object_domain'=> $faker->word,
        'object_id'=> $faker->numberBetween($min = 1, $max = 100),
        'description'=> $faker->sentence($nbWords = 6, $variableNbWords = true),
        'due'=> $faker->date($format = 'Y-m-d', $max = 'now'),
        'due_interval'=> $faker->numberBetween($min = 1, $max = 100),
        'due_unit'=> $faker->word,
        'urgency'=> $faker->numberBetween($min = 1, $max = 100),
        'template_id'=> $faker->numberBetween($min = 1, $max = 10),
    ];
});

$factory->define(App\Template::class, function (Faker\Generator $faker) {
    return [
        'name'=> $faker->word,
    ];
});

$factory->define(App\Item::class, function (Faker\Generator $faker) {
    return [
        'description'=> $faker->sentence($nbWords = 6, $variableNbWords = true),
        'due'=>  $faker->date($format = 'Y-m-d', $max = 'now'),
        'urgency'=> $faker->numberBetween($min = 1, $max = 100),
        'assignee_id'=> $faker->numberBetween($min = 1, $max = 100),
        'due_interval'=> $faker->numberBetween($min = 1, $max = 100),
        'due_unit'=> $faker->word,
        'checklist_id'=> $faker->numberBetween($min = 1, $max = 100),
    ];
});
