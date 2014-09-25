<?php
namespace Phifty\Service;
use Exception;
use ConfigKit\Accessor;

class AesCryptor {

    public $key;

    public function __construct($key) {
        $this->key = $key;
    }

    /** Encryption Procedure
     *
     *  @param mixed msg message/data
     *  @param string k encryption key
     *  @param boolean base64 base64 encode result
     *
     *  @return string iv+ciphertext+mac or
     * boolean false on error
    */
    public function encrypt( $msg, $base64 = false ) {
        $k = $this->key;
        # open cipher module (do not change cipher/mode)
        if ( ! $td = mcrypt_module_open('rijndael-128', '', 'ctr', '') ) {
            return false;
        }

        $iv = mcrypt_create_iv(16, MCRYPT_RAND);        # create iv
        if ( mcrypt_generic_init($td, $k, $iv) !== 0 )  # initialize buffers
            return false;

        $msg = mcrypt_generic($td, $msg);               # encrypt
        $msg = $iv . $msg;                              # prepend iv

        $mac = $this->pbkdf2($msg, $k, 1000, 16);       # create mac
        $msg .= $mac;                                   # append mac
        mcrypt_generic_deinit($td);                     # clear buffers
        mcrypt_module_close($td);                       # close cipher module
        if ( $base64 ) {
            $msg = base64_encode($msg);      # base64 encode?
        }
        return $msg;                                    # return iv+ciphertext+mac
    }
 
    /** Decryption Procedure
     *
     *  @param string msg output from encrypt()
     *  @param string k encryption key
     *  @param boolean base64 base64 decode msg
     *
     *  @return string original message/data or
     * boolean false on error
    */
    public function decrypt( $msg, $base64 = false ) {
        $k = $this->key;
        if ( $base64 ) {
            $msg = base64_decode($msg);          # base64 decode?
        }

        # open cipher module (do not change cipher/mode)
        if ( ! $td = mcrypt_module_open('rijndael-128', '', 'ctr', '') ) {
            return false;
        }

        $iv = substr($msg, 0, 16);                          # extract iv
        $msg = substr($msg, 16);  // remove iv
        $em = substr($msg, -16);  // extract mac
        $msg = substr($msg, 0, strlen($msg) - 16);           # extract ciphertext
        $mac = $this->pbkdf2($iv . $msg, $k, 1000, 16);     # create mac
        if ( $em !== $mac ) {                                 # authenticate mac
            return false;
        }
        if ( mcrypt_generic_init($td, $k, $iv) !== 0 ) {    # initialize buffers 
            return false;
        }
        $msg = mdecrypt_generic($td, $msg);                 # decrypt
        mcrypt_generic_deinit($td);                         # clear buffers
        mcrypt_module_close($td);                           # close cipher module
        return $msg;                                        # return original msg
    }

    /** PBKDF2 Implementation (as described in RFC 2898);
     *
     *  @param string p password
     *  @param string s salt
     *  @param int c iteration count (use 1000 or higher)
     *  @param int kl derived key length
     *  @param string a hash algorithm
     *
     *  @return string derived key
    */
    public function pbkdf2( $p, $s, $c, $kl, $a = 'sha1' ) {
 
        $hl = strlen(hash($a, null, true)); # Hash length
        $kb = ceil($kl / $hl);              # Key blocks to compute
        $dk = '';                           # Derived key
        # Create key
        for ( $block = 1; $block <= $kb; $block ++ ) {
            # Initial hash for this block
            $ib = $b = hash_hmac($a, $s . pack('N', $block), $p, true);
            # Perform block iterations
            for ( $i = 1; $i < $c; $i ++ ) {
                # XOR each iterate
                $ib ^= ($b = hash_hmac($a, $b, $p, true));
            }
            $dk .= $ib; # Append iterated block
        }
        # Return derived key of correct length
        return substr($dk, 0, $kl);
    }
}

/**
 * http://www.itnewb.com/tutorial/PHP-Encryption-Decryption-Using-the-MCrypt-Library-libmcrypt
 */
class AesCryptorService implements ServiceRegister {

    public function getId() { return 'AesCryptor'; }

    public function register($kernel, $options = array() )
    {
        $options = new Accessor($options);
        $kernel->aes = function() use ($options) {
            if ( ! isset($options['Key']) ) {
                throw new Exception("Option 'Key' is required.");
            }
            return new AesCryptor( $options['Key'] );
        };
    }
}

