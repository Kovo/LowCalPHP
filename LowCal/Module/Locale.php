<?php
declare(strict_types=1);

namespace LowCal\Module;

use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;

/**
 * Class Locale
 * The main Locale module class used for translations in views, among other places.
 * @package LowCal\Module
 */
class Locale extends Module
{
	/**
	 * All loaded translations are stored in this array.
	 * @var array
	 */
	protected $_translations = array();

	/**
	 * A registry of defined languages.
	 * @var array
	 */
	protected $_languages = array();

	/**
	 * Return the short-hand of the current language.
	 * @var string
	 */
	protected $_current_locale = '';

	/**
	 * The base translation dir used for loading files.
	 * @var string
	 */
	protected $_translations_dir = '';

	/**
	 * Locale constructor.
	 * @param Base $Base
	 */
	function __construct(Base $Base)
	{
		parent::__construct($Base);

		$this->_translations_dir = Config::get('TRANSLATIONS_DIR');
	}

	/**
	 * Set the base translations directory.
	 * @param string $directory
	 * @return Locale
	 */
	public function setTranslationsDirectory(string $directory): Locale
	{
		$this->_translations_dir = $directory;

		return $this;
	}

	/**
	 * Returns current translations base directory.
	 * @return string
	 */
	public function getTranslationsDirectory(): string
	{
		return $this->_translations_dir;
	}

	/**
	 * Get an array of exposed translations keys.
	 * To expose a translation, make sure its key starts with "exposed".
	 * @return array
	 */
	public function getExposed(): array
	{
		$exposed = array();

		if(!empty($this->_translations[$this->_current_locale]))
		{
			foreach($this->_translations[$this->_current_locale] as $key => $value)
			{
				if(substr($key,0,7) === 'exposed')
				{
					$exposed[$key] = $value;
				}
			}
		}

		return $exposed;
	}

	/**
	 * Register a language.
	 * @param string $shortForm
	 * @param string $longForm
	 * @param bool $autoload
	 * @return Locale
	 */
	public function addLanguage(string $shortForm, string $longForm, bool $autoload = false): Locale
	{
		$this->_languages[$longForm] = $shortForm;

		if($autoload)
		{
			$this->load($shortForm);
		}

		return $this;
	}

	/**
	 * Set the current language by short-hand id.
	 * @param string $shortform
	 * @param bool $autoload
	 * @return Locale
	 */
	public function setCurrentLocale(string $shortform, bool $autoload = true): Locale
	{
		$short_locale = $this->getShortLocaleId($shortform);
		$this->_current_locale = $short_locale;

		if($autoload)
		{
			$this->load($short_locale);
		}

		return $this;
	}

	/**
	 * Check and see if language exists.
	 * @param string $lang
	 * @return bool
	 */
	public function languageExists(string $lang): bool
	{
		if(empty($this->getShortLocaleId($lang)))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Check and see if any language exists.
	 * @return bool
	 */
	public function languagesExist(): bool
	{
		if(empty($this->_languages))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Load a translation file based on active language short-hand.
	 * @param string $localeoverride
	 * @return Locale
	 * @throws \Exception
	 */
	public function load(string $localeoverride = ''): Locale
	{
		$short_locale = ($localeoverride===''?$this->_current_locale:$this->getShortLocaleId($localeoverride));

		if(file_exists($this->_translations_dir.$short_locale.'.php'))
		{
			require_once $this->_translations_dir.$short_locale.'.php';

			if(!isset($this->_translations[$short_locale]))
			{
				$this->_translations[$short_locale] = array();
			}

			if(isset($translations) && is_array($translations) && !empty($translations))
			{
				$this->_translations[$short_locale] = array_merge($this->_translations[$short_locale], $translations);
			}
		}
		else
		{
			throw new \Exception('Could not find "'.Config::get('TRANSLATIONS_DIR').$short_locale.'.php"!', Codes::LOCALE_FILE_NOT_FOUND);
		}

		return $this;
	}

	/**
	 * Add another translations file not located in the base directory for translations.
	 * @param string $file
	 * @param string $localeoverride
	 * @return Locale
	 * @throws \Exception
	 */
	public function addAdditionalFile(string $file, string $localeoverride = ''): Locale
	{
		$short_locale = ($localeoverride===''?$this->_current_locale:$this->getShortLocaleId($localeoverride));

		if(file_exists($file))
		{
			require_once $file;

			if(!isset($this->_translations[$short_locale]))
			{
				$this->_translations[$short_locale] = array();
			}

			if(isset($translations) && is_array($translations) && !empty($translations))
			{
				$this->_translations[$short_locale] = array_merge($this->_translations[$short_locale], $translations);
			}
		}
		else
		{
			throw new \Exception('Could not find "'.$file.'"!', Codes::LOCALE_FILE_NOT_FOUND);
		}

		return $this;
	}

	/**
	 * Get short-hand id for provided language identifier (if it is registered).
	 * @param string $locale
	 * @return string|null
	 */
	public function getShortLocaleId(string $locale): ?string
	{
		if(isset($this->_languages[$locale]))
		{
			return $this->_languages[$locale];
		}
		elseif(in_array($locale, $this->_languages))
		{
			return $locale;
		}
		else
		{
			return null;
		}
	}


	/**
	 * Get long-hand id for provided language identifier (if it is registered).
	 * @param string $locale
	 * @return string|null
	 */
	public function getLongLocaleId(string $locale): ?string
	{
		if(isset($this->_languages[$locale]))
		{
			return $locale;
		}
		elseif(in_array($locale, $this->_languages))
		{
			return array_search($locale, $this->_languages);
		}
		else
		{
			return null;
		}
	}

	/**
	 * Return the translation based on current active language and key.
	 * You can provide replacements to drop into the translation string as well. Replacements are identified as: %replace me%
	 * @param string $key
	 * @param array $replacements
	 * @param string $locale_override
	 * @param bool $no_replace
	 * @return string
	 */
	public function translate(string $key, array $replacements = array(), string $locale_override = '', bool $no_replace = false): string
	{
		$short_locale = ($locale_override===''?$this->_current_locale:$this->getShortLocaleId($locale_override));

		if(isset($this->_translations[$short_locale][$key]))
		{
			$return_string = $this->_translations[$short_locale][$key];

			if($no_replace === false && !empty($replacements))
			{
				foreach($replacements as $key => $value)
				{
					$return_string = str_replace('%'.$key.'%', $value, $return_string);
				}
			}

			return $return_string;
		}
		else
		{
			return $key;
		}
	}

	/**
	 * Get current locale id.
	 * @return string
	 */
	public function getCurrentLocale(): string
	{
		return $this->_current_locale;
	}
}