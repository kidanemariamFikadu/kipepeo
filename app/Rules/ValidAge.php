<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidAge implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        
    }

    public function passes($attribute, $value)
    {
        // Calculate the age based on the provided date of birth
        $dob = \Carbon\Carbon::parse($value);
        $age = $dob->age;

        // Check if the age is 18 or older
        return $age >= 5;
    }

    public function message()
    {
        return 'You must be at least 18 years old to register.';
    }
}
