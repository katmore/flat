<?php
// Stands in for php-amqplib 2's AMQPConnection class, which php-amqplib 3 removed. It is declared under its real name so that
// flat's RabbitMQ driver finds it where it looks for the legacy class. Required by RabbitMqTest in a fresh process.
namespace PhpAmqpLib\Connection;
class AMQPConnection extends AMQPStreamConnection {}
