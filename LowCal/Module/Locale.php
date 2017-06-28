<?php
declare(strict_types=1);
namespace LowCal\Module;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;

/**
 * Class Locale
 * @package LowCal\Module
 */
class Locale extends Module
{
	/**
	 * @var array
	 */
	protected $_translations = array();

	/**
	 * @var array
	 */
	protected $_languages = array();

	/**
	 * @var string
	 */
	protected $_current_locale = '';

	/**
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
	 * @param string $localeoverride
	 * @return Locale
	 * @throws \Exception
	 */
	public function load(string $localeoverride = ''): Locale
	{
		$short_locale = ($localeoverride===''?$this->_current_locale:$this->getShortLocaleId($localeoverride));

		if(file_exists(Config::get('TRANSLATIONS_DIR').$short_locale.'.php'))
		{
			require_once Config::get('TRANSLATIONS_DIR').$short_locale.'.php';

			if(!isset($this->_translations[$short_locale]))
			{
				$this->_translations[$short_locale] = array();
			}

			if(isset($TRANSLATIONS) && is_array($TRANSLATIONS) && !empty($TRANSLATIONS))
			{
				$this->_translations[$short_locale] = array_merge($this->_translations[$short_locale], $TRANSLATIONS);
			}
		}
		else
		{
			throw new \Exception('Could not find "'.Config::get('TRANSLATIONS_DIR').$short_locale.'.php"!', Codes::LOCALE_FILE_NOT_FOUND);
		}

		return $this;
	}

	/**
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

			if(isset($TRANSLATIONS) && is_array($TRANSLATIONS) && !empty($TRANSLATIONS))
			{
				$this->_translations[$short_locale] = array_merge($this->_translations[$short_locale], $TRANSLATIONS);
			}
		}
		else
		{
			throw new \Exception('Could not find "'.$file.'"!', Codes::LOCALE_FILE_NOT_FOUND);
		}

		return $this;
	}

	/**
	 * @param string $locale
	 * @return string
	 */
	public function getShortLocaleId(string $locale): string
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
			return '';
		}
	}


	/**
	 * @param string $locale
	 * @return string
	 */
	public function getLongLocaleId(string $locale): string
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
			return '';
		}
	}

	/**
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
	 * @return string
	 */
	public function getCurrentLocale(): string
	{
		return $this->_current_locale;
	}
}