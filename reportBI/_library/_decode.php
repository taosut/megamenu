<?php
/**
 * 字符串加密以及解密函數
 *
 * @param string $string 原文或者密文
 * @param string $operation 操作(ENCODE | DECODE), 默認為 DECODE
 * @param string $key 密鑰
 * @param int $expiry 密文有效期, 加密時候有效， 單位 秒，0 為永久有效
 * @return string 處理後的 原文或者 經過 base64_encode 處理後的密文
 *
 * @example
 *
 *     $a = authcode('abc', 'ENCODE', 'key');
 *     $b = authcode($a, 'DECODE', 'key');  // $b(abc)
 *
 *     $a = authcode('abc', 'ENCODE', 'key', 3600);
 *     $b = authcode($a, 'DECODE', 'key'); // 在一個小時內，$b(abc)，否則 $b 為空
 */
 class _decode{
    function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

        $ckey_length = 10;    // 隨機密鑰長度 取值 0-32;
                    // 加入隨機密鑰，可以令密文無任何規律，即便是原文和密鑰完全相同，加密結果也會每次不同，增大破解難度。
                    // 取值越大，密文變動規律越大，密文變化 = 16 的 $ckey_length 次方
                    // 當此值為 0 時，則不產生隨機密鑰

        $key = md5($key ? $key : UC_KEY);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256])); // ^ is the XOR operator
        }

        if($operation == 'DECODE') {
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc.str_replace('=', '', base64_encode($result)); // In MIME Base64 encoding, '=' is used for output padding at the end of the resulting string.
        }

    }
 }
?>