<?php
/**
 * @link      http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */

namespace schmunk42\giiant\generators\crud;

use Yii;
use yii\db\BaseActiveRecord;
use yii\gii\CodeFile;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use schmunk42\giiant\helpers\SaveForm;
use yii\web\Controller;
use yii\db\ActiveRecord;
use yii\db\Schema;


/**
 * This generator generates an extended version of CRUDs.
 *
 * @author Tobais Munk <schmunk@usrbin.de>
 *
 * @since 1.0
 */
class Generator extends \yii\gii\generators\crud\Generator
{
    use ParamTrait, ModelTrait, ProviderTrait;

    public $listFields;
    public $formFields;
    public $inputType;

    public $modelClass;
    public $moduleID;
    public $controllerClass;
    public $baseControllerClass = 'yii\web\Controller';
    public $indexWidgetType = 'grid';
    public $searchModelClass = '';


    /**
     * @var null comma separated list of provider classes 提供程序类的逗号分隔列表
     */
    public $providerList = null;
    /**
     * @todo review
     *
     * @var string
     */
    public $actionButtonClass = 'yii\grid\ActionColumn';
    /**
     * @var array relations to be excluded in UI rendering 在UI呈现中要排除的关系
     */
    public $skipRelations = [];
    /**
     * @var string default view path
     */
    public $viewPath = '@backend/views';

    /**
     * @var string table prefix to be removed from class names when auto-detecting model names, eg. `app_` converts table `app_foo` into `Foo` 当自动检测模型名称时，表前缀将从类名称中删除。' app_ '将表' app_foo '转换为' Foo '
     */
    public $tablePrefix = null;

    /**
     * @var string prefix for controller route, eg. when generating controllers into subfolders 控制器路由的前缀。当生成控制器到子文件夹
     */
    public $pathPrefix = null;

    /**
     * @var string Bootstrap CSS-class for form-layout Bootstrap css类进行表单布局
     */
    public $formLayout = 'horizontal';

    /**
     * @var string translation catalogue 翻译目录
     */
    public $messageCategory = 'cruds';

    /**
     * @var string translation catalogue for model related translations 模型相关翻译的翻译目录
     */
    public $modelMessageCategory = 'models';

    /**
     * @var int maximum number of columns to show in grid 在网格中显示的最大列数
     */
    public $gridMaxColumns = 8;

    /**
     * @var int maximum number of columns to show in grid 在网格中显示的最大列数
     */
    public $gridRelationMaxColumns = 8;

    /**
     * @var array array of composer packages (only to show information to the developer in the web UI)
     */
    public $requires = [];

    /**
     * @var bool whether to convert controller name to singular 是否将控制器名称转换为单一的实体
     */
    public $singularEntities = false;

    /**
     * @var bool whether to add an access filter to controllers 是否为控制器添加访问过滤器
     */
    public $accessFilter = false;
    //生成访问过滤器迁移
    public $generateAccessFilterMigrations = false;

    public $baseTraits;

    /**
     * @var string controller base namespace
     */
    public $controllerNs;

    /**
     * @var bool whether to overwrite extended controller classes
     */
    public $overwriteControllerClass = false;

    /**
     * @var bool whether to overwrite rest/api controller classes
     */
    public $overwriteRestControllerClass = false;

    /**
     * @var bool whether to overwrite search classes
     */
    public $overwriteSearchModelClass = false;

    /**
     * @var bool whether to use phptidy on renderer files before saving
     */
    public $tidyOutput = false;

    /**
     * @var string command-line options for phptidy command
     */
    public $tidyOptions = '';

    /**
     * @var bool whether to use php-cs-fixer to generate PSR compatible output
     */
    public $fixOutput = false;

    /**
     * @var string command-line options for php-cs-fixer command
     */
    public $fixOptions = '';

    /**
     * @var string form field for selecting and loading saved gii forms
     */
    public $savedForm;

    public $moduleNs;

    public $migrationClass;

    public $indexGridClass = 'yii\\grid\\GridView';

    /**
     * @var string position of action column in gridview 'left' or 'right'
     */
    public $actionButtonColumnPosition = 'left';

    public $giiInfoPath = '.gii';

    private $_p = [];

    public function fieldTypes()
    {
        return [
            'text'         => "text",
            'textarea'     => "textarea",
            'password'     => "password",
            'time'         => "time",
            'timeStamp'         => "time stamp",
            'date'         => "date",
            'datetime'     => "datetime",
            'dropDownList' => "dropDownList",
            'radioList'    => "radioList",
            'checkbox'     => "checkbox",
            'checkboxList' => "checkboxList",
            'select2'      => 'Select2',
            'file'         => 'upload file',
            'image'        => 'upload image',
            'uid'          => 'login user id',

            // 'multipleInput' => "Input组",
            // 'baiduUEditor'    => "百度编辑器",
            // 'image'           => "图片上传",
            // 'images'          => "多图上传",
            // 'file'            => "文件上传",
            // 'files'           => "多文件上传",
            // 'cropper'         => "图片裁剪上传",
            // 'latLngSelection' => "经纬度选择",
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->providerList = self::getCoreProviders();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Giiant CRUD';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'This generator generates an extended version of CRUDs.该生成器生成crud的扩展版本。';
    }

    /**
     * {@inheritdoc}
     */
    public function successMessage()
    {
        $return = 'The code has been generated successfully. Please require the following packages with composer:代码已经成功生成。请要求以下软件包与作曲家:';
        $return .= '<br/><code>' . implode('<br/>', $this->requires) . '</code>';

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function hints()
    {
        return array_merge(
            parent::hints(),
            [
                'providerList' => 'Choose the providers to be used.',
                'viewPath' => 'Output path for view files, eg. <code>@backend/views/crud</code>.',
                'pathPrefix' => 'Customized route/subfolder for controllers and views eg. <code>crud/</code>. <b>Note!</b> Should correspond to <code>viewPath</code>.',
                'modelMessageCategory' => 'Model message categry.',
            ],
            [
                'modelClass' => 'This is the ActiveRecord class associated with the table that CRUD will be built upon.
                You should provide a fully qualified class name, e.g., <code>app\models\Post</code>.',
                'controllerClass' => 'This is the name of the controller class to be generated. You should
                provide a fully qualified namespaced class, .e.g, <code>app\controllers\PostController</code>.
                The controller class name should follow the CamelCase scheme with an uppercase first letter',
                'baseControllerClass' => 'This is the class that the new CRUD controller class will extend from.
                You should provide a fully qualified class name, e.g., <code>yii\web\Controller</code>.',
                'moduleID' => 'This is the ID of the module that the generated controller will belong to.
                If not set, it means the controller will belong to the application.',
                'indexWidgetType' => 'This is the widget type to be used in the index page to display list of the models.
                You may choose either <code>GridView</code> or <code>ListView</code>',
                'searchModelClass' => 'This is the name of the search model class to be generated. You should provide a fully
                qualified namespaced class name, e.g., <code>app\models\PostSearch</code>.',
            ],
            SaveForm::hint()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(
            parent::rules(), [
                [
                    [
                        'providerList',
                        'actionButtonClass',
                        'viewPath',
                        'pathPrefix',
                        'savedForm',
                        'formLayout',
                        'accessFilter',
                        'generateAccessFilterMigrations',
                        'singularEntities',
                        'modelMessageCategory',
                    ],
                    'safe',
                ],
                [['viewPath'], 'required'],
            ],
            [
                [['moduleID', 'controllerClass', 'modelClass', 'searchModelClass', 'baseControllerClass'], 'filter', 'filter' => 'trim'],
                [['modelClass', 'controllerClass', 'baseControllerClass', 'indexWidgetType'], 'required'],
                [['searchModelClass'], 'compare', 'compareAttribute' => 'modelClass', 'operator' => '!==', 'message' => 'Search Model Class must not be equal to Model Class.'],
                [['modelClass', 'controllerClass', 'baseControllerClass', 'searchModelClass'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
                [['modelClass'], 'validateClass', 'params' => ['extends' => BaseActiveRecord::className()]],
                [['baseControllerClass'], 'validateClass', 'params' => ['extends' => Controller::className()]],
                [['controllerClass'], 'match', 'pattern' => '/Controller$/', 'message' => 'Controller class name must be suffixed with "Controller".'],
                [['controllerClass'], 'match', 'pattern' => '/(^|\\\\)[A-Z][^\\\\]+Controller$/', 'message' => 'Controller class name must start with an uppercase letter.'],
                [['controllerClass', 'searchModelClass'], 'validateNewClass'],
                [['indexWidgetType'], 'in', 'range' => ['grid', 'list']],
                [['modelClass'], 'validateModelClass'],
                [['moduleID'], 'validateModuleID'],
                [['enableI18N'], 'boolean'],
                [['messageCategory'], 'validateMessageCategory', 'skipOnEmpty' => false],
                [['listFields', 'formFields', 'inputType'], 'safe'],
            ]
        );
    }

    /**
     * Checks if model class is valid
     */
    public function validateModelClass()
    {
        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        $pk = $class::primaryKey();
        if (empty($pk)) {
            $this->addError('modelClass', "The table associated with $class must have primary key(s).");
        }
    }
    /**
     * Generates code for active field
     * @param string $attribute
     * @return string
     */

    public function generateActiveField($attribute)
    {
        $model           = new $this->modelClass();
        $attributeLabels = $model->attributeLabels();
        $tableSchema     = $this->getTableSchema();
        if ($tableSchema === false || !isset($tableSchema->columns[$attribute])) {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $attribute)) {
                return "'$attribute' => ['type' => TabularForm::INPUT_PASSWORD,'options' => ['placeholder' => 'Enter " . $attributeLabels[$attribute] . "...']],";
                //return "\$form->field(\$model, '$attribute')->passwordInput()";
            } else {
                return "'$attribute' => ['type' => TabularForm::INPUT_TEXT, 'options' => ['placeholder' => 'Enter " . $attributeLabels[$attribute] . "...']],";
                //return "\$form->field(\$model, '$attribute')";
            }
        }
        $column = $tableSchema->columns[$attribute];
        $type   = $this->inputType[$attribute];
        if ($type === 'radioList') {
            //return "\$form->field(\$model, '$attribute')->checkbox()";
            return "'$attribute' => [
                'type'    => Form::INPUT_RADIO_LIST,
                'items'   => [true => 'Active', false => 'Inactive', 'ok'=> 'radioList'],
                'options' => ['inline' => true],
        ],";

        } elseif ($type === 'checkbox') {
            return "'$attribute' => [
            'type' => Form::INPUT_CHECKBOX,
            'label' => 'Remember your settings?',
             ],";
        } elseif ($type === 'checkboxList') {
            return "'$attribute' => ['type' => Form::INPUT_CHECKBOX_LIST,
                 'items'=>[  'value1' => 'v1',   'value2' => 'v2'],
                'options'=>['text' => 'Please select', 'options' => ['value' => 'none', 'class' => 'prompt', 'label' => 'Select']],
             ],";
        } elseif ($type === 'dropDownList') {
            return "'$attribute' => ['type' => Form::INPUT_DROPDOWN_LIST,
                'items'=>[  'value1' => 'value 1',   'value2' =>'value 2'],
                'options'=>['text' => 'Please select', 'options' => ['value' => 'none', 'class' => 'prompt', 'label' => 'Select']],
            ],";
        } elseif ($type === 'text') {
            return "'$attribute' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => 'Enter " . $attributeLabels[$attribute] . "...','rows' => 6]],";
        } elseif ($type === 'textarea') {
            //return "\$form->field(\$model, '$attribute')->textarea(['rows' => 6])";
            return "'$attribute' => ['type' => Form::INPUT_TEXTAREA, 'options' => ['placeholder' => 'Enter " . $attributeLabels[$attribute] . "...','rows' => 6]],";
        } elseif ($type === 'date') {
            return "'$attribute' => ['type' => Form::INPUT_WIDGET, 'widgetClass' => DateControl::classname(),'options' => ['type' => DateControl::FORMAT_DATE]],";
        } elseif ($type === 'time') {
            return "'$attribute' => ['type' => Form::INPUT_WIDGET, 'widgetClass' => DateControl::classname(),'options' => ['type' => DateControl::FORMAT_TIME]],";
        } elseif ($type === 'datetime' || $type === 'timestamp') {
            return "'$attribute' => ['type' => Form::INPUT_WIDGET, 'widgetClass' => DateControl::classname(),'options' => ['type' => DateControl::FORMAT_DATETIME]],";

        } elseif ($type === 'select2') {
            return "'$attribute' => [
                'type'        => Form::INPUT_WIDGET,
                'widgetClass' => '\kartik\select2\Select2',
                'options'     => [
                    'data'          => [true => 'Active', false => 'Inactive', 'ok' => 'radioList'],
                    'pluginOptions' => [
                        'allowClear'      => true,
                        'closeOnSelect'   => false,
                        'tags'            => true,
                        'multiple'        => true,
                        'tokenSeparators' => [',', ' '],
                        // 'placeholder'     => 'Select  ...',
                        'hint'            => 'Type and select state',
                    ],
                ],
            ],";
        } elseif ($type === 'file') {
            return "'$attribute' => ['type' => Form::INPUT_WIDGET, 'widgetClass' => FileInput::classname(),
                'options'            => [
                    'options'       => ['accept' => 'image/*', 'multiple' => true],
                    'pluginOptions' => [
                        // 'allowedFileExtensions' => ['csv'],
                        'showUpload'  => true,
                        'browseLabel' => 'upload file',
                        'removeLabel' => '',
                        'showPreview' => true,
                    ],
                ],
            ],";
        } elseif ($type === 'image') {
            return "'$attribute' => [
                'type' => Form::INPUT_WIDGET,
                'widgetClass' => FileInput::classname(),
                'name' => 'attachment_3',
                'attribute' => 'attachment_1[]',
                'options'            => [
                'options'       => ['accept' => 'image/*', 'multiple' => true],
                'pluginOptions' => [
                    // 'allowedFileExtensions' => ['csv'],
                    'showUpload'  => true,
                    'browseLabel' => 'upload image',
                    'removeLabel' => '',
                    'showPreview' => true,
                ],
            ],
            ],";
        } elseif ($type === 'password') {
            return "'$attribute' => [
                'type' => Form::INPUT_PASSWORD,
                'options' => ['placeholder' => 'Enter Password...']
            ],";
        }elseif ($type === 'uid') {
            return "'$attribute' => ['type' => Form::INPUT_TEXT,'options' => ['placeholder' => '','value'=>Yii::\$app->user->id]],";
        }elseif ($type === 'timeStamp') {
            return "'$attribute' => ['type' => Form::INPUT_TEXT,'options' => ['placeholder' => '','value'=>time()]],";
        }  else {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $column->name)) {
                $input = 'INPUT_PASSWORD';
            } else {
                $input = 'INPUT_TEXT';
            }
            if ($column->phpType !== 'string' || $column->size === null) {
                //return "\$form->field(\$model, '$attribute')->$input()";
                return "'$attribute' => ['type' => Form::" . $input . ", 'options' => ['placeholder' => 'Enter " . $attributeLabels[$attribute] . "...']],";
            } else {
                //return "\$form->field(\$model, '$attribute')->$input(['maxlength' => $column->size])";
                return "'$attribute' => ['type' => Form::" . $input . ", 'options' => ['placeholder' => 'Enter " . $attributeLabels[$attribute] . "...', 'maxlength' => " . $column->size . "]],";
            }
        }
    }
    /**
     * Generates code for active search field
     * @param string $attribute
     * @return string
     */
    public function generateActiveSearchField($attribute)
    {
        $tableSchema = $this->getTableSchema();
        if ($tableSchema === false) {
            return "\$form->field(\$model, '$attribute')";
        }
        $column = $tableSchema->columns[$attribute];
        if ($column->phpType === 'boolean') {
            return "\$form->field(\$model, '$attribute')->checkbox()";
        } else {
            return "\$form->field(\$model, '$attribute')";
        }
    }

    /**
     * Generates column format
     * @param \yii\db\ColumnSchema $column
     * @return string
     */
    public function generateColumnFormat($column)
    {
        if ($column->phpType === 'tinyint') {
            return 'boolean';
        } elseif ($column->type === 'text') {
            return 'ntext';
        } elseif (stripos($column->name, 'time') !== false && $column->phpType === 'integer') {
            return 'datetime';
        } elseif (stripos($column->name, 'email') !== false) {
            return 'email';
        } elseif (stripos($column->name, 'url') !== false) {
            return 'url';
        } else {
            return 'text';
        }
    }

    /**
     * Generates validation rules for the search model.
     * @return array the generated validation rules
     */
    public function generateSearchRules()
    {
        if (($table = $this->getTableSchema()) === false) {
            return ["[['" . implode("', '", $this->getColumnNames()) . "'], 'safe']"];
        }
        $types = [];
        foreach ($table->columns as $column) {
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    $types['integer'][] = $column->name;
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                default:
                    $types['safe'][] = $column->name;
                    break;
            }
        }

        $rules = [];
        foreach ($types as $type => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }

        return $rules;
    }
    /**
     * @return array searchable attributes
     */
    public function getSearchAttributes()
    {
        return $this->getColumnNames();
    }

    /**
     * Generates the attribute labels for the search model.
     * @return array the generated attribute labels (name => label)
     */
    public function generateSearchLabels()
    {
        /** @var \yii\base\Model $model */
        $model           = new $this->modelClass();
        $attributeLabels = $model->attributeLabels();
        $labels          = [];
        foreach ($this->getColumnNames() as $name) {
            if (isset($attributeLabels[$name])) {
                $labels[$name] = $attributeLabels[$name];
            } else {
                if (!strcasecmp($name, 'id')) {
                    $labels[$name] = 'ID';
                } else {
                    $label = Inflector::camel2words($name);
                    if (strcasecmp(substr($label, -3), ' id') === 0) {
                        $label = substr($label, 0, -3) . ' ID';
                    }
                    $labels[$name] = $label;
                }
            }
        }

        return $labels;
    }

    /**
     * Generates search conditions
     * @return array
     */
    public function generateSearchConditions()
    {
        $columns = [];
        if (($table = $this->getTableSchema()) === false) {
            $class = $this->modelClass;
            /** @var \yii\base\Model $model */
            $model = new $class();
            foreach ($model->attributes() as $attribute) {
                $columns[$attribute] = 'unknown';
            }
        } else {
            foreach ($table->columns as $column) {
                $columns[$column->name] = $column->type;
            }
        }

        $likeConditions = [];
        $hashConditions = [];
        foreach ($columns as $column => $type) {
            switch ($type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_BOOLEAN:
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    $hashConditions[] = "'{$column}' => \$this->{$column},";
                    break;
                default:
                    $likeConditions[] = "->andFilterWhere(['like', '{$column}', \$this->{$column}])";
                    break;
            }
        }

        $conditions = [];
        if (!empty($hashConditions)) {
            $conditions[] = "\$query->andFilterWhere([\n"
                . str_repeat(' ', 12) . implode("\n" . str_repeat(' ', 12), $hashConditions)
                . "\n" . str_repeat(' ', 8) . "]);\n";
        }
        if (!empty($likeConditions)) {
            $conditions[] = "\$query" . implode("\n" . str_repeat(' ', 12), $likeConditions) . ";\n";
        }

        return $conditions;
    }

    /**
     * Generates URL parameters
     * @return string
     */
    public function generateUrlParams()
    {
        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        $pks   = $class::primaryKey();
        if (count($pks) === 1) {
            if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                return "'id' => (string)\$model->{$pks[0]}";
            } else {
                return "'id' => \$model->{$pks[0]}";
            }
        } else {
            $params = [];
            foreach ($pks as $pk) {
                if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                    $params[] = "'$pk' => (string)\$model->$pk";
                } else {
                    $params[] = "'$pk' => \$model->$pk";
                }
            }

            return implode(', ', $params);
        }
    }

    /**
     * Generates action parameters
     * @return string
     */
    public function generateActionParams()
    {
        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        $pks   = $class::primaryKey();
        if (count($pks) === 1) {
            return '$id';
        } else {
            return '$' . implode(', $', $pks);
        }
    }

    /**
     * Generates parameter tags for phpdoc
     * @return array parameter tags for phpdoc
     */
    public function generateActionParamComments()
    {
        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        $pks   = $class::primaryKey();
        if (($table = $this->getTableSchema()) === false) {
            $params = [];
            foreach ($pks as $pk) {
                $params[] = '@param ' . (substr(strtolower($pk), -2) == 'id' ? 'integer' : 'string') . ' $' . $pk;
            }

            return $params;
        }
        if (count($pks) === 1) {
            return ['@param ' . $table->columns[$pks[0]]->phpType . ' $id'];
        } else {
            $params = [];
            foreach ($pks as $pk) {
                $params[] = '@param ' . $table->columns[$pk]->phpType . ' $' . $pk;
            }

            return $params;
        }
    }

    /**
     * Returns table schema for current model class or false if it is not an active record
     * @return boolean|\yii\db\TableSchema
     */
    public function getTableSchema()
    {
        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema();
        } else {
            return false;
        }
    }

    /**
     * @return array model column names
     */
    public function getColumnNames()
    {
        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema()->getColumnNames();
        } else {
            /** @var \yii\base\Model $model */
            $model = new $class();

            return $model->attributes();
        }
    }

    /**
     * Checks if model ID is valid
     */
    public function validateModuleID()
    {
        if (!empty($this->moduleID)) {
            $module = Yii::$app->getModule($this->moduleID);
            if ($module === null) {
                $this->addError('moduleID', "Module '{$this->moduleID}' does not exist.");
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'modelClass' => 'Model Class',
            'moduleID' => 'Module ID',
            'controllerClass' => 'Controller Class',
            'baseControllerClass' => 'Base Controller Class',
            'indexWidgetType' => 'Widget Used in Index Page',
            'searchModelClass' => 'Search Model Class',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['controller.php'];
    }

    /**
     * {@inheritdoc}
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['providerList', 'actionButtonClass', 'viewPath', 'pathPrefix'], ['baseControllerClass', 'moduleID', 'indexWidgetType']);
    }

    /**
     * all form fields for saving in saved forms.
     *
     * @return array
     */
    public function formAttributes()
    {
        return [
            'modelClass',
            'searchModelClass',
            'controllerClass',
            'baseControllerClass',
            'viewPath',
            'pathPrefix',
            'enableI18N',
            'singularEntities',
            'indexWidgetType',
            'formLayout',
            'actionButtonClass',
            'providerList',
            'template',
            'accessFilter',
            'singularEntities',
            'modelMessageCategory',
        ];
    }

    /**
     * @return string the action view file path
     */
    public function getViewPath()
    {
        if ($this->viewPath !== null) {
            return \Yii::getAlias($this->viewPath) . '/' . $this->getControllerID();
        } else {
            return parent::getViewPath();
        }
    }

    /**
     * @return string the controller ID (without the module ID prefix)
     */
    public function getControllerID()
    {
        $pos = strrpos($this->controllerClass, '\\');
        $class = substr(substr($this->controllerClass, $pos + 1), 0, -10);
        if ($this->singularEntities) {
            $class = Inflector::singularize($class);
        }

        return Inflector::camel2id($class, '-', true);
    }

    /**
     * @return string the controller ID (without the module ID prefix)
     */
    public function getModuleId()
    {
        if (!$this->moduleNs) {
            $controllerNs = \yii\helpers\StringHelper::dirname(ltrim($this->controllerClass, '\\'));
            $this->moduleNs = \yii\helpers\StringHelper::dirname(ltrim($controllerNs, '\\'));
        }

        return \yii\helpers\StringHelper::basename($this->moduleNs);
    }

    public function generate()
    {
        $accessDefinitions = require $this->getTemplatePath() . '/access_definition.php';
        $this->controllerNs = \yii\helpers\StringHelper::dirname(ltrim($this->controllerClass, '\\'));
        $this->moduleNs = \yii\helpers\StringHelper::dirname(ltrim($this->controllerNs, '\\'));
        $controllerName = substr(\yii\helpers\StringHelper::basename($this->controllerClass), 0, -10);

        if ($this->singularEntities) {
            $this->modelClass = Inflector::singularize($this->modelClass);
            $this->controllerClass = Inflector::singularize(
                    substr($this->controllerClass, 0, strlen($this->controllerClass) - 10)
                ) . 'Controller';
            $this->searchModelClass = Inflector::singularize($this->searchModelClass);
        }

        $controllerFile = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->controllerClass, '\\')) . '.php');
        $baseControllerFile = StringHelper::dirname($controllerFile) . '/base/' . StringHelper::basename($controllerFile);
        $restControllerFile = StringHelper::dirname($controllerFile) . '/api/' . StringHelper::basename($controllerFile);

        /*
         * search generated migration and overwrite it or create new
         */
        $migrationDir = StringHelper::dirname(StringHelper::dirname($controllerFile))
            . '/migrations';

        if (file_exists($migrationDir) && $migrationDirFiles = glob($migrationDir . '/m*_' . $controllerName . '00_access.php')) {
            $this->migrationClass = pathinfo($migrationDirFiles[0], PATHINFO_FILENAME);
        } else {
            $this->migrationClass = 'm' . date('ymd_Hi') . '00_' . $controllerName . '_access';
        }

        $files[] = new CodeFile($baseControllerFile, $this->render('controller.php', ['accessDefinitions' => $accessDefinitions]));
        $params['controllerClassName'] = \yii\helpers\StringHelper::basename($this->controllerClass);

        if ($this->overwriteControllerClass || !is_file($controllerFile)) {
            $files[] = new CodeFile($controllerFile, $this->render('controller-extended.php', $params));
        }

        if ($this->overwriteRestControllerClass || !is_file($restControllerFile)) {
            $files[] = new CodeFile($restControllerFile, $this->render('controller-rest.php', $params));
        }

        if (!empty($this->searchModelClass)) {
            $searchModel = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->searchModelClass, '\\') . '.php'));
            if ($this->overwriteSearchModelClass || !is_file($searchModel)) {
                $files[] = new CodeFile($searchModel, $this->render('search.php'));
            }
        }

        $viewPath = $this->getViewPath();
        $templatePath = $this->getTemplatePath() . '/views';

        foreach (scandir($templatePath) as $file) {
            if (empty($this->searchModelClass) && $file === '_search.php') {
                continue;
            }
            if (is_file($templatePath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $files[] = new CodeFile("$viewPath/$file", $this->render("views/$file", ['permisions' => $permisions]));
            }
        }

        if ($this->generateAccessFilterMigrations) {

            /*
             * access migration
             */
            $migrationFile = $migrationDir . '/' . $this->migrationClass . '.php';
            $files[] = new CodeFile($migrationFile, $this->render('migration_access.php', ['accessDefinitions' => $accessDefinitions]));

            /*
             * access roles translation
             */
            $forRoleTranslationFile = StringHelper::dirname(StringHelper::dirname($controllerFile))
                . '/messages/for-translation/'
                . $controllerName . '.php';
            $files[] = new CodeFile($forRoleTranslationFile, $this->render('roles-translation.php', ['accessDefinitions' => $accessDefinitions]));
        }

        /*
         * create gii/[name]GiantCRUD.json with actual form data
         */
        $suffix = str_replace(' ', '', $this->getName());
        $controllerFileinfo = pathinfo($controllerFile);
        $formDataFile = StringHelper::dirname(StringHelper::dirname($controllerFile))
            . '/' . $this->giiInfoPath . '/'
            . str_replace('Controller', $suffix, $controllerFileinfo['filename']) . '.json';
        $formData = json_encode(SaveForm::getFormAttributesValues($this, $this->formAttributes()), JSON_PRETTY_PRINT);
        $files[] = new CodeFile($formDataFile, $formData);

        return $files;
    }

    public function render($template, $params = [])
    {
        $code = parent::render($template, $params);

        // create temp file for code formatting
        $tmpDir = Yii::getAlias('@runtime/giiant');
        FileHelper::createDirectory($tmpDir);
        $tmpFile = $tmpDir . '/' . md5($template);
        file_put_contents($tmpFile, $code);

        if ($this->tidyOutput) {
            $command = Yii::getAlias('@vendor/bin/phptidy.php') . ' replace ' . $this->tidyOptions . ' ' . $tmpFile;
            shell_exec($command);
            $code = file_get_contents($tmpFile);
        }

        if ($this->fixOutput) {
            $command = Yii::getAlias('@vendor/bin/php-cs-fixer') . ' fix ' . $this->fixOptions . ' ' . $tmpFile;
            shell_exec($command);
            $code = file_get_contents($tmpFile);
        }

        unlink($tmpFile);

        return $code;
    }

    public function validateClass($attribute, $params)
    {
        if ($this->singularEntities) {
            $this->$attribute = Inflector::singularize($this->$attribute);
        }
        parent::validateClass($attribute, $params);
    }

    // TODO: replace with VarDumper::export
    public function var_export54($var, $indent = '')
    {
        switch (gettype($var)) {
            case 'string':
                return '"' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '"';
            case 'array':
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = "$indent    "
                        . ($indexed ? '' : $this->var_export54($key) . ' => ')
                        . $this->var_export54($value, "$indent    ");
                }

                return "[\n" . implode(",\n", $r) . "\n" . $indent . ']';
            case 'boolean':
                return $var ? 'TRUE' : 'FALSE';
            default:
                return var_export($var, true);
        }
    }
}
