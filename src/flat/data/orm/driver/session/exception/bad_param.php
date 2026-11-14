<?php
namespace flat\data\orm\driver\session\exception;
class bad_param extends \flat\data\orm\driver\session\exception
{
    public function __construct($reason) { parent::__construct('bad param: '.$reason); }
}
