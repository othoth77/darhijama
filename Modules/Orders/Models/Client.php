<?php

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Orders\Database\Factories\ClientFactory;

/**
 * Client final (marié·e·s) contacté exclusivement via WhatsApp. Aucun compte
 * utilisateur associé (pas d'authentification côté client, voir ARCHITECTURE.md).
 */
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    /**
     * newFactory() explicite : le mapping racine composer.json ("Modules\\":
     * "Modules/") ne suit pas la convention de résolution par défaut de Laravel
     * (Database\Factories\{namespace complet}Factory) — voir PHASE_1.md §9.
     */
    protected static function newFactory(): ClientFactory
    {
        return ClientFactory::new();
    }

    /**
     * whatsapp_phone_normalized est calculée par ClientObserver::saving(), jamais
     * assignée directement — volontairement absente de cette liste.
     */
    protected $fillable = [
        'name',
        'whatsapp_phone',
        'notes',
    ];

    /**
     * @return HasMany<Order>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Normalise un numéro WhatsApp saisi librement vers le format E.164 sans "+"
     * (chiffres uniquement + indicatif pays), cohérent avec config('whatsapp.phone_e164').
     * Utilisé par Modules\Orders\Observers\ClientObserver — voir PHASE_1.md §4.
     */
    public static function normalizePhone(string $raw): string
    {
        $digits = (string) preg_replace('/\D+/', '', $raw);

        // Préfixe international "00" (ex. 0021698123456) : on le retire.
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // Déjà au format indicatif Tunisie + 8 chiffres locaux : on garde tel quel.
        if (str_starts_with($digits, '216') && strlen($digits) === 11) {
            return $digits;
        }

        // Saisie locale avec un "0" initial superflu (ex. 098123456).
        if (strlen($digits) === 9 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        // Saisie locale à 8 chiffres : on préfixe l'indicatif Tunisie.
        if (strlen($digits) === 8) {
            return '216'.$digits;
        }

        // Autre format (indicatif étranger...) : conservé tel quel, chiffres seuls.
        return $digits;
    }
}
