<?php
declare(strict_types=1);

namespace LowCal\Module;

use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;
use LowCal\Helper\Strings;

/**
 * Class Security
 * The main Security module handles all cryptographic processes in LowCal; providing methods for password hashing, etc...
 * @package LowCal\Module
 */
class Security extends Module
{
	/**
	 * Flag for two-way encryption.
	 * @var int
	 */
	const TWO_WAY = 3;

	/**
	 * Flag for one-way encryption.
	 * @var int
	 */
	const ONE_WAY = 4;

	/**
	 * Flag for strict hashing/encryption.
	 * @var int
	 */
	const STRICT = 5;

	/**
	 * Custom rules flag array key for override hash.
	 * @var int
	 */
	const HASH = 6;

	/**
	 * Custom rules flag array key for override salt.
	 * @var int
	 */
	const SALT = 7;

	/**
	 * Custom rules flag array key for override poison constraints.
	 * @var int
	 */
	const POISON_CONSTRAINTS = 8;

	/**
	 * Custom rules flag array key for override unique salt.
	 * @var int
	 */
	const UNIQUE_SALT = 9;

	/**
	 * Flag to indicate encrypted string is poisoned.
	 * @var int
	 */
	const DE_POISON = 10;

	/**
	 * Depth for rehashing.
	 * @var int
	 */
	protected $_salt_depth = 1024;

	/**
	 * Default hash method.
	 * @var string
	 */
	protected $_hash = 'md5';

	/**
	 * Default salt.
	 * @var string
	 */
	protected $_salt = 'B^M#@^|>2x =<7r)t%M%y@X]8mK3b+9:e86.*6;|diL#&^|o$Ovu#K*Y>q!a<.r]_d#';

	/**
	 * Default poison constraints.
	 * @var array
	 */
	protected $_poison_constraints = array(
		array(1,2),
		array(10,3),
		array(15,2),
		array(25,2),
		array(35,3),
		array(50,1),
		array(70,1),
		array(90,2),
		array(150,1),
		array(300,2),
		array(600,1),
		array(1000,2),
		array(10000,3),
		array(100000,1),
		array(1000000,3)
	);

	/**
	 * Default caesar hashing table "from".
	 * @var array
	 */
	protected $_hash_table_from = array(
		0 => 'q',1 => 'e',2 => 'u',3 => 't',4 => 'd',5 => 'w',6 => 'n',7 => 'v',8 => 'r',9 => 'h',10 => 'o',11 => 'm',12 => 'j',13 => 'l',14 => 'i',15 => 's',16 => 'y',17 => 'b',18 => 'z',19 => 'x',20 => 'f',21 => 'p',22 => 'k',23 => 'c',24 => 'a',25 => 'g',26 => 'Q',27 => 'C',28 => 'Z',29 => 'H',30 => 'P',31 => 'B',32 => 'X',33 => 'N',34 => 'W',35 => 'V',36 => 'E',37 => 'O',38 => 'J',39 => 'Y',40 => 'A',41 => 'R',42 => 'I',43 => 'S',44 => 'K',45 => 'F',46 => 'T',47 => 'U',48 => 'D',49 => 'L',50 => 'G',51 => 'M',52 => '2',53 => '6',54 => '5',55 => '0',56 => '9',57 => '1',58 => '8',59 => '3',60 => '7',61 => '4',62 => '`',63 => '!',64 => '@',65 => '#',66 => '$',67 => '%',68 => '^',69 => '&',70 => '*',71 => '(',72 => ')',73 => '-',74 => '_',75 => '=',76 => '+',77 => '[',78 => '{',79 => ']',80 => '}',81 => ';',82 => ':',83 => '\'',84 => '"',85 => '<',86 => '>',87 => ',',88 => '.',89 => '/',90 => '?',91 => '~',92 => '|',93 => '\\',94 => 'À',95 => 'à',96 => 'Á',97 => 'á',98 => 'Â',99 => 'â',100 => 'Ã',101 => 'ã',102 => 'Ä',103 => 'ä',104 => 'Å',105 => 'å',106 => 'Æ',107 => 'æ',108 => 'Ç',109 => 'ç',110 => 'È',111 => 'è',112 => 'É',113 => 'é',114 => 'Ê',115 => 'ê',116 => 'Ë',117 => 'ë',118 => 'Ì',119 => 'ì',120 => 'Í',121 => 'í',122 => 'Î',123 => 'î',124 => 'Ï',125 => 'ï',126 => 'µ',127 => 'Ñ',128 => 'ñ',129 => 'Ò',130 => 'ò',131 => 'Ó',132 => 'ó',133 => 'Ô',134 => 'ô',135 => 'Õ',136 => 'õ',137 => 'Ö',138 => 'ö',139 => 'Ø',140 => 'ø',141 => 'ß',142 => 'Ù',143 => 'ù',144 => 'Ú',145 => 'ú',146 => 'Û',147 => 'û',148 => 'Ü',149 => 'ü',150 => 'ÿ'
	);

	/**
	 * Default caesar hashing table "to".
	 * @var array
	 */
	protected $_hash_table_to = array(
		0 => 'ü',1 => 'Õ',2 => '/',3 => 'Æ',4 => '$',5 => 'ø',6 => 'C',7 => 'x',8 => 'E',9 => '1',10 => 'm',11 => '0',12 => '=',13 => 'Z',14 => 'X',15 => 'Ó',16 => 'W',17 => '3',18 => 'g',19 => 'd',20 => '!',21 => 'R',22 => ')',23 => '?',24 => '{',25 => 'Ì',26 => 'ê',27 => 'e',28 => 'Í',29 => 'D',30 => 'w',31 => '|',32 => 'Ù',33 => 'ã',34 => '@',35 => ';',36 => '`',37 => 'ç',38 => 'v',39 => 'à',40 => 'Ç',41 => '>',42 => '\'',43 => 'b',44 => 's',45 => 'K',46 => 'é',47 => 'ó',48 => '\\',49 => ',',50 => 'c',51 => 'æ',52 => 'Ò',53 => 'o',54 => ']',55 => '&',56 => '#',57 => 'V',58 => 'î',59 => 'â',60 => '.',61 => 'À',62 => 'y',63 => 'Î',64 => 'Â',65 => 'ï',66 => 'Ñ',67 => 'U',68 => 'h',69 => 'z',70 => 'å',71 => 'ö',72 => 'S',73 => 'n',74 => '(',75 => 'è',76 => 'ú',77 => 't',78 => 'Å',79 => 'u',80 => 'Û',81 => 'p',82 => 'ì',83 => 'Ü',84 => 'È',85 => 'µ',86 => 'Y',87 => 'f',88 => '*',89 => 'á',90 => 'Ê',91 => 'ù',92 => 'Ã',93 => '8',94 => 'F',95 => 'H',96 => 'L',97 => 'Ï',98 => 'q',99 => 'P',100 => '%',101 => '4',102 => 'É',103 => 'r',104 => 'B',105 => 'Á',106 => 'j',107 => ':',108 => '^',109 => 'i',110 => 'Ä',111 => '"',112 => '~',113 => '_',114 => 'k',115 => 'a',116 => 'Ø',117 => 'O',118 => 'û',119 => 'ä',120 => 'G',121 => '+',122 => '9',123 => 'M',124 => 'l',125 => 'I',126 => 'õ',127 => 'Q',128 => 'ÿ',129 => '2',130 => '5',131 => '[',132 => 'A',133 => 'Ö',134 => 'ò',135 => '-',136 => 'ß',137 => 'ô',138 => 'Ë',139 => 'í',140 => 'T',141 => 'J',142 => '}',143 => '<',144 => 'Ú',145 => 'ñ',146 => 'N',147 => 'ë',148 => 'Ô',149 => '6',150 => '7'
	);

	/**
	 * Clean everything (javascript tags, styling tags, comments, html tags, convert all <,> to html entities).
	 * @var int
	 */
	const CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES = 0;

	/**
	 * Clean everything (javascript tags, styling tags, comments, html tags).
	 * @var int
	 */
	const CLEAN_HTML_JS_STYLE_COMMENTS = 1;

	/**
	 * Clean almost everything (javascript tags, styling tags, comments).
	 * @var int
	 */
	const CLEAN_JS_STYLE_COMMENTS = 2;

	/**
	 * Clean some things (styling tags, comments).
	 * @var int
	 */
	const CLEAN_STYLE_COMMENTS = 3;

	/**
	 * Don't clean anything.
	 * @var int
	 */
	const CLEAN_NOTHING = false;

	/**
	 * Security constructor.
	 * @param Base $Base
	 */
	function __construct(Base $Base)
	{
		parent::__construct($Base);

		$new_hash_table = Config::get('SECURITY_HASH_TABLE');
		$new_salt = Config::get('SECURITY_SALT');
		$new_poison_constraints = Config::get('SECURITY_POISON_CONSTRAINTS');
		$new_rehash_depth = Config::get('SECURITY_REHASH_DEPTH');
		$previous_installation_checksum = Config::get('SECURITY_CHECKSUM');

		if(!empty($new_hash_table))
		{
			$this->replaceHash($new_hash_table);
		}

		if(!empty($new_salt))
		{
			$this->replaceSalt($new_salt);
		}

		if(!empty($new_poison_constraints))
		{
			$this->replacePoisonConstraints($new_poison_constraints);
		}

		if(!empty($new_rehash_depth))
		{
			$this->replaceRehashDepth($new_rehash_depth);
		}

		if(!empty($previous_installation_checksum))
		{
			$this->verifyChecksum($previous_installation_checksum);
		}
	}

	/**
	 * Two-way encrypt a string. Two-way encrypted strings can be de-crypted using LowCal's built-in encryption.
	 * @param string $value
	 * @return string
	 */
	public function twoWayEncrypt(string $value): string
	{
		return $this->encrypt($value, array(self::TWO_WAY));
	}

	/**
	 * Decrypt a two-way encrypted string.
	 * @param string $value
	 * @return string
	 */
	public function twoWayDecrypt(string $value): string
	{
		return $this->decrypt($value, array(self::DE_POISON));
	}

	/**
	 * One-way encrypt a string. These strings cannot be decrypted without access to your source code, and even then,
	 * it would be very difficult.
	 * @param string $value
	 * @return string
	 */
	public function oneWayEncrypt(string $value): string
	{
		return $this->encrypt($value, array(self::ONE_WAY));
	}

	/**
	 * Compare an unencrypted string to a one-way encrypted string.
	 * @param string $unhashed_value
	 * @param string $hashed_comparison_value
	 * @return bool
	 */
	public function oneWayHashComparison(string $unhashed_value, string $hashed_comparison_value): bool
	{
		return $this->compareHashes($unhashed_value, $hashed_comparison_value, array(self::ONE_WAY));
	}

	/**
	 * Compare an unencrypted string to a two-way encrypted string.
	 * @param string $unhashed_value
	 * @param string $hashed_comparison_value
	 * @return bool
	 */
	public function twoWayHashComparison(string $unhashed_value, string $hashed_comparison_value): bool
	{
		return $this->compareHashes($unhashed_value, $hashed_comparison_value);
	}

	/**
	 * Underlying method used to compare string to encrypted strings.
	 * @param string $input_string
	 * @param string $comparison_hash
	 * @param array $flags
	 * @param array $custom_rules
	 * @return bool
	 */
	public function compareHashes(string $input_string, string $comparison_hash, array $flags = array(self::TWO_WAY), array $custom_rules = array()): bool
	{
		//encrypt string first
		$hashed_input_string = $this->encrypt($input_string, $flags, $custom_rules);

		//depoison it
		$hashed_input_string = $this->_depoisonString($hashed_input_string, (isset($custom_rules[self::POISON_CONSTRAINTS])?$custom_rules[self::POISON_CONSTRAINTS]:$this->_poison_constraints));

		//depoison the comparison hash
		$comparison_hash = $this->_depoisonString($comparison_hash, (isset($custom_rules[self::POISON_CONSTRAINTS])?$custom_rules[self::POISON_CONSTRAINTS]:$this->_poison_constraints));

		if($hashed_input_string === $comparison_hash)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Depoison a poisoned string.
	 * @param string $input
	 * @param array $constraints
	 * @return string
	 */
	protected function _depoisonString(string $input, array $constraints): string
	{
		foreach($constraints as $coords)
		{
			if($coords[0] <= strlen($input))
			{
				$input = substr_replace($input, str_repeat('|', $coords[1]), $coords[0], $coords[1]);
			}
		}

		return str_replace('|', '', $input);
	}

	/**
	 * Decrypt a string originally encrypted with LowCal.
	 * @param string $input
	 * @param array $flags
	 * @param array $custom_rules
	 * @return string
	 */
	public function decrypt(string $input, array $flags = array(self::DE_POISON), array $custom_rules = array()): string
	{
		$de_poison = false;

		if(in_array(self::DE_POISON, $flags) === true)
		{
			$de_poison = true;
		}

		if($de_poison === true)
		{
			$input = $this->_depoisonString($input, (isset($custom_rules[self::POISON_CONSTRAINTS])?$custom_rules[self::POISON_CONSTRAINTS]:$this->_poison_constraints));
		}

		$input_length = strlen($input);

		$unhashed_characters = array();
		for($i=0;$i<$input_length;$i++)
		{
			$this_character_array_key = array_search(mb_substr($input, $i, 1, 'UTF-8'), $this->_hash_table_to);

			if($this_character_array_key !== false)
			{
				$unhashed_characters[] = $this->_hash_table_from[$this_character_array_key];
			}
		}

		$input = implode('', $unhashed_characters);

		return $input;
	}

	/**
	 * Encrypt a string.
	 * @param string $input
	 * @param array $flags
	 * @param array $custom_rules
	 * @return string
	 */
	public function encrypt(string $input, array $flags = array(self::TWO_WAY), array $custom_rules = array()): string
	{
		//string that will be returned
		$final_output = '';

		//used for poisoning
		$input_length = strlen($input);

		//main flags default states
		$one_way = false;
		$two_way = false;
		$strict = false;

		//the following statements modify the above default flag states (if necessary)
		if(in_array(self::TWO_WAY, $flags) === true)
		{
			$two_way = true;
		}
		else
		{
			$one_way = true;
		}

		if(in_array(self::STRICT, $flags) === true)
		{
			$strict = true;
		}

		//we are going to produce a oneway, super strong encryption (virtually irreversible)
		if($one_way === true)
		{
			$salt_one = hash((isset($custom_rules[self::HASH])&&$custom_rules[self::HASH]!==''?$custom_rules[self::HASH]:$this->_hash), $input.(isset($custom_rules[self::SALT])&&$custom_rules[self::SALT]!==''?$custom_rules[self::SALT]:$this->_salt).(isset($custom_rules[self::UNIQUE_SALT])&&$custom_rules[self::UNIQUE_SALT]!==''?$custom_rules[self::UNIQUE_SALT]:''));

			for($x=0; $x<$this->_salt_depth; $x++)
			{
				$salt_one = hash((isset($custom_rules[self::HASH])&&$custom_rules[self::HASH]!==''?$custom_rules[self::HASH]:$this->_hash), $salt_one);
			}

			//get list of supported hashing algorithims
			$supported_hashes = hash_algos();

			//first encrypt with salt first
			$final_output = (isset($supported_hashes['whirlpool'])?
				hash('whirlpool',$salt_one.$input):
				(isset($supported_hashes['sha512'])?
					hash('sha512',$salt_one.$input):
					(isset($supported_hashes['ripemd320'])?
						hash('ripemd320',$salt_one.$input):hash('md5', $salt_one.$input)
					)
				)
			);

			//then encrypt with salt last
			$final_output = (isset($supported_hashes['whirlpool'])?
				hash('whirlpool',$final_output.$salt_one):
				(isset($supported_hashes['sha512'])?
					hash('sha512',$final_output.$salt_one):
					(isset($supported_hashes['ripemd320'])?
						hash('ripemd320',$final_output.$salt_one):hash('md5', $final_output.$salt_one)
					)
				)
			);

			//begin poisoning
			if(!isset($custom_rules[self::POISON_CONSTRAINTS]) && !empty($this->_poison_constraints))
			{
				$final_output = $this->_poisonString($final_output, $this->_poison_constraints);
			}
			elseif(isset($custom_rules[self::POISON_CONSTRAINTS]) && !empty($custom_rules[self::POISON_CONSTRAINTS]))
			{
				$final_output = $this->_poisonString($final_output, $custom_rules[self::POISON_CONSTRAINTS]);
			}

			if($strict === true && $one_way === true)
			{
				if(isset($custom_rules[self::HASH]) && $custom_rules[self::HASH] !== '')
				{
					$final_output = substr($final_output, 0, strlen(hash($custom_rules[self::HASH], $input)));
				}
				else
				{
					$final_output = substr($final_output, 0, strlen(hash($this->_hash, $input)));
				}
			}
		}
		//we are going to produce a two-way encrypted string (which will be extremely hard to crack without source code access)
		elseif($two_way === true)
		{
			$hashed_characters = array();

			for($i=0;$i<$input_length;$i++)
			{
				$this_character_array_key = array_search(substr($input, $i, 1), $this->_hash_table_from);
				$hashed_characters[] = $this->_hash_table_to[$this_character_array_key];
			}

			$final_output = implode('', $hashed_characters);

			//begin poisoning
			if(!isset($custom_rules[self::POISON_CONSTRAINTS]) && !empty($this->_poison_constraints))
			{
				$final_output = $this->_poisonString($final_output, $this->_poison_constraints, Strings::ALPHANUMERIC);
			}
			elseif(isset($custom_rules[self::POISON_CONSTRAINTS]) && !empty($custom_rules[self::POISON_CONSTRAINTS]))
			{
				$final_output = $this->_poisonString($final_output, $custom_rules[self::POISON_CONSTRAINTS], Strings::ALPHANUMERIC);
			}
		}

		return $final_output;
	}

	/**
	 * Poison a string.
	 * @param string $input
	 * @param array $constraints
	 * @param int $type
	 * @return string
	 */
	protected function _poisonString(string $input, array $constraints, int $type = Strings::HEX): string
	{
		foreach($constraints as $coords)
		{
			if($coords[0] <= strlen($input))
			{
				$part1 = substr($input, 0, $coords[0]);
				$part2 = substr($input, $coords[0]);

				$part1 = $part1.Strings::createCode($coords[1], $type);
				$input = $part1.$part2;
			}
		}

		return $input;
	}

	/**
	 * Check to see if detected domain is part of a authorized list (prevents certain types of application hijacking).
	 * @throws \Exception
	 */
	public function domainCheck(): void
	{
		$server_name = Strings::trim($_SERVER['SERVER_NAME']);

		//domain protection prevents certain rare exploits, where attackers may play with the HEADER information
		//this also helps redirect users when they type example.com instead of www.example.com
		if(!empty($server_name))
		{
			$allowed_domains = Config::get('DOMAIN_ALLOWED_DOMAINS');

			if(is_array($allowed_domains) || strpos($allowed_domains, ',') !== false)
			{
				if(!is_array($allowed_domains))
				{
					$allowed_domains = array_map('trim', explode(',', $allowed_domains));
				}

				if(!empty($allowed_domains))
				{
					$exists = false;

					foreach($allowed_domains as $domain)
					{
						if(strrpos($server_name, $domain) === true)
						{
							$exists = true;

							break;
						}
					}

					if($exists === false)
					{
						throw new \Exception('Illegal domain detected!', Codes::SECURITY_EXCEPTION_DOMAINCHECK);
					}
				}
			}
			elseif(strrpos($server_name, $allowed_domains) === false)
			{
				throw new \Exception('Illegal domain detected!', Codes::SECURITY_EXCEPTION_DOMAINCHECK);
			}
		}
	}

	/**
	 * This method returns a checksum you can use to make sure other installations using LowCal have the
	 * same security settings.
	 * @return string
	 */
	public function getChecksum(): string
	{
		$strung_string = md5($this->_salt_depth);
		$strung_string .= md5($this->_hash);
		$strung_string .= md5($this->_salt);
		$strung_string .= md5(var_export($this->_poison_constraints,true));
		$strung_string .= md5(var_export($this->_hash_table_from,true));
		$strung_string .= md5(var_export($this->_hash_table_to,true));

		$supported_hashes = hash_algos();

		$strung_string .=  md5((isset($supported_hashes['whirlpool'])?
			md5('whirlpool'):
			(isset($supported_hashes['sha512'])?
				md5('sha512'):
				(isset($supported_hashes['ripemd320'])?
					md5('ripemd320'):md5('md5')
				)
			)
		));

		return md5($strung_string);
	}

	/**
	 * Using a supplied checksum, this method will validate if this installation shares the same checksum.
	 * @param string $checksum_string
	 * @throws \Exception
	 */
	public function verifyChecksum(string $checksum_string): void
	{
		if($this->getChecksum() !== $checksum_string)
		{
			throw new \Exception('Security checksum mismatch!', Codes::SECURITY_EXCEPTION_CHECKSUM);
		}
	}

	/**
	 * Regenerate caesar "from" hash table. This will not change the current hash table, but instead provide
	 * an array you can use in your configurations.
	 * @return array
	 */
	public function regenerateHash(): array
	{
		$hash_table_from = $this->_hash_table_from;

		shuffle($hash_table_from);

		return array_values($hash_table_from);
	}

	/**
	 * Replace existing caesar hash table with provided array.
	 * @param array $new_from_table_hash_array
	 */
	public function replaceHash(array $new_from_table_hash_array): void
	{
		$this->_hash_table_from = $new_from_table_hash_array;
	}

	/**
	 * Generate a new salt.
	 * @return string
	 */
	public function regenerateSalt(): string
	{
		return Strings::createCode(random_int(40,45), Strings::ALPHANUMERIC_PLUS);
	}

	/**
	 * Replace existing salt.
	 * @param string $new_salt
	 */
	public function replaceSalt(string $new_salt): void
	{
		$this->_salt = $new_salt;
	}

	/**
	 * Generate a new set of poison constraints. This will not replace the current poison constraints.
	 * @return array
	 */
	public function regeneratePoisonConstraints(): array
	{
		return array(
			array(random_int(0,5),random_int(1,2)),
			array(random_int(8,12),random_int(1,2)),
			array(random_int(13,20),random_int(1,2)),
			array(random_int(22,34),random_int(1,2)),
			array(random_int(35,48),random_int(1,2)),
			array(random_int(49,65),random_int(1,2)),
			array(random_int(68,80),random_int(1,2)),
			array(random_int(85,124),random_int(1,2)),
			array(random_int(135,287),random_int(1,2)),
			array(random_int(289,555),random_int(1,2)),
			array(random_int(580,987),random_int(1,2)),
			array(random_int(999,8754),random_int(1,2)),
			array(random_int(9000,89547),random_int(1,3)),
			array(random_int(99853,985412),random_int(1,2)),
			array(random_int(998541,1245551),random_int(1,3))
		);
	}

	/**
	 * Replace existing poison constraints.
	 * @param array $new_constraints
	 */
	public function replacePoisonConstraints(array $new_constraints): void
	{
		$this->_poison_constraints = $new_constraints;
	}

	/**
	 * Replace rehash depth.
	 * @param int $new_depth
	 */
	public function replaceRehashDepth(int $new_depth): void
	{
		$this->_salt_depth = $new_depth;
	}
}