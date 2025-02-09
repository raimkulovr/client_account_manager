<?php
class Account {
    function __construct(
        public readonly int $id,
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $email,
        public readonly ?int $company_id,
        public readonly ?string $position,
        public readonly ?string $phone_number1,
        public readonly ?string $phone_number2,
        public readonly ?string $phone_number3,
    ) {}

    public static function fromArray($array) {
        if (is_array($array)) {
            return new self(
                $array['id'],
                $array['first_name'],
                $array['last_name'],
                $array['email'],
                $array['company_id'],
                $array['position'],
                $array['phone_number1'],
                $array['phone_number2'],
                $array['phone_number3'],
            );
        }
        return null;
    }
}