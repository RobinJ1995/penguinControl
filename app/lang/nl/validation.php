<?php

return array (
    /*
      |--------------------------------------------------------------------------
      | Validation Language Lines
      |--------------------------------------------------------------------------
      |
      | The following language lines contain the default error messages used by
      | the validator class. Some of these rules have multiple versions such
      | as the size rules. Feel free to tweak each of these messages here.
      |
     */

    "accepted" => "':attribute' moet geaccepteerd worden.",
    "active_url" => "':attribute' is geen geldige URL.",
    "after" => "':attribute' moet een datum na :date zijn.",
    "alpha" => "':attribute' mag enkel letters bevatten.",
    "alpha_dash" => "':attribute' mag enkel letters, cijfers en middenstreepjes (-) bevatten.",
    "alpha_num" => "':attribute' mag enkel letters en cijfers bevatten.",
    "array" => "':attribute' moet een array zijn.",
    "before" => "':attribute' moet een datum voor :date zijn.",
    "between" => array (
	"numeric" => "':attribute' moet tussen :min en :max liggen.",
	"file" => "':attribute' moet tussen :min en :max kB groot zijn.",
	"string" => "':attribute' moet tussen :min en :max tekens lang zijn.",
	"array" => "':attribute' moet tussen :min en :max items bevatten.",
    ),
    "confirmed" => "De bevestiging voor ':attribute' komt niet overeen.",
    "date" => "':attribute' is geen geldige datum.",
    "date_format" => "':attribute' komt niet overeen met het formaat :format.",
    "different" => "':attribute' en ':other' moeten verschillend zijn.",
    "digits" => "':attribute' moet :digits cijfers lang zijn.",
    "digits_between" => "':attribute' moet tussen :min en :max cijfers lang zijn.",
    "email" => "':attribute' moet een geldig e-mailadres zijn.",
    "exists" => "':attribute' is ongeldig.",
    "image" => "':attribute' moet een afbeelding zijn.",
    "in" => "':attribute' is ongeldig.",
    "integer" => "':attribute' moet een getal zijn.",
    "ip" => "':attribute' moet een geldig IP-adres zijn.",
    "max" => array (
	"numeric" => "':attribute' mag niet hoger zijn dan :max.",
	"file" => "':attribute' mag niet groter zijn dan :max kB.",
	"string" => "':attribute' mag niet langer zijn dan :max tekens.",
	"array" => "':attribute' mag niet meer dan :max items bevatten.",
    ),
    "mimes" => "':attribute' moet een bestand van zijn in 1 van de volgende soorten: :values.",
    "min" => array (
	"numeric" => "':attribute' mag niet lager zijn dan :min.",
	"file" => "':attribute' mag niet kleiner zijn dan :min kB.",
	"string" => "':attribute' moet langer zijn dan :min tekens.",
	"array" => "':attribute' moet minstens :min items bevatten.",
    ),
    "not_in" => "':attribute' moet een andere waarde hebben.",
    "numeric" => "':attribute' moet een getal zijn.",
    "regex" => "':attribute' heeft een ongeldig formaat.",
    "required" => "':attribute' is een verplicht veld.",
    "required_if" => "':attribute' is een verplicht veld wanneer ':other' gelijk is aan :value.",
    "required_with" => "':attribute' is een verplicht veld wanneer :values aanwezig is.",
    "required_with_all" => "':attribute' is een verplicht veld wanneer :value aanwezig is.",
    "required_without" => "':attribute' is een verplicht veld wanneer :values niet aanwezig is.",
    "required_without_all" => "':attribute' is een verplicht veld wanneer geen van :values aanwezig zijn.",
    "same" => "':attribute' en ':other' moeten overeenkomen.",
    "size" => array (
	"numeric" => "':attribute' moet :size zijn.",
	"file" => "':attribute' moet :size kB groot zijn.",
	"string" => "':attribute' moet :size tekens lang zijn.",
	"array" => "':attribute' moet :size items bevatten.",
    ),
    "unique" => "':attribute' is al in gebruik.",
    "url" => "':attribute' is in een ongeldig formaat.",
    /*
      |--------------------------------------------------------------------------
      | Custom Validation Language Lines
      |--------------------------------------------------------------------------
      |
      | Here you may specify custom validation messages for attributes using the
      | convention "attribute.rule" to name the lines. This makes it quick to
      | specify a specific custom language line for a given attribute rule.
      |
     */
     //TODO// Fatsoeinlijke melding voor vHost subdomain validation //
    'custom' => array (
	'attribute-name' => array (
	    'rule-name' => 'custom-message',
	),
    ),
    /*
      |--------------------------------------------------------------------------
      | Custom Validation Attributes
      |--------------------------------------------------------------------------
      |
      | The following language lines are used to swap attribute place-holders
      | with something more reader friendly such as E-Mail Address instead
      | of "email". This simply helps us make messages a little cleaner.
      |
     */
    'attributes' => array (),
);
