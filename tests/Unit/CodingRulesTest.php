<?php
namespace flat\test\Unit;
use PHPUnit\Framework\TestCase;

/**
 * Enforces three rules of AGENTS.md ("Class names: never class_alias") over the source:
 * no class_alias; every class is declared where its path says; classes are loaded by the autoloader, never by require or include.
 * The third-party libraries in src/flat/vendor are not flat's code and are skipped.
 */
class CodingRulesTest extends TestCase
{
    private static function phpFiles($relativeDir)
    {
        $root = dirname(__DIR__, 2);
        $files = [];
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator("$root/$relativeDir", \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            $path = str_replace('\\', '/', $file->getPathname());
            if (substr($path, -4) !== '.php' || strpos($path, '/src/flat/vendor/') !== false) continue;
            $files[] = substr($path, strlen($root) + 1);
        }
        sort($files);
        return $files;
    }

    /** @return array [namespace, [declared names]] of one file */
    private static function declarations($file)
    {
        $tokens = token_get_all(file_get_contents(dirname(__DIR__, 2).'/'.$file));
        $namespace = ''; $names = []; $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            if (!is_array($tokens[$i])) continue;
            if ($tokens[$i][0] === T_NAMESPACE) {
                $namespace = ''; $j = $i + 1;
                while ($j < $count && is_array($tokens[$j]) && in_array($tokens[$j][0], [T_WHITESPACE, T_STRING, T_NS_SEPARATOR, T_NAME_QUALIFIED], true)) {
                    if ($tokens[$j][0] !== T_WHITESPACE) $namespace .= $tokens[$j][1];
                    $j++;
                }
            } elseif (in_array($tokens[$i][0], [T_CLASS, T_INTERFACE, T_TRAIT], true)) {
                $before = $i - 1;
                while ($before >= 0 && is_array($tokens[$before]) && $tokens[$before][0] === T_WHITESPACE) $before--;
                if ($before >= 0 && is_array($tokens[$before]) && in_array($tokens[$before][0], [T_DOUBLE_COLON, T_NEW], true)) continue; // ::class, new class
                $j = $i + 1;
                while ($j < $count && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
                if ($j < $count && is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) $names[] = ($namespace === '' ? '' : $namespace.'\\').$tokens[$j][1];
            }
        }
        return $names;
    }

    public function testEverySourceFileParsesOnThisPhpRuntime()
    {
        foreach (self::phpFiles('src') as $file) {
            try {
                $tokens = token_get_all(file_get_contents(dirname(__DIR__, 2).'/'.$file), TOKEN_PARSE);
            } catch (\ParseError $error) {
                $this->fail($file.': '.$error->getMessage());
            }
            $this->assertNotEmpty($tokens, $file);
        }
    }

    private static function hasClassAlias($source)
    {
        foreach (token_get_all($source) as $token) {
            if (!is_array($token) || !in_array($token[0], [T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true)) continue;
            $parts = explode('\\', $token[1]);
            if (strtolower(end($parts)) === 'class_alias') return true;
        }
        return false;
    }

    /** @dataProvider aliasSyntax */
    public function testAliasGuardHandlesPhp80NameTokens($source, $expected)
    {
        $this->assertSame($expected, self::hasClassAlias($source));
    }
    public function aliasSyntax()
    {
        return [
            ['<?php class_alias("A", "B");', true],
            ['<?php \\class_alias("A", "B");', true],
            ['<?php custom\\class_alias("A", "B");', true],
            ['<?php // class_alias("A", "B");', false],
            ['<?php $text = "class_alias";', false],
        ];
    }

    public function testClassAliasIsNeverUsed()
    {
        $found = [];
        foreach (array_merge(self::phpFiles('src'), self::phpFiles('tests'), self::phpFiles('bin'), self::phpFiles('docker')) as $file) {
            if (self::hasClassAlias(file_get_contents(dirname(__DIR__, 2).'/'.$file))) $found[] = $file;
        }
        $this->assertSame([], $found, 'class_alias is forbidden (AGENTS.md)');
    }

    public function testEveryClassIsDeclaredWhereItsPathSays()
    {
        $wrong = [];
        foreach (self::phpFiles('src') as $file) {
            $declared = self::declarations($file);
            if ($declared === []) continue; // an empty file or one without a class
            $expected = str_replace('/', '\\', substr($file, strlen('src/'), -4));
            if (!in_array($expected, $declared, true)) $wrong[$file] = $declared;
        }
        $this->assertSame([], $wrong, 'each file must declare the class its path names (AGENTS.md)');
    }

    public function testClassesAreLoadedByTheAutoloaderNeverByInclude()
    {
        $including = [];
        foreach (self::phpFiles('src') as $file) {
            foreach (token_get_all(file_get_contents(dirname(__DIR__, 2).'/'.$file)) as $token) {
                if (is_array($token) && in_array($token[0], [T_REQUIRE, T_REQUIRE_ONCE, T_INCLUDE, T_INCLUDE_ONCE], true)) { $including[] = $file; break; }
            }
        }
        // Only these two include files that are data, never classes: the config loader (a file that returns an array) and the
        // template controller (template files).
        $this->assertSame(['src/flat/core/config.php', 'src/flat/core/controller/tmpl.php'], $including);
    }
}
