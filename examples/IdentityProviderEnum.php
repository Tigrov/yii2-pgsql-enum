<?php
namespace tigrov\pgsql\enum\examples;

use tigrov\pgsql\enum\EnumBehavior;

class IdentityProviderEnum extends EnumBehavior
{
    const FACEBOOK = 'facebook';
    const TWITTER = 'twitter';
    const GOOGLE = 'google';
    const LINKEDIN = 'linkedin';
    const INSTAGRAM = 'instagram';

    /** @var array list of attributes that are to be automatically detected value */
    public $attributes = ['provider' => 'provider_key'];

    /**
     * Values of Identity Providers
     * @param bool $addEmpty add empty value first
     * @return array
     */
    public static function values($addEmpty = false)
    {
        $values = parent::values($addEmpty);

        // Correct some display values
        $values[static::LINKEDIN] = 'LinkedIn';

        return $values;
    }
}