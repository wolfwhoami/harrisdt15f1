<?php
/**
 * XXTEA加密/解密算法
 * @param	$str: 原始字符串
 * @param	$key: 加密/解密的密钥
 * @return	string
 * @copyright	http://coolcode.org/?action=show&id=128
 */
class XXTea {

	public static function encrypt($str, $key) {

		if ($str === '') {
			return '';
		}

		$v = self::_str2long($str, true);
		$k = self::_str2long($key, false);

		if (empty($v) || empty($k)) {
			return '';
		}

		$len = count($k);

		if ($len < 4) {
			for ($i = $len; $i < 4; $i++) {
				$k[$i] = 0;
			}
		}

		$n = count($v) - 1;
		$z = $v[$n];
		$y = $v[0];
		$delta = 0x9E3779B9;
		$q = floor(6 + 52 / ($n + 1));

		$sum = 0;
		while (0 < $q--) {
			$sum = self::_int32($sum + $delta);
			$e = $sum >> 2 & 3;
			for ($p = 0; $p < $n; $p++) {
				$y = $v[$p + 1];
				$mx = self::_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::_int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
				$z = $v[$p] = self::_int32($v[$p] + $mx);
			}
			$y = $v[0];
			$mx = self::_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::_int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
			$z = $v[$n] = self::_int32($v[$n] + $mx);
		}
		return base64_encode(self::_long2str($v, false));
	}

	public static function decrypt($str, $key) {

		if ($str === '') {
			return NULL;
		}

		$str = base64_decode($str);
		$v = self::_str2long($str, false);
		$k = self::_str2long($key, false);

		if (empty($v) || empty($k)) {
			return '';
		}

		$len = count($k);

		if ($len < 4) {
			for ($i = $len; $i < 4; $i++) {
				$k[$i] = 0;
			}
		}

		$n = count($v) - 1;
		$z = $v[$n];
		$y = $v[0];
		$delta = 0x9E3779B9;
		$q = floor(6 + 52 / ($n + 1));

		$sum = self::_int32($q * $delta);
		while ($sum != 0) {
			$e = $sum >> 2 & 3;
			for ($p = $n; $p > 0; $p--) {
				$z = $v[$p - 1];
				$mx = self::_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::_int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
				$y = $v[$p] = self::_int32($v[$p] - $mx);
			}
			$z = $v[$n];
			$mx = self::_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::_int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
			$y = $v[0] = self::_int32($v[0] - $mx);
			$sum = self::_int32($sum - $delta);
		}
		return self::_long2str($v, true);
	}

	private static function _long2str($v, $w) {
		$len = count($v);
		$n = ($len - 1) << 2;
		if ($w) {
			$m = $v[$len - 1];
			if (($m < $n - 3) || ($m > $n)) {
				return false;
			}
			$n = $m;
		}
		$s = array();
		for ($i = 0; $i < $len; $i++) {
			$s[$i] = pack("V", $v[$i]);
		}

		return $w ? substr(implode('', $s), 0, $n) : implode('', $s);
	}

	private static function _str2long($s, $w) {
		$v = unpack("V*", $s. str_repeat("\0", (4 - strlen($s) % 4) & 3));
		$v = array_values($v);
		if ($w) {
			$v[count($v)] = strlen($s);
		}
		return $v;
	}

	private static function _int32($n) {
		while ($n >= 2147483648) $n -= 4294967296;
		while ($n <= -2147483649) $n += 4294967296; 
		return (int)$n;
	}
}