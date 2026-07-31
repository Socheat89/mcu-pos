<?php
// core/classes/CookieCrypt.php
// ─────────────────────────────────────────────────────────────────────────────
// AES-256-GCM authenticated encryption for cookie values.
//
// Why AES-256-GCM?
//   • Confidentiality  : attacker cannot read the raw token from the cookie
//   • Integrity / Auth : GCM tag + HMAC-SHA256 prevent bit-flipping / forgery
//   • Replay protection: nonce is random per encryption — each seal is unique
// ─────────────────────────────────────────────────────────────────────────────

class CookieCrypt
{
    private const CIPHER    = 'aes-256-gcm';
    private const TAG_LEN   = 16;   // bytes — GCM auth tag
    private const NONCE_LEN = 12;   // bytes — GCM recommended nonce size

    /** @var string 32-byte binary key */
    private string $encKey;
    /** @var string any-length HMAC key */
    private string $hmacKey;

    public function __construct(string $encKey, string $hmacKey)
    {
        if (strlen($encKey) !== 32) {
            throw new \RuntimeException('CookieCrypt: encryption key must be exactly 32 bytes.');
        }
        $this->encKey  = $encKey;
        $this->hmacKey = $hmacKey;
    }

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Encrypt + HMAC-sign a plaintext string.
     * Returns a URL-safe base64 string safe to store directly in a cookie.
     */
    public function seal(string $plaintext): string
    {
        $nonce = random_bytes(self::NONCE_LEN);
        $tag   = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $this->encKey,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            '',          // additional authenticated data (none)
            self::TAG_LEN
        );

        if ($ciphertext === false) {
            throw new \RuntimeException('CookieCrypt: encryption failed.');
        }

        // Pack: nonce(12) | tag(16) | ciphertext(n)
        $blob = $nonce . $tag . $ciphertext;

        // Outer HMAC-SHA256 over the entire blob (encrypt-then-MAC)
        $mac  = hash_hmac('sha256', $blob, $this->hmacKey, true);

        // Encode: mac(32) | blob
        return rtrim(strtr(base64_encode($mac . $blob), '+/', '-_'), '=');
    }

    /**
     * Verify HMAC, then decrypt.
     * Returns the original plaintext, or null if tampered / invalid.
     */
    public function open(string $sealed): ?string
    {
        // Decode URL-safe base64
        $raw = base64_decode(strtr($sealed, '-_', '+/') . str_repeat('=', (4 - strlen($sealed) % 4) % 4), true);
        if ($raw === false || strlen($raw) < 32 + self::NONCE_LEN + self::TAG_LEN + 1) {
            return null;
        }

        // Split: mac(32) | nonce(12) | tag(16) | ciphertext
        $mac        = substr($raw, 0, 32);
        $blob       = substr($raw, 32);
        $nonce      = substr($blob, 0, self::NONCE_LEN);
        $tag        = substr($blob, self::NONCE_LEN, self::TAG_LEN);
        $ciphertext = substr($blob, self::NONCE_LEN + self::TAG_LEN);

        // Constant-time HMAC verification
        $expected = hash_hmac('sha256', $blob, $this->hmacKey, true);
        if (!hash_equals($expected, $mac)) {
            return null; // tampered
        }

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $this->encKey,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag
        );

        return ($plaintext === false) ? null : $plaintext;
    }

    // ── Factory ───────────────────────────────────────────────────────────────

    /** Create from config/app.php */
    public static function fromConfig(): self
    {
        $cfg = require dirname(__DIR__, 2) . '/config/app.php';
        return new self(
            $cfg['cookie_encryption_key'],
            $cfg['cookie_hmac_key']
        );
    }

    /** Helper to encrypt values dynamically */
    public static function encrypt(string $plaintext): string
    {
        if ($plaintext === '') return '';
        $crypt = self::fromConfig();
        return $crypt->seal($plaintext);
    }

    /** Helper to decrypt values dynamically (with fallback to plaintext if decoding fails) */
    public static function decrypt(?string $ciphertext): string
    {
        if ($ciphertext === null || $ciphertext === '') return '';
        try {
            $crypt = self::fromConfig();
            $opened = $crypt->open($ciphertext);
            return $opened !== null ? $opened : $ciphertext;
        } catch (\Exception $e) {
            return $ciphertext;
        }
    }
}
