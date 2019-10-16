<?php



/**
 * This is the dotenv class.
 *
 * It's responsible for loading a `.env` file in the given directory and
 * setting the environment vars.
 */
class Dotenv_Dotenv
{
    /**
     * The file path.
     *
     * @var string
     */
    protected $filePath;

    /**
     * The loader instance.
     *
     * @var Dotenv_Loader|null
     */
    protected $loader;

    /**
     * Create a new dotenv instance.
     *
     * @param string $path
     * @param string $file
     *
     * @return void
     */
    public function __construct($path, $file = '.env')
    {
        $this->filePath = $this->getFilePath($path, $file);
        $this->loader = new Dotenv_Loader($this->filePath, true);
    }

    /**
     * Load environment file in given directory.
     *
     * @throws Dotenv_Exception_InvalidPathException|\Dotenv\Exception\InvalidFileException
     *
     * @return array
     */
    public function load()
    {
        return $this->loadData();
    }

    /**
     * Load environment file in given directory, suppress InvalidPathException.
     *
     * @throws \Dotenv\Exception\InvalidFileException
     *
     * @return array
     */
    public function safeLoad()
    {
        try {
            return $this->loadData();
        } catch (Dotenv_Exception_InvalidPathException $e) {
            // suppressing exception
            return array();
        }
    }

    /**
     * Load environment file in given directory.
     *
     * @throws Dotenv_Exception_InvalidPathException|\Dotenv\Exception\InvalidFileException
     *
     * @return array
     */
    public function overload()
    {
        return $this->loadData(true);
    }

    /**
     * Returns the full path to the file.
     *
     * @param string $path
     * @param string $file
     *
     * @return string
     */
    protected function getFilePath($path, $file)
    {
        if (!is_string($file)) {
            $file = '.env';
        }

        $filePath = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$file;

        return $filePath;
    }

    /**
     * Actually load the data.
     *
     * @param bool $overload
     *
     * @throws Dotenv_Exception_InvalidPathException|\Dotenv\Exception\InvalidFileException
     *
     * @return array
     */
    protected function loadData($overload = false)
    {
        return $this->loader->setImmutable(!$overload)->load();
    }

    /**
     * Required ensures that the specified variables exist, and returns a new validator object.
     *
     * @param string|string[] $variable
     *
     * @return Dotenv_Validator
     */
    public function required($variable)
    {
        return new Dotenv_Validator((array) $variable, $this->loader);
    }

    /**
     * Get the list of environment variables declared inside the 'env' file.
     *
     * @return array
     */
    public function getEnvironmentVariableNames()
    {
        return $this->loader->variableNames;
    }
}
