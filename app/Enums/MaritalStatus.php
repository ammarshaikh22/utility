<?php

namespace App\Enums;

enum MaritalStatus: string
{

    // phpcs:disable
    // Represents an individual who is not currently married or in a committed marital relationship.
    case Single = 'single';
    // Represents an individual who is legally and officially married to another person.
    case Married = 'married';
    // Represents a man whose spouse has passed away and who has not remarried.
    case Widower = 'widower';
    // Represents a woman whose spouse has passed away and who has not remarried.
    case Widow = 'widow';
    // Represents an individual who is legally married but living separately from their spouse, without formal divorce.
    case Separate = 'separate';
    // Represents an individual whose marriage has been legally dissolved through divorce proceedings.
    case Divorced = 'divorced';
    // Represents an individual who has agreed to marry another person in the future but is not yet married.
    case Engaged = 'engaged';
    // phpcs:enable

    // This method is used to display the enum value in the user interface.
    public function label(): string
    {
        return match ($this) {
            self::Single, self::Married, self::Widow, self::Widower, self::Separate, self::Divorced, self::Engaged => __('app.maritalStatus.' . $this->value),
            default => $this->value,
        };
    }

    // This method is return all the values as array.
    public static function toArray(): array
    {
        $maritalStatus = [];

        foreach (MaritalStatus::cases() as $status) {
            $maritalStatus [] = $status->value;
        }

        return $maritalStatus;
    }

}