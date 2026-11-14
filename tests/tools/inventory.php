<?php
/**
 * Builds the Markdown test inventory from PHPUnit's --list-tests-xml output.
 * Run through `bin/test inventory` (or `docker/bin/test inventory`); it
 * writes the result to stdout.
 *
 * Usage: php tests/tools/inventory.php <list-tests.xml> [<KNOWN-DEFECTS.md>]
 * With a known-defects file, tests in @group known-defect get their KD IDs.
 *
 * Test classes, methods, groups and data-provider cases come from PHPUnit.
 * File paths follow the PSR-4 mapping flat\test\ => tests/, and line numbers are
 * read from the test source text; no test code is loaded or executed.
 */
if ($argc < 2 || $argc > 3) {
    fwrite(STDERR, "usage: php inventory.php <list-tests.xml> [<KNOWN-DEFECTS.md>]\n");
    exit(2);
}
$listFile = $argv[1];
$defectsFile = isset($argv[2]) ? $argv[2] : null;
$root = dirname(__DIR__, 2);

$document = new DOMDocument();
if (!@$document->load($listFile)) {
    fwrite(STDERR, "cannot read PHPUnit test list: $listFile\n");
    exit(1);
}

// Known-defect IDs by test method name, from entries like "### KD-07 - Area" whose
// lines name the test as "Class::testName".
$defectIds = [];
$currentId = null;
foreach ($defectsFile ? file($defectsFile, FILE_IGNORE_NEW_LINES) : [] as $row) {
    if (preg_match('/^### (KD-\d+)\b/', $row, $id)) { $currentId = $id[1]; continue; }
    if (substr($row, 0, 1) === '#') { $currentId = null; continue; }
    if ($currentId === null) continue;
    preg_match_all('/::test(\w+)/', $row, $names);
    foreach ($names[1] as $name) $defectIds[$name][] = $currentId;
}

$files = [];
$methodCount = $caseCount = $providerCount = $defectCount = 0;
foreach ($document->getElementsByTagName('testCaseClass') as $class) {
    $className = $class->getAttribute('name');
    $relative = 'tests/' . str_replace('\\', '/', substr($className, strlen('flat\\test\\'))) . '.php';
    $source = is_file("$root/$relative") ? file("$root/$relative") : [];
    $methods = [];
    foreach ($class->getElementsByTagName('testCaseMethod') as $method) {
        $name = $method->getAttribute('name');
        if (!isset($methods[$name])) {
            $methods[$name] = ['cases' => 0, 'provider' => false, 'groups' => [], 'line' => ''];
            foreach ($source as $index => $text) {
                if (preg_match('/function\s+' . preg_quote($name, '/') . '\s*\(/', $text)) {
                    $methods[$name]['line'] = $index + 1;
                    break;
                }
            }
        }
        $methods[$name]['cases']++;
        if ($method->hasAttribute('dataSet')) $methods[$name]['provider'] = true;
        foreach (explode(',', $method->getAttribute('groups')) as $group) {
            if ($group !== '' && $group !== 'default') $methods[$name]['groups'][$group] = true;
        }
    }
    $rows = [];
    foreach ($methods as $name => $info) {
        $methodCount++;
        $caseCount += $info['cases'];
        $notes = [];
        if ($info['provider']) {
            $providerCount++;
            $notes[] = $info['cases'] . ' data-provider cases';
        }
        $groups = array_keys($info['groups']);
        $isDefect = in_array('known-defect', $groups, true);
        $groups = array_diff($groups, ['known-defect']);
        if ($groups) $notes[] = 'groups: ' . implode(', ', $groups);
        if ($isDefect) {
            $defectCount++;
            $short = substr($name, 4);
            if (!$defectsFile) $notes[] = '**known defect**';
            elseif (isset($defectIds[$short])) $notes[] = '**known defect ' . implode(', ', $defectIds[$short]) . '**';
            else $notes[] = '**known defect (no KD entry found)**';
        }
        $words = preg_replace('/(?<=[a-z0-9])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', ' ', substr($name, 4));
        $rows[] = "| {$info['line']} | `$name` | $words | " . implode('; ', $notes) . ' |';
    }
    $files[$relative] = $rows;
}
ksort($files);

echo "# Test inventory\n\n";
echo "Every test in the `unit` suite, grouped by file. **Generated; do not edit by hand.**\n";
echo "Regenerate with `bin/test inventory [KNOWN-DEFECTS.md] > TEST-INVENTORY.md` (or\n";
echo "`docker/bin/test inventory ...` to run it in Docker), which lists the suite with\n";
echo "PHPUnit's `--list-tests-xml` and converts it with\n";
echo "`tests/tools/inventory.php`.\n\n";
echo "- $methodCount test methods in " . count($files) . " files, running $caseCount test cases.\n";
echo "- $providerCount methods use a data provider (one case per data set).\n";
echo "- $defectCount methods still assert defective behavior (`@group known-defect`)"
    . ($defectsFile ? "; KD IDs are from `" . $defectsFile . "`" : '') . ".\n";
echo "- \"Reads as\" splits the method name into words; the name is the test's claim.\n";
echo "- Groups `services`, `mongo` and `rabbitmq` mark tests that need those containers.\n\n";
echo "## Files\n";
foreach ($files as $relative => $rows) {
    echo "\n### `$relative` (" . count($rows) . ")\n\n";
    echo "| Line | Method | Reads as | Notes |\n| --- | --- | --- | --- |\n";
    echo implode("\n", $rows), "\n";
}
