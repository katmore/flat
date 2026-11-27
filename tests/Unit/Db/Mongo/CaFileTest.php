<?php
namespace flat\test\Unit\Db\Mongo;
use PHPUnit\Framework\TestCase;
class CaLiteralFixture extends \flat\db\driver\mongo\ssl\cafile\base64
{
    protected function _get_cafile_base64() { return 'literal certificate content'; }
}
class CaPathFixture extends \flat\db\driver\mongo\ssl\cafile\file
{
    public static $path;
    protected function _get_cafile_path() { return self::$path; }
}
class CaFileTest extends TestCase
{
    use \flat\test\Support\PhpSubprocess;
    public function testFileAdapterCallsItsDeclaredPathHook()
    {
        $path = CaPathFixture::$path = tempnam(sys_get_temp_dir(),'flat-ca-path-');
        try {
            file_put_contents($path,'existing certificate'); $certificate = new CaPathFixture;
            $this->assertSame('existing certificate',$certificate->ca);
            $this->assertSame($path,$certificate->get_file());
            file_put_contents($path,''); $certificate = new CaPathFixture;
            $this->assertNull($certificate->get_file());
        } finally { unlink($path); CaPathFixture::$path = null; }
    }
    public function testLiteralContentUsesRequestedPathAndReusesWrittenFile()
    {
        $path = tempnam(sys_get_temp_dir(),'flat-ca-'); $other = $path.'-other';
        try {
            $certificate = new CaLiteralFixture;
            $this->assertSame($path,$certificate->get_file(['path'=>$path]));
            $this->assertSame('literal certificate content',file_get_contents($path));
            $this->assertSame($path,$certificate->get_file(['path'=>$path]));
            $this->assertSame($path,$certificate->get_file());
            $this->assertSame($other,$certificate->get_file(['path'=>$other]));
            $certificate->ca = '';
            $this->assertNull($certificate->get_file());
        } finally { unlink($path); if (file_exists($other)) unlink($other); }
    }
    public function testTemporaryCertificateIsRemovedAtNativePhpShutdown()
    {
        $body = '$c = new class extends \\flat\\db\\driver\\mongo\\ssl\\cafile {}; $c->ca="local certificate"; echo $c->get_file();';
        list($status,$out,$err) = $this->subprocess($body);
        $this->assertSame(0,$status); $this->assertSame('',$err);
        $this->assertStringContainsString('cafile',$out);
        $this->assertFileDoesNotExist($out);
    }
    public function testFailedFileWriteRaisesAnError()
    {
        $certificate = new CaLiteralFixture; $warnings = [];
        $previous = null;
        $previous = set_error_handler(function ($severity,$message,$file,$line) use (&$warnings,&$previous) {
            if (strpos($file,'/ssl/cafile.php') !== false && strpos($message,'file_put_contents') !== false) { $warnings[]=$message; return true; }
            return $previous ? $previous($severity,$message,$file,$line) : false;
        });
        try {
            $certificate->get_file(['path'=>sys_get_temp_dir()]);
            $this->fail('expected an error for an unwritable path');
        } catch (\flat\lib\exception\app_error $e) {
            $this->assertStringContainsString('could not write CA file: '.sys_get_temp_dir(), $e->getMessage());
        } finally { restore_error_handler(); }
        $this->assertCount(1,$warnings);
    }
    public function testEverySslClassLoadsByNameWithoutIncludingFiles()
    {
        // A clean process: nothing has been included by hand, so only the autoloader can find them.
        $body = '$names = ["flat\\\\db\\\\driver\\\\mongo\\\\ssl", "flat\\\\db\\\\driver\\\\mongo\\\\ssl\\\\cafile", "flat\\\\db\\\\driver\\\\mongo\\\\ssl\\\\cafile\\\\base64",'
            .' "flat\\\\db\\\\driver\\\\mongo\\\\ssl\\\\cafile\\\\file", "flat\\\\db\\\\driver\\\\mongo\\\\ssl\\\\localcert", "flat\\\\db\\\\driver\\\\mongo\\\\ssl\\\\localcert\\\\base64",'
            .' "flat\\\\db\\\\driver\\\\mongo\\\\ssl\\\\localcert\\\\file"]; $loaded = [];'
            .' foreach ($names as $name) $loaded[$name] = class_exists($name) || interface_exists($name);'
            .' echo json_encode([$loaded, class_exists("flat\\\\db\\\\mongo\\\\ssl\\\\cafile"), interface_exists("flat\\\\db\\\\mongo\\\\ssl"),'
            .' is_subclass_of("flat\\\\db\\\\driver\\\\mongo\\\\ssl\\\\localcert", "flat\\\\db\\\\driver\\\\mongo\\\\ssl\\\\cafile"),'
            .' is_subclass_of("flat\\\\db\\\\driver\\\\mongo\\\\ssl\\\\localcert\\\\file", "flat\\\\db\\\\driver\\\\mongo\\\\ssl\\\\cafile\\\\file")]);';
        list($status,$out,$err) = $this->subprocess($body);
        $this->assertSame(0,$status); $this->assertSame('',$err);
        list($loaded,$oldClass,$oldInterface,$certIsCafile,$localFileIsCaFile) = json_decode($out,true);
        $this->assertCount(7,$loaded);
        foreach ($loaded as $name => $ok) $this->assertTrue($ok,"$name must load by name");
        // The old, wrong names (KD-107) are gone.
        $this->assertFalse($oldClass); $this->assertFalse($oldInterface);
        $this->assertTrue($certIsCafile); $this->assertTrue($localFileIsCaFile);
    }
}
