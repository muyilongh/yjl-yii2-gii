<?php

use Yjl\Gii\helpers\SaveForm;

/**
 * @var yii\web\View
 * @var yii\bootstrap\ActiveForm                   $form
 * @var Yjl\Gii\generators\crud\Generator $generator
 */

/*
 * JS for listbox "Saved Form"
 * on chenging listbox, form fill with selected saved forma data
 * currently work with input text, input checkbox and select form fields
 */
$this->registerJs(SaveForm::getSavedFormsJs($generator->getName(), $generator->giiInfoPath), yii\web\View::POS_END);
$this->registerJs(SaveForm::jsFillForm(), yii\web\View::POS_END);
echo $form->field($generator, 'savedForm')->dropDownList(
        SaveForm::getSavedFormsListbox($generator->getName(), $generator->giiInfoPath), ['onchange' => 'fillForm(this.value)']
);
//dd($generator);
echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'searchModelClass');
echo $form->field($generator, 'controllerClass');
echo $form->field($generator, 'baseControllerClass');
echo $form->field($generator, 'moduleID');
echo $form->field($generator, 'indexWidgetType')->dropDownList([
    'grid' => 'GridView',
    'list' => 'ListView',
]);
echo $form->field($generator, 'viewPath');
echo $form->field($generator, 'pathPrefix');
echo $form->field($generator, 'accessFilter')->checkbox();
echo $form->field($generator, 'generateAccessFilterMigrations')->checkbox();
echo $form->field($generator, 'enableI18N')->checkbox();
echo $form->field($generator, 'messageCategory');

if (Yii::$app->request->isPost) {
    $table_s = $generator->getTableSchema();

    if (empty($table_s)) {
        return;
    }

    $columns = $table_s->columns;
    $cols    = [];
    foreach ($columns as $key => $val) {
        $cols[$key] = $val->name;

    }
    // var_dump($cols);
    echo $form->field($generator, 'listFields')->checkboxList($cols);
    if (empty($generator->inputType)) {
        foreach ($columns as $name => $val) {
            $generator->inputType[$name] = 1;
        }
    }
    // var_dump($generator->inputType);
    echo "<div form-group'>";
    echo '<label control-label help" data-original-title title>Form Fields</label>';
    echo "<div  class='row'>";
    foreach ($columns as $name => $val) {
        $checked = '';
        if (!empty($generator->formFields) && in_array($name, array_values($generator->formFields))) {
            $checked = 'checked="checked"';
//            echo "cccc::::" . $name . ":" . "," . $checked . ";;;;";
        }

        // var_dump($val);
        echo '<div class="col-lg-6 text-center text-success" style="border-bottom: 1px solid #000;display: flex;
justify-content: left;
align-items: center;"><input type="checkbox" name="Generator[formFields][]" value="' . $name . '" ' . $checked . '> <label style="margin-bottom: 0px;" control-label">' . $name . '</label></div>';
        echo '<div class="col-lg-6" style="border-bottom: 1px solid #000">' . \kartik\helpers\Html::dropDownList("Generator[inputType][$name]", $generator->inputType[$name],
                $generator->fieldTypes(), ['class' => 'form-control','style'=>'width:100%']) . '</div>';
    }
    echo "</div></div>";
}

echo $form->field($generator, 'modelMessageCategory');
echo $form->field($generator, 'singularEntities')->checkbox();
echo $form->field($generator, 'indexWidgetType')->dropDownList(
        [
            'grid' => 'GridView',
            'list' => 'ListView',
        ]
);
echo $form->field($generator, 'formLayout')->dropDownList(
        [
            /* Form Types */
            'default' => 'full-width',
            'horizontal' => 'horizontal',
            'inline' => 'inline',
        ]
);
echo $form->field($generator, 'actionButtonClass')->dropDownList(
        [
            'yii\\grid\\ActionColumn' => 'Default',
        ]
);
echo $form->field($generator, 'providerList')->checkboxList($generator->generateProviderCheckboxListData());
