<?php

namespace OpenOrchestra\FrontBundle\Tests\Twig;

use OpenOrchestra\FrontBundle\Twig\OrchestraTimedTwigEngine;
use Phake;
use Twig_Environment;

/**
 * Class OrchestraTimedTwigEngineTest
 */
class RenderableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrchestraTimedTwigEngine
     */
    protected $twig;
    protected $devices;
    protected $twigEnv;
    protected $request;
    protected $stopWatch;
    protected $fileLocator;
    protected $templateName;
    protected $requestStack;
    protected $engineInterface;
    protected $twigLoaderInterface;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->twigEnv = Phake::mock('Twig_Environment');
        $this->request = Phake::mock('Symfony\Component\HttpFoundation\Request');
        $this->stopWatch = Phake::mock('Symfony\Component\Stopwatch\Stopwatch');
        $this->fileLocator = Phake::mock('Symfony\Component\Config\FileLocatorInterface');
        $this->templateName = Phake::mock('Symfony\Component\Templating\TemplateNameParserInterface');
        $this->requestStack = Phake::mock('Symfony\Component\HttpFoundation\RequestStack');
        Phake::when($this->requestStack)->getMasterRequest()->thenReturn($this->request);

        $this->twigLoaderInterface = Phake::mock('Twig_ExistsLoaderInterface');
        Phake::when($this->twigEnv)->getLoader()->thenReturn($this->twigLoaderInterface);

        $this->devices = array('web' => array('parent' => null), 'mobile' => array('parent' => 'web'), 'android' => array('parent' => 'mobile'));

        $this->twig = new OrchestraTimedTwigEngine($this->twigEnv, $this->templateName, $this->fileLocator, $this->stopWatch, $this->requestStack, $this->devices);
    }

    /**
     * @param string $name
     * @param string $expected
     * @param string $device
     *
     * @dataProvider generateTemplateName
     */
    public function testGetTemplate($name, $expected, $device)
    {
        Phake::when($this->request)->get('X-UA-Device')->thenReturn($device);
        Phake::when($this->twigLoaderInterface)->exists($name)->thenReturn(true);
        Phake::when($this->twigLoaderInterface)->exists($expected)->thenReturn(true);

        $result = $this->twig->getTemplate($name, $device);

        $this->assertSame($expected, $result);
    }

    /**
     * @param string $name
     * @param string $device
     *
     * @dataProvider generateTemplateNameExistFalse
     */
    public function testGetTemplateMobileFalse($name, $device)
    {
        Phake::when($this->request)->get('X-UA-Device')->thenReturn($device);
        Phake::when($this->twigLoaderInterface)->exists($name)->thenReturn(false);

        $result = $this->twig->getTemplate($name, $device);

        $this->assertSame($name, $result);
    }

    /**
     * @param string $name
     * @param string $expected
     * @param string $device
     *
     * @dataProvider generateTemplateNameMobileExistTrue
     */
    public function testGetTemplateWithMobileTrueAndAndroidFalse($name, $expected, $device)
    {
        Phake::when($this->request)->get('X-UA-Device')->thenReturn($device);
        Phake::when($this->twigLoaderInterface)->exists('OpenOrchestraFrontBundle:Node:show.mobile.html.twig')->thenReturn(true);
        Phake::when($this->twigLoaderInterface)->exists('OpenOrchestraFrontBundle:Node:show.mobile.html.smarty')->thenReturn(true);

        $result = $this->twig->getTemplate($name, $device);

        $this->assertSame($expected, $result);
    }

    /**
     * @return array
     */
    public function generateTemplateName()
    {
        return array(
            array('OpenOrchestraFrontBundle:Node:show.html.twig', 'OpenOrchestraFrontBundle:Node:show.html.twig', null),
            array('OpenOrchestraFrontBundle:Node:show.html.twig', 'OpenOrchestraFrontBundle:Node:show.android.html.twig', 'android'),
            array('OpenOrchestraDisplayBundle:Block/LanguageList:show.html.twig', 'OpenOrchestraDisplayBundle:Block/LanguageList:show.html.twig', ''),
            array('OpenOrchestraDisplayBundle:Block/LanguageList:show.html.twig', 'OpenOrchestraDisplayBundle:Block/LanguageList:show.mobile.html.twig', 'mobile'),
            array('OpenOrchestraFrontBundle:Node:show.html.smarty', 'OpenOrchestraFrontBundle:Node:show.html.smarty', null),
            array('OpenOrchestraFrontBundle:Node:show.html.smarty', 'OpenOrchestraFrontBundle:Node:show.android.html.smarty', 'android'),
        );
    }

    /**
     * @return array
     */
    public function generateTemplateNameExistFalse()
    {
        return array(
            array('OpenOrchestraFrontBundle:Node:show.html.twig', 'blob'),
            array('OpenOrchestraFrontBundle:Node:show.html.twig', 'android'),
            array('OpenOrchestraFrontBundle:Node:show.html.smarty', 'android'),
        );
    }

    /**
     * @return array
     */
    public function generateTemplateNameMobileExistTrue()
    {
        return array(
            array('OpenOrchestraFrontBundle:Node:show.html.smarty', 'OpenOrchestraFrontBundle:Node:show.mobile.html.smarty', 'android'),
            array('OpenOrchestraFrontBundle:Node:show.html.twig', 'OpenOrchestraFrontBundle:Node:show.mobile.html.twig', 'android'),
            array('OpenOrchestraFrontBundle:Node:show.html.twig', 'OpenOrchestraFrontBundle:Node:show.mobile.html.twig', 'mobile'),
            array('OpenOrchestraFrontBundle:Node:show.html.smarty', 'OpenOrchestraFrontBundle:Node:show.mobile.html.smarty', 'mobile'),
        );
    }
}
