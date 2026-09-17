<?php namespace Serendipity\Villas\Classes;

use Illuminate\Contracts\Session\Session;

/** A short-lived layout download grant issued only after saving an inquiry. */
class LayoutDownloadAccess
{
    public function __construct(protected Session $session, protected string $key)
    {
    }

    public static function current(): self
    {
        return new self(request()->session(), app('encrypter')->getKey());
    }

    public function grant(int $villaId, int $minutes = 30): array
    {
        $grant = [
            'expires' => time() + max(1, min($minutes, 60)) * 60,
            'token' => bin2hex(random_bytes(32)),
        ];
        $this->session->put('serendipity.layout_downloads.'.$villaId, $grant);

        return [
            'expires' => $grant['expires'],
            'signature' => $this->sign($villaId, $grant),
        ];
    }

    public function allows(int $villaId, string $signature, string $expires): bool
    {
        $grant = $this->session->get('serendipity.layout_downloads.'.$villaId);
        if (!is_array($grant) || !isset($grant['token'], $grant['expires']) ||
            !ctype_digit($expires) || (string) $grant['expires'] !== $expires ||
            time() >= $grant['expires']) {
            return false;
        }

        return hash_equals($this->sign($villaId, $grant), $signature);
    }

    protected function sign(int $villaId, array $grant): string
    {
        return hash_hmac('sha256', 'villa-layouts|'.$villaId.'|'.$grant['expires'].'|'.$grant['token'], $this->key);
    }
}
