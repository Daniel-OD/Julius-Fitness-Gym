<?php

namespace App\Services\Members;

use App\Enums\MemberImportField;
use Illuminate\Support\Str;

class MemberImportColumnMapper
{
    /**
     * @var array<string, list<string>>
     */
    private const HEADER_ALIASES = [
        MemberImportField::Email->value => ['email', 'e-mail', 'mail', 'adresa email', 'adresă email'],
        MemberImportField::FirstName->value => ['prenume', 'first name', 'firstname', 'first_name', 'given name'],
        MemberImportField::LastName->value => ['nume', 'last name', 'lastname', 'last_name', 'surname', 'family name'],
        MemberImportField::Name->value => ['nume complet', 'full name', 'name', 'member name', 'membru'],
        MemberImportField::Contact->value => ['telefon', 'phone', 'contact', 'mobile', 'mobil', 'nr telefon', 'tel'],
        MemberImportField::Dob->value => ['data nasterii', 'data nașterii', 'data nastere', 'dob', 'birth', 'birthday', 'date of birth'],
        MemberImportField::Status->value => ['status', 'stare', 'activ'],
        MemberImportField::Notes->value => ['note', 'notes', 'notite', 'notițe', 'observatii', 'observații', 'comments', 'comment'],
        MemberImportField::PlanName->value => ['abonament', 'plan', 'tip abonament', 'plan name', 'membership', 'pachet'],
        MemberImportField::PlanAmount->value => ['cost', 'pret', 'preț', 'price', 'amount', 'suma', 'tarif', 'valoare', 'plata', 'plată'],
        MemberImportField::PlanDays->value => ['zile', 'durata', 'duration', 'days', 'plan days', 'durata zile'],
        MemberImportField::SubscriptionStart->value => ['data start', 'start date', 'inceput', 'început', 'de la', 'valabil de la', 'subscription start'],
        MemberImportField::SubscriptionEnd->value => ['data expirare', 'expirare', 'end date', 'expira', 'expiră', 'pana la', 'până la', 'valabil pana', 'subscription end'],
    ];

    /**
     * @param  list<string>  $headers
     * @return array<int, string> column index => field value
     */
    public function suggest(array $headers): array
    {
        $mapping = [];

        foreach ($headers as $index => $header) {
            $mapping[$index] = $this->matchHeader($header)?->value ?? MemberImportField::Ignore->value;
        }

        return $mapping;
    }

    /**
     * @param  list<string>  $headers
     * @return array<int, string>
     */
    public function autoDetectedFields(array $headers): array
    {
        $detected = [];

        foreach ($headers as $index => $header) {
            if ($this->matchHeader($header) !== null) {
                $detected[$index] = $this->suggest($headers)[$index];
            }
        }

        return $detected;
    }

    private function matchHeader(string $header): ?MemberImportField
    {
        $normalized = $this->normalizeHeader($header);

        if ($normalized === '') {
            return null;
        }

        foreach (self::HEADER_ALIASES as $field => $aliases) {
            foreach ($aliases as $alias) {
                if ($normalized === $this->normalizeHeader($alias)) {
                    return MemberImportField::from($field);
                }
            }
        }

        return null;
    }

    private function normalizeHeader(string $header): string
    {
        return Str::of($header)
            ->lower()
            ->ascii()
            ->replace(['_', '-', '.'], ' ')
            ->squish()
            ->toString();
    }
}
