<?php
class ClassLoader
{
    /**
     * @var array
     */
    private $_namespaces = array();

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
        foreach ($this->_namespaces as $unNamespace => $configNamespace) {
            $fileName = $configNamespace['path'] . DIRECTORY_SEPARATOR;
            $fileName .= substr($className, strpos($unNamespace, $className) + strlen($unNamespace) + 1);
            $fileName .= $configNamespace['extension'];
            if (!empty($unNamespace) && substr_count(strtolower($className), $unNamespace) > 0 && file_exists(
                $fileName
            )
            ) {
                include_once($fileName);

                return true;
            }
        }

        return false;
    }
}