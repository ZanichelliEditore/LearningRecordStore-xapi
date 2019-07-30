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

$factory->define(App\Models\Lrs::class, function (Faker\Generator $faker) {
    return [
        '_id' => $faker->bothify('?#####?#######'),
        'title' => $faker->numerify('Lrs ##'),
        'folder' => $faker->numerify('Folder ##'),
        'description' => $faker->text($maxNbChars = 20),
    ];
});
