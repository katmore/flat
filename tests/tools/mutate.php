<?php
/**
 * Mutation probe: plants one small bug at a time in the source and checks that
 * the unit suite notices. A mutant that the suite does not notice (it
 * "survives") points at behavior the tests do not actually pin down, or at an
 * equivalent change that cannot be observed; each survivor needs a human or
 * agent to tell the two apart.
 *
 * Run through `docker/bin/mutate`, which works on a throwaway COPY of the
 * repository. This script rewrites source files in the current directory, so it
 * refuses to run unless the directory holds the `.mutation-copy` marker that the
 * wrapper creates.
 *
 * Usage (inside the copy):
 *   php tests/tools/mutate.php [--limit=N] [--seed=N] [--timeout=SECONDS] [--list]
 *                              [--with-services] [--at=FILE:LINE ...] [path...]
 *
 * --with-services also runs the MongoDB/RabbitMQ tests (the services must be
 * reachable; docker/bin/mutate --services starts them). Without it those tests
 * are excluded, so mutants in code only they cover wrongly survive.
 * --at=FILE:LINE (repeatable) re-tests only the mutants on that line.
 *
 * Paths are files or folders under the copy (default: src/flat). Third-party
 * code in `vendor/` folders is skipped. When there are more mutants than
 * --limit, a seeded random sample is run (same seed, same sample).
 * Needs FLAT_TEST_PHPUNIT (default vendor/bin/phpunit) and optionally
 * FLAT_TEST_BOOTSTRAP, like bin/test. Writes a Markdown report to stdout and
 * progress to stderr. PHP 7.2 compatible.
 */
$root = getcwd();
if (!is_file("$root/.mutation-copy")) {
    fwrite(STDERR, "Refusing to run: $root is not a mutation copy (no .mutation-copy marker).\n"
        . "Use docker/bin/mutate, which mutates a throwaway copy of the repository.\n");
    exit(2);
}

$limit = 150; $limitGiven = false; $seed = 1; $timeout = 60; $listOnly = false; $withServices = false; $at = []; $paths = [];
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--limit=(\d+)$/', $arg, $m)) { $limit = (int) $m[1]; $limitGiven = true; }
    elseif ($arg === '--with-services') $withServices = true;
    elseif (preg_match('/^--at=(.+):(\d+)$/', $arg, $m)) $at[] = [$m[1], (int) $m[2]];
    elseif (preg_match('/^--seed=(\d+)$/', $arg, $m)) $seed = (int) $m[1];
    elseif (preg_match('/^--timeout=(\d+)$/', $arg, $m)) $timeout = (int) $m[1];
    elseif ($arg === '--list') $listOnly = true;
    elseif ($arg !== '' && $arg[0] === '-') {
        fwrite(STDERR, "usage: php tests/tools/mutate.php [--limit=N] [--seed=N] [--timeout=SECONDS] [--list] [--with-services] [--at=FILE:LINE ...] [path...]\n");
        exit(2);
    } else $paths[] = $arg;
}
if (!$paths) $paths = ['src/flat'];

/** Source files under the given paths, relative to the copy, without vendor code. */
function source_files($root, array $paths) {
    $files = [];
    foreach ($paths as $path) {
        $full = "$root/" . rtrim($path, '/');
        if (is_file($full)) { $files[] = rtrim($path, '/'); continue; }
        if (!is_dir($full)) { fwrite(STDERR, "no such path: $path\n"); exit(2); }
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($full, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            $relative = substr($file->getPathname(), strlen($root) + 1);
            if ($file->isFile() && substr($relative, -4) === '.php' && strpos("/$relative", '/vendor/') === false) {
                $files[] = $relative;
            }
        }
    }
    sort($files);
    return array_values(array_unique($files));
}

/**
 * Candidate mutations of one file: [token index, line, description, replacement].
 * Comparison, logic, arithmetic and boolean swaps, negation removal and
 * 0/1 literal swaps; comments and strings are never touched.
 */
function find_mutations($code) {
    $tokens = token_get_all($code);
    $swap = ['>' => '>=', '>=' => '>', '<' => '<=', '<=' => '<', '==' => '!=', '!=' => '==', '<>' => '==',
             '===' => '!==', '!==' => '===', '&&' => '||', '||' => '&&', '++' => '--', '--' => '++'];
    $mutations = []; $line = 1; $previous = null;
    foreach ($tokens as $i => $token) {
        $id = is_array($token) ? $token[0] : null;
        $text = is_array($token) ? $token[1] : $token;
        $replacement = null;
        if (isset($swap[$text]) && ($id !== null || $text === '>' || $text === '<')) {
            $replacement = $swap[$text];
        } elseif (($text === '+' || $text === '-') && $id === null) {
            // Binary only: unary signs follow an operator, not an operand.
            $operand = $previous !== null && (in_array($previous[0], [T_VARIABLE, T_LNUMBER, T_DNUMBER, T_STRING, T_CONSTANT_ENCAPSED_STRING], true)
                || in_array($previous[1], [')', ']'], true));
            if ($operand) $replacement = $text === '+' ? '-' : '+';
        } elseif ($text === '!' && $id === null) {
            $replacement = '';
        } elseif ($id === T_STRING && in_array(strtolower($text), ['true', 'false'], true)
            && !($previous !== null && in_array($previous[0], [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NS_SEPARATOR, T_CONST, T_FUNCTION, T_NEW], true))) {
            $replacement = strtolower($text) === 'true' ? 'false' : 'true';
        } elseif ($id === T_LNUMBER && ($text === '0' || $text === '1')) {
            $replacement = $text === '0' ? '1' : '0';
        }
        if ($replacement !== null) {
            $mutations[] = ['index' => $i, 'line' => $line, 'from' => $text, 'to' => $replacement];
        }
        $line += substr_count($text, "\n");
        if ($id !== T_WHITESPACE && $id !== T_COMMENT && $id !== T_DOC_COMMENT) $previous = [$id, $text];
    }
    return [$tokens, $mutations];
}

function render(array $tokens, $index, $replacement) {
    $out = '';
    foreach ($tokens as $i => $token) {
        $out .= $i === $index ? $replacement : (is_array($token) ? $token[1] : $token);
    }
    return $out;
}

/** Runs the unit suite; returns the process exit status (124 = timed out). */
function run_suite($timeout, $withServices = false) {
    $phpunit = getenv('FLAT_TEST_PHPUNIT') ?: 'vendor/bin/phpunit';
    $bootstrap = getenv('FLAT_TEST_BOOTSTRAP');
    $command = 'timeout -k 5 ' . (int) $timeout . ' ' . escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($phpunit)
        . ($bootstrap ? ' --bootstrap ' . escapeshellarg($bootstrap) : '')
        . ' --testsuite unit' . ($withServices ? '' : ' --exclude-group services')
        . ' --stop-on-failure --stop-on-error --no-coverage >/dev/null 2>&1';
    exec($command, $unused, $status);
    return $status;
}

$files = source_files($root, $paths);
$all = []; $perFile = [];
foreach ($files as $file) {
    list(, $mutations) = find_mutations(file_get_contents("$root/$file"));
    $perFile[$file] = count($mutations);
    foreach ($mutations as $mutation) $all[] = $mutation + ['file' => $file];
}
if ($at) {
    $all = array_values(array_filter($all, function ($m) use ($at) {
        foreach ($at as $target) {
            if ($m['line'] === $target[1] && ($m['file'] === $target[0] || substr($m['file'], -strlen($target[0])) === $target[0])) return true;
        }
        return false;
    }));
    if (!$limitGiven) $limit = count($all);
}
if ($listOnly) {
    $perFile = [];
    foreach ($all as $m) $perFile[$m['file']] = ($perFile[$m['file']] ?? 0) + 1;
    foreach ($perFile as $file => $count) printf("%5d  %s\n", $count, $file);
    printf("%5d  total mutants in %d files\n", count($all), count($files));
    exit(0);
}

fwrite(STDERR, "baseline run...\n");
if (run_suite($timeout * 4, $withServices) !== 0) {
    fwrite(STDERR, "The unmutated suite does not pass; fix that before probing.\n");
    exit(1);
}
mt_srand($seed);
shuffle($all);
$total = count($all);
$sample = array_slice($all, 0, $limit);

$killed = $timedOut = $invalid = 0; $survivors = [];
foreach ($sample as $n => $m) {
    $path = "$root/{$m['file']}";
    $original = file_get_contents($path);
    list($tokens) = find_mutations($original);
    $mutant = render($tokens, $m['index'], $m['to']);
    file_put_contents($path, $mutant);
    $label = sprintf('%s:%d  %s  ->  %s', $m['file'], $m['line'], $m['from'], $m['to'] === '' ? '(removed)' : $m['to']);
    try {
        exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($path) . ' >/dev/null 2>&1', $unused, $lint);
        if ($lint !== 0) { $result = 'invalid'; $invalid++; }
        else {
            $status = run_suite($timeout, $withServices);
            if ($status === 0) { $result = 'SURVIVED'; $survivors[] = [$m, trim(explode("\n", $original)[$m['line'] - 1])]; }
            elseif ($status === 124 || $status === 137) { $result = 'timeout'; $timedOut++; }
            else { $result = 'killed'; $killed++; }
        }
    } finally {
        file_put_contents($path, $original);
    }
    fwrite(STDERR, sprintf("[%d/%d] %-8s %s\n", $n + 1, count($sample), $result, $label));
}

$run = count($sample) - $invalid;
echo "# Mutation probe\n\n";
printf("- Source files: %d; candidate mutants: %d; run: %d (seed %d, limit %d%s); invalid (did not compile): %d.\n",
    count($files), $total, count($sample), $seed, $limit, $withServices ? ', with services' : '', $invalid);
printf("- Killed: %d; timed out (counted as killed): %d; **survived: %d** of %d valid mutants",
    $killed, $timedOut, count($survivors), $run);
if ($run) printf(" (%.1f%% caught).\n", 100 * ($killed + $timedOut) / $run); else echo ".\n";
if ($survivors) {
    echo "\n## Survivors\n\nEach needs triage: a missing assertion (add a test) or an equivalent change (no observable effect).\n\n";
    echo "| File | Line | Mutation | Source line |\n| --- | --- | --- | --- |\n";
    foreach ($survivors as $s) {
        list($m, $src) = $s;
        printf("| `%s` | %d | `%s` to `%s` | `%s` |\n", $m['file'], $m['line'], $m['from'],
            $m['to'] === '' ? '(removed)' : $m['to'], str_replace('|', '\\|', substr($src, 0, 110)));
    }
}
exit(0);
