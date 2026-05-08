<?php

use PHPUnit\Framework\TestCase;
use Twig\Environment;

class TwigTest extends TestCase
{
    public function testTwigEnvironmentIsInitialized()
    {
        // Wir müssen die Datei inkludieren, aber da sie require_once verwendet,
        // kann es sein, dass sie schon woanders inkludiert wurde.
        // Die Datei definiert $twig in einer anonymen Funktion und weist sie $twig zu.
        $twig = null;
        require __DIR__ . '/../public/includes/twig.php';

        $this->assertInstanceOf(Environment::class, $twig);
    }

    public function testTwigHasCorrectLoaderPath()
    {
        $twig = null;
        require __DIR__ . '/../public/includes/twig.php';

        $loader = $twig->getLoader();
        $this->assertInstanceOf(\Twig\Loader\FilesystemLoader::class, $loader);
        
        $paths = $loader->getPaths();
        $this->assertNotEmpty($paths);
        $this->assertStringContainsString('templates', $paths[0]);
    }

    public function testTwigOptions()
    {
        $twig = null;
        require __DIR__ . '/../public/includes/twig.php';

        $this->assertEquals('UTF-8', $twig->getCharset());
        $this->assertFalse($twig->isDebug());
    }
}
