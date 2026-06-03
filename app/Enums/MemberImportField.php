<?php

namespace App\Enums;

enum MemberImportField: string
{
    case Ignore = 'ignore';
    case FirstName = 'first_name';
    case LastName = 'last_name';
    case Name = 'name';
    case Email = 'email';
    case Contact = 'contact';
    case Dob = 'dob';
    case Status = 'status';
    case Notes = 'notes';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $field): array => [$field->value => $field->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::Ignore => __('app.settings.import.fields.ignore'),
            self::FirstName => __('app.settings.import.fields.first_name'),
            self::LastName => __('app.settings.import.fields.last_name'),
            self::Name => __('app.settings.import.fields.name'),
            self::Email => __('app.settings.import.fields.email'),
            self::Contact => __('app.settings.import.fields.contact'),
            self::Dob => __('app.settings.import.fields.dob'),
            self::Status => __('app.settings.import.fields.status'),
            self::Notes => __('app.settings.import.fields.notes'),
        };
    }

    public function isRequiredChoice(): bool
    {
        return in_array($this, [self::Email, self::Name, self::FirstName, self::LastName], true);
    }
}
