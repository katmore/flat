<?php
namespace flat\db;

interface crud {
   public function create($data,array $option=NULL);
   
   public function update(array $param=NULL);
   
   public function delete(array $param);
   
   public function read(array $param=NULL);
}