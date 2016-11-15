<?php
/**
 * i18n 支持
 *
 *
 *
lang_en.ini (English)

greeting = "Hello World!"

[category]
somethingother = "Something other..."
lang_de.ini (German)

greeting = "Hallo Welt!"

[category]
somethingother = "Etwas anderes..."

echo L::greeting;
// If 'en' is applied: 'Hello World'

echo L::category_somethingother;
// If 'en' is applied: 'Something other...'

echo L::last_modified("today");
// Could be: 'Last modified: today'

echo L($string);
// Outputs a dynamically chosen static property

echo L($string, $args);
// Same as L::last_modified("today");

 *
 */
namespace Kerisy\Lang;

class Language
{
    /**
     * Language file path
     * This is the path for the language files. You must use the '{LANGUAGE}' placeholder for the language or the script wont find any language files.
     *
     * @var string
     */
    protected $filePath = './lang/lang_{LANGUAGE}.ini';
    /**
     * Cache file path
     * This is the path for all the cache files. Best is an empty directory with no other files in it.
     *
     * @var string
     */
    protected $cachePath = './langcache/';
    /**
     * Fallback language
     * This is the language which is used when there is no language file for all other user languages. It has the lowest priority.
     * Remember to create a language file for the fallback!!
     *
     * @var string
     */
    protected $fallbackLang = 'en';
    /**
     * The class name of the compiled class that contains the translated texts.
     * @var string
     */
    protected $prefix = 'L';
    /**
     * Forced language
     * If you want to force a specific language define it here.
     *
     * @var string
     */
    protected $forcedLang = null;
    /**
     * This is the seperator used if you use sections in your ini-file.
     * For example, if you have a string 'greeting' in a section 'welcomepage' you will can access it via 'L::welcomepage_greeting'.
     * If you changed it to 'ABC' you could access your string via 'L::welcomepageABCgreeting'
     *
     * @var string
     */
    protected $sectionSeperator = '_';
    /*
     * The following properties are only available after calling init().
     */
    /**
     * User languages
     * These are the languages the user uses.
     * Normally, if you use the getUserLangs-method this array will be filled in like this:
     * 1. Forced language
     * 2. Language in $_GET['lang']
     * 3. Language in $_SESSION['lang']
     * 4. Fallback language
     *
     * @var array
     */
    protected $userLangs = array();
    protected $appliedLang = null;
    protected $langFilePath = null;
    protected $cacheFilePath = null;
    protected $isInitialized = false;

    protected static $userLang = "zh_cn";

    public function register()
    {
        $this->setCachePath(APPLICATION_PATH . "/runtime/langcomplie");
        $this->setFilePath(APPLICATION_PATH . '/config/lang/{LANGUAGE}.ini'); // language file path
        $this->setFallbackLang('zh_cn');
        $this->setPrefix('L');
        $this->setSectionSeperator('_');
        $this->init();
    }

    /**
     * Constructor
     * The constructor sets all important settings. All params are optional, you can set the options via extra functions too.
     *
     * @param string [$filePath] This is the path for the language files. You must use the '{LANGUAGE}' placeholder for the language.
     * @param string [$cachePath] This is the path for all the cache files. Best is an empty directory with no other files in it. No placeholders.
     * @param string [$fallbackLang] This is the language which is used when there is no language file for all other user languages. It has the lowest priority.
     * @param string [$prefix] The class name of the compiled class that contains the translated texts. Defaults to 'L'.
     */
    public function __construct($filePath = null, $cachePath = null, $fallbackLang = null, $prefix = null)
    {
        // Apply settings
        if ($filePath != null) {
            $this->filePath = $filePath;
        }
        if ($cachePath != null) {
            $this->cachePath = $cachePath;
        }
        if ($fallbackLang != null) {
            $this->fallbackLang = $fallbackLang;
        }
        if ($prefix != null) {
            $this->prefix = $prefix;
        }
    }


    public static function setUserLang($userLang)
    {
        self::$userLang = $userLang;
    }

    public function init()
    {
        if ($this->isInitialized()) {
            throw new BadMethodCallException('This object from class ' . __CLASS__ . ' is already initialized. It is not possible to init one object twice!');
        }
        $this->isInitialized = true;
        $this->userLangs = $this->getUserLangs();
        // search for language file
        $this->appliedLang = null;
        foreach ($this->userLangs as $priority => $langcode) {
            $this->langFilePath = str_replace('{LANGUAGE}', $langcode, $this->filePath);
            if (file_exists($this->langFilePath)) {
                $this->appliedLang = $langcode;
                break;
            }
        }
        if ($this->appliedLang == null) {
            throw new RuntimeException('No language file was found.');
        }
        // search for cache file
        $this->cacheFilePath = $this->cachePath . '/php_i18n_' . md5_file(__FILE__) . '_' . $this->prefix . '_' . $this->appliedLang . '.cache.php';
        // if no cache file exists or if it is older than the language file create a new one
        if (!file_exists($this->cacheFilePath) || filemtime($this->cacheFilePath) < filemtime($this->langFilePath)) {
            switch ($this->getFileExtension()) {
                case 'properties':
                case 'ini':
                    $config = parse_ini_file($this->langFilePath, true);
                    break;
                case 'yml':
                    $config = spyc_load_file($this->langFilePath);
                    break;
                case 'json':
                    $config = json_decode(file_get_contents($this->langFilePath), true);
                    break;
                default:
                    throw new InvalidArgumentException($this->get_file_extension() . " is not a valid extension!");
            }
            $compiled = "<?php class " . $this->prefix . " {\n"
                . $this->compile($config)
                . 'public static function __callStatic($string, $args) {' . "\n"
                . '  return vsprintf(constant("self::" . $string), $args);'
                . "\n}\n}\n"
                . "function " . $this->prefix . '($string, $args=NULL) {' . "\n"
                . '    $return = constant("' . $this->prefix . '::".$string);' . "\n"
                . '    return $args ? vsprintf($return,$args) : $return;'
                . "\n}";
            if (!is_dir($this->cachePath)) {
                mkdir($this->cachePath, 0777, true);
            }

            if (file_put_contents($this->cacheFilePath, $compiled) === false) {
                throw new Exception("Could not write cache file to path '" . $this->cacheFilePath . "'. Is it writable?");
            }
            chmod($this->cacheFilePath, 0777);
        }
        require_once $this->cacheFilePath;
    }

    public function isInitialized()
    {
        return $this->isInitialized;
    }

    public function getAppliedLang()
    {
        return $this->appliedLang;
    }

    public function getCachePath()
    {
        return $this->cachePath;
    }

    public function getFallbackLang()
    {
        return $this->fallbackLang;
    }

    public function setFilePath($filePath)
    {
        $this->failAfterInit();
        $this->filePath = $filePath;
    }

    public function setCachePath($cachePath)
    {
        $this->failAfterInit();
        $this->cachePath = $cachePath;
    }

    public function setFallbackLang($fallbackLang)
    {
        $this->failAfterInit();
        $this->fallbackLang = $fallbackLang;
    }

    public function setPrefix($prefix)
    {
        $this->failAfterInit();
        $this->prefix = $prefix;
    }

    public function setForcedLang($forcedLang)
    {
        $this->failAfterInit();
        $this->forcedLang = $forcedLang;
    }

    public function setSectionSeperator($sectionSeperator)
    {
        $this->failAfterInit();
        $this->sectionSeperator = $sectionSeperator;
    }

    /**
     * getUserLangs()
     * Returns the user languages
     * Normally it returns an array like this:
     * 1. Forced language
     * 2. Language in $_GET['lang']
     * 3. Language in $_SESSION['lang']
     * 4. HTTP_ACCEPT_LANGUAGE
     * 5. Fallback language
     * Note: duplicate values are deleted.
     *
     * @return array with the user languages sorted by priority.
     */
    public function getUserLangs()
    {
        $userLangs = array();
        // Highest priority: forced language
        if ($this->forcedLang != null) {
            $userLangs[] = $this->forcedLang;
        }

        if(self::$userLang){
            $userLangs[] = self::$userLang;
        }

        // Lowest priority: fallback
        $userLangs[] = $this->fallbackLang;
        // remove duplicate elements
        $userLangs = array_unique($userLangs);
        foreach ($userLangs as $key => $value) {
            $userLangs[$key] = preg_replace('/[^a-zA-Z0-9_-]/', '', $value); // only allow a-z, A-Z and 0-9
        }
        return $userLangs;
    }

    /**
     * Recursively compile an associative array to PHP code.
     */
    protected function compile($config, $prefix = '')
    {
        $code = '';
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $code .= $this->compile($value, $prefix . $key . $this->sectionSeperator);
            } else {
                $code .= 'const ' . $prefix . $key . ' = \'' . str_replace('\'', '\\\'', $value) . "';\n";
            }
        }
        return $code;
    }

    protected function getFileExtension()
    {
        return substr(strrchr($this->langFilePath, '.'), 1);
    }

    protected function failAfterInit()
    {
        if ($this->isInitialized()) {
            throw new BadMethodCallException('This ' . __CLASS__ . ' object is already initalized, so you can not change any settings.');
        }
    }
}
