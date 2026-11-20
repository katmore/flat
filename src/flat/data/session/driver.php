<?php
// Preserve the session driver's historical parent name using the existing base.
class_alias(\flat\data\orm\driver::class, 'flat\data\session\driver');
