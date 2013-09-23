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
     * @var string
     */
    private $_extension;

    /**
     * @param string $extension
     */
    public function __construct($extension = '.php')
    {
        $this->setExtension($extension);
    }

    /**
     * @param string $extension
     */
    public function setExtension($extension)
    {
        $this->_extension = $this->rajouterPointExtensionSiNonPresent($extension);
    }

    private function rajouterPointExtensionSiNonPresent($extension)
    {
        if (strncmp($extension, '.', strlen('.'))) {
            $extension = '.' . $extension;
        }

        return $extension;
    }

    /**
     * @param string $namespace
     * @param string $includePath
     * @param string $extension
     */
    public function ajouterNamespace($namespace, $includePath, $extension = null)
    {
        if (is_null($extension)) {
            $extension = $this->_extension;
        } else {
            $extension = $this->rajouterPointExtensionSiNonPresent($extension);
        }

        $this->_namespaces[strtolower($namespace)] = array('path' => $includePath, 'extension' => $extension);
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
        $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);

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

                require_once($fileName);

                return true;
            }
        }

        return false;
    }
}