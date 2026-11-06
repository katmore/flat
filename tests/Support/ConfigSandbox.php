<?php
namespace flat\test\Support;
trait ConfigSandbox
{
    private $configSaved = [];
    private $configDirectory;
    protected function configSandboxDirectory() { return $this->configDirectory; }
    protected function openConfigSandbox(array $values)
    {
        foreach ((new \ReflectionClass(\flat\core\config::class))->getProperties(\ReflectionProperty::IS_STATIC) as $property) {
            $property->setAccessible(true); $this->configSaved[$property->getName()] = $property->getValue();
            $property->setValue(null, $property->getName() === 'base_dir' ? null : []);
        }
        $this->configDirectory = sys_get_temp_dir().'/flat-config-fixture-'.bin2hex(random_bytes(6)); mkdir($this->configDirectory);
        mkdir($this->configDirectory.'/services');
        foreach ($values as $namespace=>$fields) {
            file_put_contents($this->configDirectory.'/services/'.$namespace.'.php', '<?php return '.var_export($fields,true).';');
        }
        \flat\core\config::set_base_dir($this->configDirectory);
    }
    protected function closeConfigSandbox()
    {
        foreach (glob($this->configDirectory.'/services/*.php') as $file) unlink($file);
        rmdir($this->configDirectory.'/services'); rmdir($this->configDirectory);
        foreach ($this->configSaved as $name=>$value) {
            $property = new \ReflectionProperty(\flat\core\config::class, $name); $property->setAccessible(true); $property->setValue(null, $value);
        }
    }
}
