<?php
class ClassLoader
{
    /**
     * @var array
     */
    private $_namespaces = array();

    /**
     * @var array
     */
    private $_cacheMapping = array();

    /**
     * @param string $namespace
     * @param string $includePath
     * @param string $extension
     */
    public function ajouterNamespace($namespace, $includePath, $extension = '.class.php')
    {
        if (!$this->startsWith($extension, '.')) {
            $extension = '.' . $extension;
        }

        $this->_namespaces[strtolower($namespace)] = array('path' => $includePath, 'extension' => $extension);
    }

    /**
     * @param string $string
     * @param string $stringRecherche
     * @return bool
     */
    public function startsWith($string, $stringRecherche)
    {
        return !strncmp($string, $stringRecherche, strlen($stringRecherche));
    }

    /**
     * @return bool
     */
    public function register()
    {
        return spl_autoload_register(array($this, 'loaderFunction')) === true;
    }

    /**
     * @param string $namespace
     * @throws \Exception
     * @return bool
     */
    public function unregister($namespace = '')
    {
        if (!empty($namespace)) {
            if (array_key_exists(strtolower($namespace), $this->_namespaces)) {
                unset($this->_namespaces[strtolower($namespace)]);

                return true;
            } else {
                throw new \Exception('Namespace ' . $namespace . ' not found');
            }
        } else {
            return spl_autoload_unregister(array($this, 'loaderFunction')) === true;
        }
    }

    /**
     * @param string $className
     * @return bool
     */
    public function loaderFunction($className)
    {
        if (array_key_exists($className, $this->_cacheMapping)) {
            return false;
        }

        foreach ($this->_namespaces as $unNamespace => $configNamespace) {
            if (!empty($unNamespace) && substr_count(strtolower($className), $unNamespace) > 0 && file_exists(
                $fileName = $configNamespace['path'] . DIRECTORY_SEPARATOR .
                    substr($className, strpos($unNamespace, $className) + strlen($unNamespace) + 1) .
                    $configNamespace['extension']
            )
            ) {
                $this->_cacheMapping[$className] = true;

                include_once($fileName);

                return true;
            }
        }

        return false;
    }
}