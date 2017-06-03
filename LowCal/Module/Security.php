<?php
declare(strict_types=1);
namespace LowCal\Module;
use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;
use LowCal\Helper\Strings;

/**
 * Class Security
 * @package LowCal\Module
 */
class Security extends Module
{
	/**
	 * Flag for two-way encryption.
	 */
	const TWO_WAY = 3;

	/**
	 * Flag for one-way encryption.
	 */
	const ONE_WAY = 4;

	/**
	 * Flag for strict hashing/encryption.
	 */
	const STRICT = 5;

	/**
	 * Custom rules flag array key for override hash.
	 */
	const HASH = 6;

	/**
	 * Custom rules flag array key for override salt.
	 */
	const SALT = 7;

	/**
	 * Custom rules flag array key for override poison constraints.
	 */
	const POISON_CONSTRAINTS = 8;

	/**
	 * Custom rules flag array key for override unique salt.
	 */
	const UNIQUE_SALT = 9;

	/**
	 * Flag to indicate encrypted string is poisoned.
	 */
	const DE_POISON = 10;

	/**
	 * @var int
	 */
	protected $_salt_depth = 1024;

	/**
	 * @var string
	 */
	protected $_hash = 'md5';

	/**
	 * @var string
	 */
	protected $_salt = 'B^M#@^|>2x =<7r)t%M%y@X]8mK3b+9:e86.*6;|diL#&^|o$Ovu#K*Y>q!a<.r]_d#';

	/**
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
	 * @var array
	 */
	protected $_hash_table_from = array(
		0 => 'q',1 => 'e',2 => 'u',3 => 't',4 => 'd',5 => 'w',6 => 'n',7 => 'v',8 => 'r',9 => 'h',10 => 'o',11 => 'm',12 => 'j',13 => 'l',14 => 'i',15 => 's',16 => 'y',17 => 'b',18 => 'z',19 => 'x',20 => 'f',21 => 'p',22 => 'k',23 => 'c',24 => 'a',25 => 'g',26 => 'Q',27 => 'C',28 => 'Z',29 => 'H',30 => 'P',31 => 'B',32 => 'X',33 => 'N',34 => 'W',35 => 'V',36 => 'E',37 => 'O',38 => 'J',39 => 'Y',40 => 'A',41 => 'R',42 => 'I',43 => 'S',44 => 'K',45 => 'F',46 => 'T',47 => 'U',48 => 'D',49 => 'L',50 => 'G',51 => 'M',52 => '2',53 => '6',54 => '5',55 => '0',56 => '9',57 => '1',58 => '8',59 => '3',60 => '7',61 => '4',62 => '`',63 => '!',64 => '@',65 => '#',66 => '$',67 => '%',68 => '^',69 => '&',70 => '*',71 => '(',72 => ')',73 => '-',74 => '_',75 => '=',76 => '+',77 => '[',78 => '{',79 => ']',80 => '}',81 => ';',82 => ':',83 => '\'',84 => '"',85 => '<',86 => '>',87 => ',',88 => '.',89 => '/',90 => '?',91 => '~',92 => '|',93 => '\\',94 => 'À',95 => 'à',96 => 'Á',97 => 'á',98 => 'Â',99 => 'â',100 => 'Ã',101 => 'ã',102 => 'Ä',103 => 'ä',104 => 'Å',105 => 'å',106 => 'Æ',107 => 'æ',108 => 'Ç',109 => 'ç',110 => 'È',111 => 'è',112 => 'É',113 => 'é',114 => 'Ê',115 => 'ê',116 => 'Ë',117 => 'ë',118 => 'Ì',119 => 'ì',120 => 'Í',121 => 'í',122 => 'Î',123 => 'î',124 => 'Ï',125 => 'ï',126 => 'µ',127 => 'Ñ',128 => 'ñ',129 => 'Ò',130 => 'ò',131 => 'Ó',132 => 'ó',133 => 'Ô',134 => 'ô',135 => 'Õ',136 => 'õ',137 => 'Ö',138 => 'ö',139 => 'Ø',140 => 'ø',141 => 'ß',142 => 'Ù',143 => 'ù',144 => 'Ú',145 => 'ú',146 => 'Û',147 => 'û',148 => 'Ü',149 => 'ü',150 => 'ÿ',151 => '¨',152 => '¯',153 => '´',154 => '¸',155 => '¡',156 => '¿',157 => '·',158 => '«',159 => '»',160 => '¶',161 => '§',162 => '©',163 => '®',164 => '÷',165 => 'ª',166 => 'º',167 => '¬',168 => '°',169 => '±',170 => '¤',171 => '¢',172 => '£',173 => '¥',174 => ' ',175 => 'Ð',176 => 'ð',177 => 'Þ',178 => 'þ',179 => 'Ý',180 => 'ý',181 => '¦',182 => '¹',183 => '²',184 => '³',185 => '×',186 => '¼',187 => '½',188 => '¾',189 => 'Δ',190 => 'ƒ',191 => 'Ω',192 => 'Œ',193 => 'œ',194 => 'Š',195 => 'š',196 => 'Ÿ',197 => 'ı',198 => 'ˆ',199 => 'ˇ',200 => '˘',201 => '˚',202 => '˙',203 => '˛',204 => '˝',205 => '˜',206 => '–',207 => '—',208 => '†',209 => '‡',210 => '•',211 => '…',212 => '‘',213 => '’',214 => '‚',215 => '“',216 => '”',217 => '„',218 => '‹',219 => '›',220 => '™',221 => '℠',222 => '℗',223 => '√',224 => '∞',225 => '∫',226 => '∂',227 => '≅',228 => '≠',229 => '≤',230 => '≥',231 => 'Σ',232 => '‰',233 => '⁄',234 => '⌘',235 => '⌥',236 => '☮',237 => '☯'
	);

	/**
	 * @var array
	 */
	protected $_hash_table_to = array(
		0 => '1', 1 => '2',2 => '3',3 => '4',4 => '5',5 => '6',6 => '7',7 => '8',8 => '9',9 => '0',10 => 'a',11 => 'b',12 => 'c',13 => 'd',14 => 'e',15 => 'f',16 => 'g',17 => 'h',18 => 'i',19 => 'j',20 => 'k',21 => 'l',22 => 'm',23 => 'n',24 => 'o',25 => 'p',26 => 'q',27 => 'r',28 => 's',29 => 't',30 => 'u',31 => 'v',32 => 'w',33 => 'x',34 => 'y',35 => 'z',36 => 'A',37 => 'B',38 => 'C',39 => 'D',40 => 'E',41 => 'F',42 => 'G',43 => 'H',44 => 'I',45 => 'J',46 => 'K',47 => 'L',48 => 'M',49 => 'N',50 => 'O',51 => 'P',52 => 'Q',53 => 'R',54 => 'S',55 => 'T',56 => 'U',57 => 'V',58 => 'W',59 => 'X',60 => 'Y',61 => 'Z',62 => '\'',63 => '`',64 => '~',65 => '!',66 => '@',67 => '#',68 => '$',69 => '%',70 => '^',71 => '&',72 => '*',73 => '(',74 => ')',75 => '-',76 => '_',77 => '+',78 => '=',79 => '|',80 => '\\',81 => '[',82 => ']',83 => '}',84 => '{',85 => ';',86 => ':',87 => '"',88 => ',',89 => '<',90 => '>',91 => '.',92 => '?',93 => '/',94 => 'Ë',95 => 'ê',96 => 'Ê',97 => 'é',98 => 'É',99 => 'è',100 => 'È',101 => 'ç',102 => 'å',103 => 'æ',104 => 'Æ',105 => 'Ç',106 => 'Å',107 => 'ä',108 => 'Ä',109 => 'ã',110 => 'Ã',111 => 'â',112 => 'Â',113 => 'á',114 => 'Á',115 => 'à',116 => 'À',117 => 'í',118 => 'Î',119 => 'î',120 => 'Ï',121 => 'ï',122 => 'Í',123 => 'ì',124 => 'Ì',125 => 'ë',126 => 'ÿ',127 => 'ü',128 => 'Ü',129 => 'û',130 => 'Û',131 => 'ú',132 => 'Ú',133 => 'ù',134 => 'Ù',135 => 'ß',136 => 'ø',137 => 'Ø',138 => 'µ',139 => 'Ñ',140 => 'ñ',141 => 'Ò',142 => 'ò',143 => 'Ó',144 => 'ó',145 => 'Ô',146 => 'ô',147 => 'Õ',148 => 'õ',149 => 'Ö',150 => 'ö',151 => '£',152 => '¢',153 => '¤',154 => '±',155 => '°',156 => '¬',157 => 'º',158 => 'ª',159 => '÷',160 => '®',161 => '©',162 => '§',163 => '¶',164 => '»',165 => '«',166 => '·',167 => '¿',168 => '¡',169 => '¸',170 => '´',171 => '¯',172 => '¨',173 => 'Ÿ',174 => 'š',175 => 'Š',176 => 'œ',177 => 'Œ',178 => 'Ω',179 => 'ƒ',180 => 'Δ',181 => '¾',182 => '½',183 => '¼',184 => '×',185 => '³',186 => '¹',187 => '¹',188 => '¦',189 => 'ý',190 => 'Ý',191 => 'þ',192 => 'Þ',193 => 'ð',194 => 'Ð',195 => ' ',196 => '¥',197 => '•',198 => '‚',199 => '℗',200 => '℠',201 => '™',202 => '›',203 => '‹',204 => '„',205 => '”',206 => '“',207 => '’',208 => '‘',209 => '…',210 => '˘',211 => '‡',212 => '†',213 => '—',214 => '–',215 => '˜',216 => '˝',217 => '˛',218 => '˙',219 => '˚',220 => 'ˇ',221 => 'ˆ',222 => 'ı',223 => '☯',224 => '☮',225 => '⌥',226 => '⌘',227 => '⁄',228 => '‰',229 => 'Σ',230 => '≅',231 => '≤',232 => '≠',233 => '≥',234 => '∂',235 => '∫',236 => '∞',237 => '√'
	);

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
	 * @param string $value
	 * @return string
	 */
	public function twoWayEncrypt(string $value): string
	{
		return $this->encrypt($value, array(self::TWO_WAY));
	}

	/**
	 * @param string $value
	 * @return string
	 */
	public function twoWayDecrypt(string $value): string
	{
		return $this->decrypt($value, array(self::DE_POISON));
	}

	/**
	 * @param string $value
	 * @return string
	 */
	public function oneWayEncrypt(string $value): string
	{
		return $this->encrypt($value, array(self::ONE_WAY));
	}

	/**
	 * @param string $unhashed_value
	 * @param string $hashed_comparison_value
	 * @return bool
	 */
	public function oneWayHashComparison(string $unhashed_value, string $hashed_comparison_value): bool
	{
		return $this->compareHashes($unhashed_value, $hashed_comparison_value, array(self::ONE_WAY));
	}

	/**
	 * @param string $unhashed_value
	 * @param string $hashed_comparison_value
	 * @return bool
	 */
	public function twoWayHashComparison(string $unhashed_value, string $hashed_comparison_value): bool
	{
		return $this->compareHashes($unhashed_value, $hashed_comparison_value);
	}

	/**
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
	 * @throws \Exception
	 */
	public function domainCheck(): void
	{
		$server_name = trim($_SERVER['SERVER_NAME']);

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
	 * @return array
	 */
	public function regenerateHash(): array
	{
		$hash_table_from = $this->_hash_table_from;

		shuffle($hash_table_from);

		return array_values($hash_table_from);
	}

	/**
	 * @param array $new_from_table_hash_array
	 */
	public function replaceHash(array $new_from_table_hash_array): void
	{
		$this->_hash_table_from = $new_from_table_hash_array;
	}

	/**
	 * @return string
	 */
	public function regenerateSalt(): string
	{
		return Strings::createCode(mt_rand(40,45), Strings::ALPHANUMERIC_PLUS);
	}

	/**
	 * @param string $new_salt
	 */
	public function replaceSalt(string $new_salt): void
	{
		$this->_salt = $new_salt;
	}

	/**
	 * @return array
	 */
	public function regeneratePoisonConstraints(): array
	{
		return array(
			array(mt_rand(0,5),mt_rand(1,2)),
			array(mt_rand(8,12),mt_rand(1,2)),
			array(mt_rand(13,20),mt_rand(1,2)),
			array(mt_rand(22,34),mt_rand(1,2)),
			array(mt_rand(35,48),mt_rand(1,2)),
			array(mt_rand(49,65),mt_rand(1,2)),
			array(mt_rand(68,80),mt_rand(1,2)),
			array(mt_rand(85,124),mt_rand(1,2)),
			array(mt_rand(135,287),mt_rand(1,2)),
			array(mt_rand(289,555),mt_rand(1,2)),
			array(mt_rand(580,987),mt_rand(1,2)),
			array(mt_rand(999,8754),mt_rand(1,2)),
			array(mt_rand(9000,89547),mt_rand(1,3)),
			array(mt_rand(99853,985412),mt_rand(1,2)),
			array(mt_rand(998541,1245551),mt_rand(1,3))
		);
	}

	/**
	 * @param array $new_constraints
	 */
	public function replacePoisonConstraints(array $new_constraints): void
	{
		$this->_poison_constraints = $new_constraints;
	}

	/**
	 * @param int $new_depth
	 */
	public function replaceRehashDepth(int $new_depth): void
	{
		$this->_salt_depth = $new_depth;
	}
}