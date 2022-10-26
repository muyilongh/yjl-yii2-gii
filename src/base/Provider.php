<?php
/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 19.03.14
 * Time: 01:02.
 */
namespace Yjl\Gii\base;

use yii\base\BaseObject;

class Provider extends BaseObject
{
    /**
     * @var \Yjl\Gii\generators\crud\Generator
     */
    public $generator;

    public $columnNames = [];

    public $columnPatterns = [];
}
