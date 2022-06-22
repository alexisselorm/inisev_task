<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WebsiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'=>$this->faker->unique()->firstName(),
            'url'=>$this->faker->url(),
            'code'=>'WEB'.$this->faker->numberBetween(100,1000),
        ];
    }
}
