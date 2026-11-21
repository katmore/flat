<?php
/**
 * Public API comparison between two versions of flat.
 *
 *   php tests/tools/apidump.php dump <src-root> [--autoload=FILE] > api.json
 *   php tests/tools/apidump.php compare old.json new.json > report.md
 *
 * <src-root> is the folder that contains `flat/` (the repository's `src`). `dump` loads
 * every class, interface and trait under it (third-party `vendor` folders excluded) with
 * Reflection and records what each declares that callers or subclasses can use: kind,
 * abstract/final, parent, interfaces, non-private constants, properties and methods
 * (visibility, static, abstract, final, parameters with types, defaults, by-reference
 * and variadic flags, and return type). Each class is loaded in its own PHP process,
 * because a class whose parent is missing is a fatal error that cannot be caught.
 * --autoload=FILE is a Composer autoloader for the third-party packages flat uses.
 *
 * `compare` writes a Markdown report of every difference. Needs PHP 7.3 or newer.
 */
if (PHP_SAPI !== 'cli') exit(1);
$argv = $_SERVER['argv'];
$mode = isset($argv[1]) ? $argv[1] : '';

function type_string($type) {
    if ($type === null) return null;
    return ($type->allowsNull() ? '?' : '') . (string) $type;
}

function describe_class($name) {
    $r = new ReflectionClass($name);
    $out = [
        'kind' => $r->isInterface() ? 'interface' : ($r->isTrait() ? 'trait' : 'class'),
        'abstract' => $r->isAbstract() && !$r->isInterface(),
        'final' => $r->isFinal(),
        'parent' => $r->getParentClass() ? $r->getParentClass()->getName() : null,
        'interfaces' => $r->getInterfaceNames(),
        'constants' => [], 'properties' => [], 'methods' => [],
    ];
    sort($out['interfaces']);
    foreach ($r->getReflectionConstants() as $c) {
        if ($c->isPrivate() || $c->getDeclaringClass()->getName() !== $name) continue;
        $out['constants'][$c->getName()] = ['visibility' => $c->isPublic() ? 'public' : 'protected', 'value' => var_export($c->getValue(), true)];
    }
    $defaults = $r->getDefaultProperties();
    foreach ($r->getProperties() as $p) {
        if ($p->isPrivate() || $p->getDeclaringClass()->getName() !== $name) continue;
        $out['properties'][$p->getName()] = [
            'visibility' => $p->isPublic() ? 'public' : 'protected', 'static' => $p->isStatic(),
            'default' => array_key_exists($p->getName(), $defaults) ? var_export($defaults[$p->getName()], true) : null,
        ];
    }
    foreach ($r->getMethods() as $m) {
        if ($m->isPrivate() || $m->getDeclaringClass()->getName() !== $name) continue;
        $params = [];
        foreach ($m->getParameters() as $p) {
            $default = null;
            if ($p->isDefaultValueAvailable()) {
                $default = $p->isDefaultValueConstant() ? 'const ' . $p->getDefaultValueConstantName() : var_export($p->getDefaultValue(), true);
            }
            $params[] = ['name' => $p->getName(), 'type' => type_string($p->getType()), 'optional' => $p->isOptional(),
                'default' => $default, 'byref' => $p->isPassedByReference(), 'variadic' => $p->isVariadic()];
        }
        $out['methods'][$m->getName()] = [
            'visibility' => $m->isPublic() ? 'public' : 'protected', 'static' => $m->isStatic(),
            'abstract' => $m->isAbstract(), 'final' => $m->isFinal(),
            'returns_reference' => $m->returnsReference(),
            'params' => $params, 'return' => type_string($m->getReturnType()),
        ];
    }
    ksort($out['constants']); ksort($out['properties']); ksort($out['methods']);
    return $out;
}

function register_autoload($root, $extra) {
    if ($extra) require_once $extra;
    spl_autoload_register(function ($class) use ($root) {
        if (strpos($class, 'flat\\') !== 0) return;
        $file = $root . '/' . str_replace('\\', '/', $class) . '.php';
        if (is_file($file)) require_once $file;
    });
}

if ($mode === 'one') { // internal: describe one class in this process
    $root = $argv[2]; $class = $argv[3]; $extra = isset($argv[4]) ? $argv[4] : null;
    register_autoload($root, $extra);
    $found = class_exists($class) || interface_exists($class) || trait_exists($class);
    echo $found ? json_encode(describe_class($class)) : json_encode(['missing' => true]);
    exit(0);
}

if ($mode === 'dump') {
    $root = rtrim($argv[2], '/'); $extra = null;
    foreach (array_slice($argv, 3) as $a) if (strpos($a, '--autoload=') === 0) $extra = substr($a, 11);
    $classes = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$root/flat", FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        $rel = substr($f->getPathname(), strlen($root) + 1);
        if (substr($rel, -4) !== '.php' || strpos("/$rel", '/vendor/') !== false) continue;
        $classes[] = str_replace('/', '\\', substr($rel, 0, -4));
    }
    sort($classes);
    $result = [];
    foreach ($classes as $class) {
        $errorFile = tempnam(sys_get_temp_dir(), 'apidump');
        $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' one ' . escapeshellarg($root) . ' ' . escapeshellarg($class) . ($extra ? ' ' . escapeshellarg($extra) : '') . ' 2>' . escapeshellarg($errorFile);
        $json = shell_exec($cmd);
        $data = json_decode((string) $json, true);
        if (!is_array($data)) {
            // keep what the class said when it failed to load, so the report can show why
            $said = array_filter(array_map('trim', array_merge(file($errorFile, FILE_IGNORE_NEW_LINES) ?: [], explode("\n", (string) $json))));
            $result[$class] = ['unloadable' => true, 'reason' => $said ? substr(reset($said), 0, 300) : 'no output'];
        }
        elseif (isset($data['missing'])) $result[$class] = ['nodeclaration' => true];
        else $result[$class] = $data;
        unlink($errorFile);
    }
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
    exit(0);
}

if ($mode === 'compare') {
    $old = json_decode(file_get_contents($argv[2]), true);
    $new = json_decode(file_get_contents($argv[3]), true);
    foreach ([$old, $new] as $dump) {
        foreach ($dump as $class) {
            foreach ($class['methods'] ?? [] as $method) {
                if (!array_key_exists('returns_reference', $method)) {
                    echo "# API comparison\n\nIncomplete: return-reference metadata is missing. Re-dump both versions with the current tool.\n";
                    exit(2);
                }
            }
        }
    }
    $lines = []; $counts = [];
    $note = function ($category, $text) use (&$lines, &$counts) { $lines[$category][] = $text; $counts[$category] = ($counts[$category] ?? 0) + 1; };
    $state = function ($c) { return isset($c['unloadable']) ? 'unloadable' : (isset($c['nodeclaration']) ? 'declares nothing' : 'ok'); };
    foreach ($old as $name => $o) {
        if (!isset($new[$name])) { $note('file removed', "`$name`"); continue; }
        $n = $new[$name];
        if ($state($o) !== $state($n)) { $note('load state changed', "`$name`: " . $state($o) . ' before, ' . $state($n) . ' now'); }
        if ($state($o) !== 'ok' || $state($n) !== 'ok') continue;
        foreach (['kind', 'abstract', 'final', 'parent'] as $k) {
            if ($o[$k] !== $n[$k]) $note('class declaration changed', "`$name`: $k " . json_encode($o[$k]) . ' to ' . json_encode($n[$k]));
        }
        foreach (array_diff($o['interfaces'], $n['interfaces']) as $i) $note('interface removed', "`$name` no longer implements `$i`");
        foreach (array_diff($n['interfaces'], $o['interfaces']) as $i) $note('interface added', "`$name` now implements `$i`");
        foreach ($o['constants'] as $c => $v) {
            if (!isset($n['constants'][$c])) $note('constant removed', "`$name::$c`");
            elseif ($n['constants'][$c] !== $v) $note('constant changed', "`$name::$c`: " . json_encode($v) . ' to ' . json_encode($n['constants'][$c]));
        }
        foreach (array_diff_key($n['constants'], $o['constants']) as $c => $v) $note('constant added', "`$name::$c`");
        foreach ($o['properties'] as $p => $v) {
            if (!isset($n['properties'][$p])) $note('property removed', "`$name::\$$p`");
            elseif ($n['properties'][$p] !== $v) $note('property changed', "`$name::\$$p`: " . json_encode($v) . ' to ' . json_encode($n['properties'][$p]));
        }
        foreach (array_diff_key($n['properties'], $o['properties']) as $p => $v) $note('property added', "`$name::\$$p`");
        foreach ($o['methods'] as $m => $v) {
            if (!isset($n['methods'][$m])) { $note('method removed', "`$name::$m()`"); continue; }
            $w = $n['methods'][$m];
            foreach (['visibility', 'static', 'abstract', 'final', 'returns_reference'] as $k) {
                $before = $v[$k]; $after = $w[$k];
                if ($before !== $after) $note('method modifier changed', "`$name::$m()`: $k " . json_encode($before) . ' to ' . json_encode($after));
            }
            if ($v['return'] !== $w['return']) $note('return type changed', "`$name::$m()`: " . json_encode($v['return']) . ' to ' . json_encode($w['return']));
            if (count($v['params']) !== count($w['params'])) {
                $note('parameter count changed', "`$name::$m()`: " . count($v['params']) . ' to ' . count($w['params']) . ' parameters');
            }
            foreach ($v['params'] as $i => $pv) {
                if (!isset($w['params'][$i])) continue;
                $pw = $w['params'][$i];
                $label = "`$name::$m()` parameter " . ($i + 1) . " (\${$pv['name']})";
                if ($pv['name'] !== $pw['name']) $note('parameter renamed', "$label is now \${$pw['name']}");
                foreach (['type', 'optional', 'default', 'byref', 'variadic'] as $k) {
                    if ($pv[$k] !== $pw[$k]) $note("parameter $k changed", "$label: " . json_encode($pv[$k]) . ' to ' . json_encode($pw[$k]));
                }
            }
        }
        foreach (array_diff_key($n['methods'], $o['methods']) as $m => $v) $note('method added', "`$name::$m()`");
    }
    foreach ($new as $name => $n) if (!isset($old[$name])) $note('file added', "`$name`");
    echo "# API comparison\n\n";
    printf("Classes, interfaces and traits compared: %d (old) and %d (new).\n\n", count($old), count($new));
    echo "## Counts\n\n";
    if (!$counts) echo "- no differences\n";
    ksort($counts);
    foreach ($counts as $cat => $c) echo "- $cat: $c\n";
    ksort($lines);
    foreach ($lines as $cat => $items) { echo "\n## $cat\n\n"; foreach ($items as $i) echo "- $i\n"; }
    exit(0);
}
fwrite(STDERR, "usage: php apidump.php dump <src-root> [--autoload=FILE] | compare old.json new.json\n");
exit(2);
