<?php

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

class ClassLoaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var ClassLoader */
    private $_classLoader;

    protected function setUp()
    {
        $this->_classLoader = new ClassLoader();
    }

    public function testExtension()
    {
        $this->_classLoader->setExtension('.class.php');

        $this->assertAttributeEquals('.class.php', '_extension', $this->_classLoader);
    }

    public function testAjouterNamespace()
    {
        $this->_classLoader->ajouterNamespace('myNamespace', '/path/');
        $this->assertAttributeEquals(
            array('mynamespace' => array('path' => '/path/', 'extension' => '.php')), '_namespaces',
            $this->_classLoader
        );
    }

    public function testAjouterNamespaceRajoutePointSiExtension()
    {
        $this->_classLoader->ajouterNamespace('myNamespace', '/path/', 'php');
        $this->assertAttributeEquals(
            array('mynamespace' => array('path' => '/path/', 'extension' => '.php')), '_namespaces', $this->_classLoader
        );
    }

    public function testLoaderFunction()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new \org\bovigo\vfs\vfsStreamDirectory('realPath'));

        mkdir(vfsStream::url('realPath') . '/folder');
        file_put_contents(vfsStream::url('realPath/folder') . '/Factice.php', '');

        $this->_classLoader->ajouterNamespace('MyNamespace', vfsStream::url('realPath/folder'));

        $this->assertTrue($this->_classLoader->loaderFunction('MyNamespace\Factice'));
    }

    public function testLoaderFunctionCache()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new \org\bovigo\vfs\vfsStreamDirectory('realPath'));

        mkdir(vfsStream::url('realPath') . '/folder');
        file_put_contents(vfsStream::url('realPath/folder') . '/Factice.php', '');

        $this->_classLoader->ajouterNamespace('MyNamespace', vfsStream::url('realPath/folder'));

        $this->_classLoader->loaderFunction('MyNamespace\Factice');
        $this->assertFalse($this->_classLoader->loaderFunction('MyNamespace\Factice'));
    }

    public function testLoaderFunctionNonTrouve()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new \org\bovigo\vfs\vfsStreamDirectory('realPath'));

        mkdir(vfsStream::url('realPath') . '/MyNamespace');
        file_put_contents(vfsStream::url('realPath/MyNamespace') . '/Factice.class.php', '');

        $this->_classLoader->ajouterNamespace('MyFakeNamespace', vfsStream::url('testPath'));

        $this->assertFalse($this->_classLoader->loaderFunction('MyNamespace\Factice'));
    }

    public function testRegisterNamespace()
    {
        $this->_classLoader->ajouterNamespace('myNamespace', '/path/', 'php');

        $this->assertTrue($this->_classLoader->register());
    }

    public function testUnregisterNamespace()
    {
        $this->_classLoader->ajouterNamespace('myNamespace', '/path/', 'php');

        $this->assertTrue($this->_classLoader->unregister('mynamespace'));
    }

    /**
     * @expectedException \Exception
     */
    public function testUnregisterNamespaceNonExistant()
    {
        $this->assertFalse($this->_classLoader->unregister('inexistant'));
    }

    public function testUnregisterClassEntiere()
    {
        $this->assertFalse($this->_classLoader->unregister());
        $this->_classLoader->register();
        $this->assertTrue($this->_classLoader->unregister());
    }

    public function testLoaderFunctionClassCache()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new \org\bovigo\vfs\vfsStreamDirectory('realPath'));

        mkdir(vfsStream::url('realPath') . '/folder');
        file_put_contents(vfsStream::url('realPath/folder') . '/Factice.class.php', '');

        $this->_classLoader->ajouterNamespace('MyNamespace', vfsStream::url('realPath/folder'));

        $this->_classLoader->loaderFunction('MyNamespace\Factice');
        $this->assertFalse($this->_classLoader->loaderFunction('MyNamespace\Factice'));
    }

    public function testDifferentOs() {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new \org\bovigo\vfs\vfsStreamDirectory('realPath'));

        mkdir(vfsStream::url('realPath') . '/folder');
        mkdir(vfsStream::url('realPath/folder') . '/Deeper');
        file_put_contents(vfsStream::url('realPath/folder/Deeper') . '/Factice.php', '');

        $this->_classLoader->ajouterNamespace('MyNamespace', vfsStream::url('realPath/folder'));

        $this->_classLoader->loaderFunction('MyNamespace\Deeper\Factice');
        $this->assertFalse($this->_classLoader->loaderFunction('MyNamespace\Factice'));
    }
}