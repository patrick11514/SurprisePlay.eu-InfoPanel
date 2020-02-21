<?php

namespace patrick115\Interfaces;

interface Main
{

    public static function Start($directory);

    public static function Environment();

    public static function Create($cls, array $params);

    public static function getApp($cls);

    public function Run();

    public static function getWorkDirectory();

    public static function getConfigDirectory();

    public static function getTemplateDirectory();
}