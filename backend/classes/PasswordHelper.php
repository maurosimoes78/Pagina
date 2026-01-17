<?php
/**
 * Classe auxiliar para hash de senhas compatível com PHP 5.3
 * Implementa bcrypt usando crypt() que está disponível desde PHP 5.3
 */

class PasswordHelper {
    /**
     * Gera hash da senha
     * Compatível com PHP 5.3 usando crypt()
     */
    public static function hash($password) {
        // Usar crypt com salt aleatório
        $salt = self::generateSalt();
        return crypt($password, $salt);
    }

    /**
     * Verifica se a senha corresponde ao hash
     * Compatível com PHP 5.3
     */
    public static function verify($password, $hash) {
        // crypt() retorna o hash completo incluindo o salt
        // Comparar usando hash_equals se disponível (PHP 5.6+), senão usar ==
        if (function_exists('hash_equals')) {
            return hash_equals($hash, crypt($password, $hash));
        } else {
            // Para PHP 5.3-5.5, usar comparação segura manual
            $newHash = crypt($password, $hash);
            return self::safeCompare($hash, $newHash);
        }
    }

    /**
     * Comparação segura de strings (timing-safe)
     * Para PHP < 5.6
     */
    private static function safeCompare($a, $b) {
        if (strlen($a) !== strlen($b)) {
            return false;
        }
        $result = 0;
        for ($i = 0; $i < strlen($a); $i++) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $result === 0;
    }

    /**
     * Gera salt para crypt()
     */
    private static function generateSalt() {
        // Usar blowfish (2y) que está disponível desde PHP 5.3.7
        // Se não disponível, usar DES (menos seguro mas compatível)
        if (defined('CRYPT_BLOWFISH') && CRYPT_BLOWFISH == 1) {
            $salt = '$2y$10$';
            $salt .= substr(str_replace('+', '.', base64_encode(self::randomBytes(16))), 0, 22);
            return $salt;
        } else {
            // Fallback para DES (menos seguro)
            return '$1$' . substr(md5(uniqid(rand(), true)), 0, 8) . '$';
        }
    }

    /**
     * Gera bytes aleatórios (compatível com PHP 5.3)
     */
    private static function randomBytes($length) {
        if (function_exists('openssl_random_pseudo_bytes')) {
            return openssl_random_pseudo_bytes($length);
        } else {
            // Fallback menos seguro
            $bytes = '';
            for ($i = 0; $i < $length; $i++) {
                $bytes .= chr(mt_rand(0, 255));
            }
            return $bytes;
        }
    }
}

