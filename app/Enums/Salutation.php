<?php

namespace App\Enums;

enum Salutation: string
{

    // phpcs:disable
    // Represents the salutation for a male individual, typically used for adult men.
    case Mr = 'mr';
    // Represents the salutation for a married female individual.
    case Mrs = 'mrs';
    // Represents the salutation for an unmarried female individual.
    case Miss = 'miss';
    // Represents the salutation for an individual with a doctoral degree, gender-neutral.
    case Dr = 'dr';
    // Represents a formal salutation for a male individual, often used in official or respectful contexts.
    case Sir = 'sir';
    // Represents a formal salutation for a female individual, often used in official or respectful contexts.
    case Madam = 'madam';
    // phpcs:enable

    // This method is used to display the enum value in the user interface, retrieving the translated string for the salutation.
    public function label(): string
    {
        return match ($this) {
            self::Mr, self::Mrs, self::Miss, self::Dr, self::Sir, self::Madam => __('app.' . $this->value),
            default => $this->value,
        };
    }

}